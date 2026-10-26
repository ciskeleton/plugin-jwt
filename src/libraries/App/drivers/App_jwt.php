<?php

/**
 * CI_App_jwt Class
 *
 * A compact but complete JWT system made for CiSkeleton.
 * Provides JWT encoding/decoding plus secure refresh token handling.
 *
 * On the client (user’s device):
 * - **Access token**: full JWT string `header.payload.signature`, short-lived.
 * - **Refresh token**: opaque string `selector.verifier`, long-lived.
 *
 * In the database:
 * Only refresh tokens are persisted (OAuth2-style):
 * - `refresh_selector`: plaintext, indexed UNIQUE, used for lookup.
 * - `refresh_verifier_hash`: hashed verifier, used for validation.
 * - Metadata columns: expiry, revoked flag, timestamps, etc.
 *
 * 🚦 Workflow:
 * 1. Client authenticates → server issues an **access token** (JWT) and a **refresh token**.
 * 2. Client uses the access token in the `Authorization: Bearer` header on each request.
 * 3. When the access token expires, client sends the refresh token to get a new pair.
 * 4. Server splits the refresh token into selector/verifier, looks up selector, and verifies
 *    the stored hash against the presented verifier.
 * 5. If valid, server **rotates** the refresh token (revokes old row, issues new access+refresh).
 *
 * Philosophy:
 * - Access tokens are **stateless** and validated purely via JWT signature & claims.
 * - Refresh tokens are **stateful**, persisted for rotation, revocation, and auditing.
 * - Revocation is enforced by marking refresh tokens as `revoked`, preventing reuse.
 *
 * @package     CiSkeleton\Plugins\Libraries
 * @subpackage  App\Drivers
 * @category    JWT
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     Apache-2.0 — Apache License 2.0
 * @link        https://www.apache.org/licenses/LICENSE-2.0
 *
 * SPDX-License-Identifier: Apache-2.0
 */

final class CI_App_jwt extends CI_Driver
{
	/**
	 * The algorithm used to sign the token.
	 *
	 * @var string
	 */
	private $algo = 'HS256';

	/**
	 * Time to live (TTL) for access tokens, in seconds.
	 *
	 * @var int
	 */
	private $ttl = LIFE_JWT_ACCESS; // 15 minutes.

	/**
	 * Time to refresh (TTR) for refresh tokens, in seconds.
	 *
	 * @var int
	 */
	private $ttr = LIFE_JWT_REFRESH; // 12 hours.

	/**
	 * Seconds of clock skew tolerance.
	 *
	 * @var int
	 */
	private $leeway = 60;

	/**
	 * Lists of allowed audiences.
	 *
	 * @var string[]
	 */
	private $allowed_audiences = ['mobile-app', 'web-app'];

	/**
	 * Cached decoded payload after token validation.
	 *
	 * @var array|false
	 */
	private $_payload;

	/**
	 * List of error constants.
	 *
	 * @var int
	 */
	public const ERR_NONE = 0;
	public const ERR_BLACKLISTED = 1;
	public const ERR_MALFORMED = 2;
	public const ERR_JSON_DECODE = 3;
	public const ERR_UNSUPPORTED_ALG = 4;
	public const ERR_BAD_SIGNATURE = 5;
	public const ERR_EXPIRED = 6;
	public const ERR_NBF = 7;
	public const ERR_IAT_FUTURE = 8;
	public const ERR_AUDIENCE = 9;
	public const ERR_ISSUER = 10;

	/**
	 * Last error.
	 *
	 * @var int
	 */
	private $last_error = self::ERR_NONE;

	/**
	 * Whether to allow parsing token from body if not found in header.
	 *
	 * @var bool
	 */
	private $allow_body_token = false;

	// --------------------------------------------------------------------

	/**
	 * Encodes a payload array into a JWT string.
	 *
	 * @param  array    $payload  Custom payload data.
	 * @param  int|null $ttl      Optional custom TTL.
	 * @return string             The JWT string.
	 */
	public function encode($payload = [], $ttl = null)
	{
		// Add 'aud', 'iss', 'iat', 'exp', and 'nbf'.
		$payload['aud'] = $payload['aud'] ?? (is_mobile() ? 'mobile-app' : 'web-app');
		$payload['iss'] = $payload['iss'] ?? $this->ci->config->item('domain_url');
		$payload['iat'] = $payload['iat'] ?? CI_REQUEST_TIME;
		$payload['exp'] = $payload['exp'] ?? CI_REQUEST_TIME + ($ttl ?? $this->ttl);
		$payload['nbf'] = $payload['nbf'] ?? $payload['iat']; // recommended

		// Prepare token header.
		$header = ['alg' => $this->algo, 'typ' => 'JWT'];

		// Prepare token segments.
		if (($header_json = $this->json_encode_safe($header)) === null
			|| ($payload_json = $this->json_encode_safe($payload)) === null) {
			$this->_set_error(self::ERR_JSON_DECODE);
			return false;
		}
		$segments = [
			$this->base64_url_encode($header_json),
			$this->base64_url_encode($payload_json)
		];

		// Generate token signature and add it to segments.
		$signature = $this->sign(implode('.', $segments));
		$segments[] = $signature;

		// Return final string.
		return implode('.', $segments);
	}

	// --------------------------------------------------------------------

	/**
	 * Decodes and verifies a JWT string.
	 *
	 * @param  string $token       The JWT string.
	 * @param  bool   $verify_exp  Whether to check for expiration.
	 * @return array|false         Decoded payload array on success, false on failure.
	 */
	public function decode($token, $verify_exp = true)
	{
		// Is the token blacklisted?
		if ($this->is_blacklisted($token)) {
			$this->_set_error(self::ERR_BLACKLISTED);
			return $this->_payload = false;
		}

		// Doesn't it have 3 parts?.
		if (count($parts = explode('.', $token)) !== 3) {
			$this->_set_error(self::ERR_MALFORMED);
			return $this->_payload = false;
		}

		// Prepare header, payload and signature strings.
		[$header64, $payload64, $signature] = $parts;
		$header = json_decode($this->base64_url_decode($header64), true);
		$payload = json_decode($this->base64_url_decode($payload64), true);

		// Problem JSON decoding header or payload?
		if (!is_array($header) || !is_array($payload)) {
			$this->_set_error(self::ERR_JSON_DECODE);
			return $this->_payload = false;
		}

		// Enforce allowed "alg" & JWT "typ".
		if (empty($header['alg'])
			|| $header['alg'] !== $this->algo
			|| (isset($header['typ']) && $header['typ'] !== 'JWT')) {
			$this->_set_error(self::ERR_UNSUPPORTED_ALG);
			return $this->_payload = false;
		}

		// Verify signature.
		$expected = $this->sign("{$header64}.{$payload64}", $header);
		if (!hash_equals($expected, $signature)) {
			$this->_set_error(self::ERR_BAD_SIGNATURE);
			return $this->_payload = false;
		}

		$now = CI_REQUEST_TIME;

		// "iat" must not be too far in the future.
		if (isset($payload['iat']) && ($payload['iat'] - $this->leeway) > $now) {
			$this->_set_error(self::ERR_IAT_FUTURE);
			return $this->_payload = false;
		}

		// "nbf" must allow for leeway.
		if (isset($payload['nbf']) && ($now + $this->leeway) < $payload['nbf']) {
			$this->_set_error(self::ERR_NBF);
			return $this->_payload = false;
		}

		// "exp" with leeway.
		if ($verify_exp && isset($payload['exp']) && $now > ($payload['exp'] + $this->leeway)) {
			$this->_set_error(self::ERR_EXPIRED);
			return $this->_payload = false;
		}

		// Check issuer.
		if (isset($payload['iss']) && $payload['iss'] !== $this->ci->config->item('domain_url')) {
			$this->_set_error(self::ERR_ISSUER);
			return $this->_payload = false;
		}

		// Check audience.
		if (isset($payload['aud']) && !in_array($payload['aud'], $this->allowed_audiences, true)) {
			$this->_set_error(self::ERR_AUDIENCE);
			return $this->_payload = false;
		}

		return $this->_payload = $payload;
	}

	// --------------------------------------------------------------------

	/**
	 * Invalidates a given JWT by blacklisting it.
	 *
	 * @param  string $token  The token to blacklist.
	 * @return bool           True if successful, false otherwise.
	 */
	public function invalidate($token)
	{
		return $this->blacklist($token);
	}

	// --------------------------------------------------------------------

	/**
	 * Signs a string with HMAC SHA-256.
	 *
	 * @param  string $data  The data to sign.
	 * @return string        The base64 URL-encoded HMAC signature.
	 */
	private function sign($data)
	{
		return $this->base64_url_encode(hash_hmac('sha256', $data, $this->ci->config->item('encryption_key_256'), true));
	}

	// --------------------------------------------------------------------

	/**
	 * Checks if a token is blacklisted.
	 *
	 * @param  string $token  The JWT token.
	 * @return bool           True if blacklisted, false otherwise.
	 */
	public function is_blacklisted($token)
	{
		$fp = $this->fp($token);
		$list = $this->ci->config->item('jwt_blacklist', null, []);

		if (isset($list[$fp]) && CI_REQUEST_TIME > $list[$fp]) {
			unset($list[$fp]);
			$this->ci->config->persist('jwt_blacklist', $list);
			return false;
		}

		return isset($list[$fp]);
	}

	// --------------------------------------------------------------------

	/**
	 * Adds a token to the blacklist until given timestamp.
	 *
	 * @param  string   $token  The token to blacklist.
	 * @param  int|null $until  UNIX timestamp; defaults to token exp + leeway or 1h.
	 * @return bool             True on success, false on failure.
	 */
	public function blacklist($token, $until = null)
	{
		$fp = $this->fp($token);
		$list = $this->ci->config->item('jwt_blacklist', null, []);

		if (empty($until) && count($parts = explode('.', $token)) === 3) {
			$payload = json_decode($this->base64_url_decode($parts[1]), true);
			if (is_array($payload) && isset($payload['exp'])) {
				$until = (int)$payload['exp'] + $this->leeway;
			}
		}

		$list[$fp] = $until ?? (CI_REQUEST_TIME + HOUR_IN_SECONDS);
		return $this->ci->config->persist('jwt_blacklist', $list);
	}

	// --------------------------------------------------------------------

	/**
	 * Manually purges expired blacklisted tokens.
	 *
	 * @return bool
	 */
	public function purge_blacklist()
	{
		$list = $this->ci->config->item('jwt_blacklist', null, []);
		$now = CI_REQUEST_TIME;
		$changed = false;

		foreach ($list as $token => $until) {
			if ($now > $until) {
				unset($list[$token]);
				$changed = true;
			}
		}

		return $changed ? $this->ci->config->persist('jwt_blacklist', $list) : true;
	}

	// --------------------------------------------------------------------

	/**
	 * Attempts to extract a JWT from the request.
	 *
	 * @return string|null  The token if found, or null.
	 */
	public function parse()
	{
		// From HTTP header?
		$header = $this->ci->input->get_request_header('Authorization', true);
		if ($header && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
			return $matches[1];
		}

		// From body?
		if ($this->allow_body_token && !empty($token = $this->ci->input->get_post('bearer', true))) {
			return $token;
		}

		// Nothing found!
		return null;
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the decoded payload of a JWT.
	 *
	 * @param  string|null $token       Optional token (falls back to parsed token).
	 * @param  bool        $verify_exp  Whether to check for expiration.
	 * @return array|false              Decoded payload, or false if invalid.
	 */
	public function payload($token = null, $verify_exp = true)
	{
		// Already cached?
		if (isset($this->_payload)) {
			return $this->_payload;
		}

		// Missing token? Parse it...
		empty($token) && $token = $this->parse();

		// Nothing found?
		if (empty($token)) {
			return $this->_payload = false;
		}
		// Could not be decode?
		elseif (!($this->_payload = $this->decode($token, $verify_exp))) {
			return $this->_payload = false;
		}

		// Everything passed.
		return $this->_payload;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieves the subject (user ID) from the payload.
	 *
	 * @param  string|null $token    Optional token.
	 * @param  mixed       $default  Default return value if not found.
	 * @return mixed                 Subject from token or default.
	 */
	public function subject($token = null, $default = 0)
	{
		$payload = $this->payload($token);

		return (is_array($payload) && isset($payload['sub'])) ? $payload['sub'] : $default;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieves the audience from the payload.
	 *
	 * @param  string|null $token    Optional token.
	 * @param  mixed       $default  Default value if not found.
	 * @return mixed                 Audience value or default.
	 */
	public function audience($token = null, $default = 'web-app')
	{
		$payload = $this->payload($token);

		return (is_array($payload) && isset($payload['aud'])) ? $payload['aud'] : $default;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieves the issuer from the payload.
	 *
	 * @param  string|null $token    Optional token.
	 * @param  mixed       $default  Default value if not found.
	 * @return mixed                 Issuer or default.
	 */
	public function issuer($token = null, $default = Platform::SHORT)
	{
		$payload = $this->payload($token);

		return (is_array($payload) && isset($payload['iss'])) ? $payload['iss'] : $default;
	}

	// --------------------------------------------------------------------

	/**
	 * Saves a hashed version of access and refresh tokens to the database.
	 *
	 * @param  int   $user_id     User ID to associate with tokens.
	 * @param  array $token_data  Token data (access/refresh/user_agent/IP).
	 * @return bool|array         Array of token data on success,, false otherwise.
	 */
	public function save($user_id, $token_data)
	{
		// Make sure the user exists first.
		if (!($user = $this->ci->hub->users->get_by_id($user_id))) {
			return false;
		}

		// Parse refresh token into selector / verifier
		[$selector, $verifier] = explode('.', $token_data['refresh_token'], 2);

		// Prepare data to insert into `tokens` table.
		$data = [
			'user_id' => $user->id,
			'refresh_selector' => $selector,
			'refresh_verifier_hash' => service('password')->hash($verifier),
			'refresh_expires_at' => CI_REQUEST_TIME + $this->ttr,
			'user_agent' => $this->ci->input->user_agent(),
			'ip_address' => ip_address(),
			'revoked' => 0,
			'revoked_at' => 0,
			'last_used_at' => CI_REQUEST_TIME,
			'created_at' => CI_REQUEST_TIME,
			'updated_at' => CI_REQUEST_TIME
		];

		// Something went wrong?
		if (!$this->ci->db->insert('tokens', $data)) {
			return false;
		}

		// Complete token data before returning it.
		return [
			'access_token' => $token_data['access_token'],
			'refresh_token' => $token_data['refresh_token'],
			'access_expire' => CI_REQUEST_TIME + $this->ttl,
			'refresh_expire' => $data['refresh_expires_at'],
			'token_type' => 'Bearer',
			'current_time' => CI_REQUEST_TIME,
			'access_ttl' => $this->ttl,
			'refresh_ttl' => $this->ttr
		];
	}

	// --------------------------------------------------------------------

	/**
	 * Revokes a refresh token (by selector) or all tokens by user ID.
	 *
	 * @param  string|int $target  Refresh token string ("selector.verifier") OR user ID (int).
	 * @return int                 Number of affected rows.
	 */
	public function revoke($target)
	{
		// Case 1: String → refresh token string "selector.verifier"
		if (is_string($target)) {
			// Split refresh token into selector/verifier
			[$selector] = explode('.', $target, 2);

			// Mark this refresh token as revoked
			$this->ci->db->update('tokens', [
				'revoked' => 1,
				'revoked_at' => CI_REQUEST_TIME
			], ['refresh_selector' => $selector]);

			return $this->ci->db->affected_rows();
		}

		// Case 2: Integer → revoke all refresh tokens for the given user
		$this->ci->db->update('tokens', [
			'revoked' => 1,
			'revoked_at' => CI_REQUEST_TIME
		], ['user_id' => (int) $target]);

		return $this->ci->db->affected_rows();
	}

	// --------------------------------------------------------------------

	/**
	 * Refreshes an access token using a valid refresh token.
	 *
	 * @param  string $refresh_token  The refresh token provided by the client.
	 * @return array|null             New token data or null on failure.
	 */
	public function refresh($refresh_token)
	{
		// Must be "selector.verifier"
		if (count($parts = explode('.', $refresh_token, 2)) !== 2) {
			return null;
		}

		[$selector, $verifier] = $parts;

		// 1. Find matching refresh token row
		$row = $this->ci->db->get_where('tokens', [
			'refresh_selector' => $selector,
			'refresh_expires_at >' => CI_REQUEST_TIME,
			'revoked' => 0
		], 1)->row();

		if (!$row) {
			return null;
		}

		// 2. Verify the refresh verifier
		if (!service('password')->verify($verifier, $row->refresh_verifier_hash)) {
			return null;
		}

		// 3. Verify the user still exists
		if (!($user = $this->ci->hub->users->get_by_id($row->user_id))) {
			$this->ci->db->delete('tokens', ['id' => $row->id]);
			return null;
		}

		// 4. Generate new tokens
		$new_access_token  = $this->generate_access_token($user);
		$new_refresh_token = $this->generate_refresh_token();
		[$new_selector, $new_verifier] = explode('.', $new_refresh_token, 2);

		// 5. Rotate refresh token in-place
		$data = [
			'refresh_selector' => $new_selector,
			'refresh_verifier_hash' => service('password')->hash($new_verifier),
			'refresh_expires_at' => CI_REQUEST_TIME + $this->ttr,
			'revoked' => 0,
			'revoked_at' => 0,
			'last_used_at' => CI_REQUEST_TIME,
			'updated_at' => CI_REQUEST_TIME
		];

		$this->ci->db->update('tokens', $data, ['id' => $row->id]);

		if ($this->ci->db->affected_rows() < 1) {
			return null;
		}

		// 6. Return new token bundle to client
		return [
			'access_token' => $new_access_token,
			'refresh_token' => $new_refresh_token,
			'access_expire' => CI_REQUEST_TIME + $this->ttl,
			'refresh_expire' => $data['refresh_expires_at'],
			'token_type' => 'Bearer',
			'current_time' => CI_REQUEST_TIME,
			'access_ttl' => $this->ttl,
			'refresh_ttl' => $this->ttr
		];
	}

	// --------------------------------------------------------------------

	/**
	 * generate_access_token
	 *
	 * Generate a JWT access token for a given user.
	 *
	 * Builds the standard JWT payload including subject (user ID), token type,
	 * audience, issuer, issued-at, and expiration claims.
	 * Optionally merges in extra claims provided by the caller.
	 *
	 * @param  CI_User  $user  User object.
	 * @param  array    $data  Optional. Additional key-value pairs to merge into the payload.
	 * @param  int|null $ttl   Optional. Time-to-live in seconds for the token.
	 * @return string          Encoded JWT access token.
	 */
	public function generate_access_token($user, $data = [], $ttl = null)
	{
		// Base payload with standard claims
		$payload = [
			'sub' => $user->id,
			'type' => 'access',
			'aud' => is_mobile() ? 'mobile-app' : 'web-app',
			'iss' => $this->ci->config->item('domain_url'),
			'iat' => CI_REQUEST_TIME,
			'exp' => CI_REQUEST_TIME + ($ttl ?? $this->ttl)
		];

		// Merge any extra payload values, allowing overrides
		empty($data) || $payload = array_merge($payload, $data);

		// Encode and return the JWT token
		return $this->encode($payload, $ttl);
	}

	// --------------------------------------------------------------------

	/**
	 * Generates a refresh token in the form "selector.verifier".
	 *
	 * @return string
	 */
	public function generate_refresh_token()
	{
		$selector = bin2hex(random_bytes(16));
		$verifier = bin2hex(random_bytes(32));
		return $selector.'.'.$verifier;
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the last decode error code.
	 *
	 * @return int
	 */
	public function last_error()
	{
		return $this->last_error;
	}

	// --------------------------------------------------------------------

	/**
	 * Whether to accept consider body tokens if failed to parse it from header.
	 *
	 * @return static
	 */
	public function allow_body_token()
	{
		$this->allow_body_token = true;
		return $this;
	}

	// --------------------------------------------------------------------
	// Private Methods
	// --------------------------------------------------------------------

	/**
	 * Base64 URL encodes a string (RFC 7515).
	 *
	 * @param  string $data  Input string.
	 * @return string        Encoded string.
	 */
	private function base64_url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	// --------------------------------------------------------------------

	/**
	 * Base64 URL decodes a string (RFC 7515).
	 *
	 * @param  string $data  Encoded string.
	 * @return string        Decoded string.
	 */
	private function base64_url_decode($data)
	{
		$pad = strlen($data) % 4;

		($pad > 0) && $data .= str_repeat('=', 4 - $pad);

		return base64_decode(strtr($data, '-_', '+/'));
	}

	// --------------------------------------------------------------------

	/**
	 * Sets decode error.
	 *
	 * @param  int $code
	 * @return void
	 */
	private function _set_error($code)
	{
		$this->last_error = $code;
	}

	// --------------------------------------------------------------------

	/**
	 * Safe-JSON-encodes the given data.
	 *
	 * @param  array $data  The data to encode.
	 * @return string|null  Encoded data on success, false on failure.
	 */
	private function json_encode_safe($data)
	{
		$json = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
		if ($json === false) {
			log_message('critical', 'JWT: '.json_last_error_msg());
			return null;
		}
		return $json;
	}

	// --------------------------------------------------------------------

	/**
	 * Helper to fingerprint token (sha256).
	 *
	 * @param  string $token
	 * @return string
	 */
	private function fp($token)
	{
		return hash('sha256', $token);
	}

}
