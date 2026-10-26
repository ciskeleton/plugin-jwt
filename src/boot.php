<?php

/**
 * JSON Web Tokens (JWT) Plugin Boot
 *
 * @package     CiSkeleton\Plugins
 * @subpackage  JWT
 * @category    Bootstrap
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     Apache-2.0 — Apache License 2.0
 * @link        https://www.apache.org/licenses/LICENSE-2.0
 *
 * SPDX-License-Identifier: Apache-2.0
 */

// --------------------------------------------------------------------
// Lifespan Constants
// --------------------------------------------------------------------
define_const('LIFE_JWT_ACCESS', MINUTE_IN_SECONDS * 15); // JWT Access Token lifespan
define_const('LIFE_JWT_REFRESH', HOUR_IN_SECONDS * 12);  // JWT Refresh Token lifespan.

// --------------------------------------------------------------------
// Boot Hook
// --------------------------------------------------------------------
once_action('plugin_boot_jwt', static function () {
	// Register needed class with 'Autoloader'.
	Autoloader::add_class('CI_App_jwt', __DIR__.'/libraries/App/drivers/App_jwt.php');

	// ----------------------------------------------------------------

	// Add our driver.
	once_filter('app_drivers', static function ($drivers) {
		$drivers[] = 'jwt';
		return $drivers;
	});

	// ----------------------------------------------------------------

	/**
	 * tokens
	 *
	 * Purge expired and revoked JWT token entries from the database.
	 *
	 * This method is responsible for cleaning up the `tokens` table by
	 * deleting records that are no longer valid or useful.
	 *
	 * - Tokens that have been explicitly **revoked** (revoked = 1).
	 * - Tokens for which refresh expiration timestamp have passed.
	 *
	 * The cleanup logic ensures the database remains efficient and
	 * uncluttered by removing obsolete tokens, thus reducing potential
	 * attack surfaces and saving storage space.
	 *
	 * To avoid over-purging on frequent hits, it only runs if the current
	 * time exceeds the `jwt_purge_int` value stored in `options`.
	 * If successful, the method updates the value of the current time
	 * plus the configured purge interval (`jwt_purge_int`).
	 *
	 * After deleting, it logs how many token entries were removed and also
	 * prints a message to the CLI (if the call is from a cron job).
	 *
	 * @param  int           $unused  Previous number of deleted entries.
	 * @param  CI_Controller $ci      Instance of CI super-object.
	 * @return int                    The number of tokens deleted from the database.
	 */
	once_filter('hub_purge', static function ($unsed, $ci) {
		$jwt_purge_int = defined('INT_PURGE_JWT') ? INT_PURGE_JWT : HOUR_IN_SECONDS;

		// Purge time not yet due?
		if (CI_REQUEST_TIME < $ci->config->item('next_jwt_purge', null, 0)) {
			return 0;
		}

		// Unable to update next purge time?
		if (!$ci->config->persist('next_jwt_purge', CI_REQUEST_TIME + $jwt_purge_int)) {
			return 0;
		}

		// For some reason, `tokens` table is missing?
		if (!$ci->db->table_exists('tokens')) {
			return 0;
		}

		$count = 0;
		$iter = 0;
		$max_iter = 20;

		do {
			$ci->db
				->where('revoked', 1)
				->or_where('refresh_expires_at <', CI_REQUEST_TIME)
				->limit(1000)
				->delete('tokens');

			$deleted = $ci->db->affected_rows();
			$count += $deleted;
			$iter++;
		} while ($deleted > 0 && $iter < $max_iter);

		// If something was deleted, log the operation.
		if ($count > 0) {
			// Reset table `AUTO_INCREMENT`.
			$ci->db->set_auto_increment('tokens', 1);

			$message = sprintf('JWT Purge: Complete - %d tokens deleted in %d iterations.', $count, $iter);

			if (is_cli_request()) {
				log_message('debug', '[CRON] '.$message);
				echo date('Y-m-d H:i', CI_REQUEST_TIME).' - [CRON] '.$message.PHP_EOL;
			} else {
				log_message('debug', '[WEB] '.$message);
			}
		}

		// Delete table orphans.
		$count += $ci->hub->purge->table_orphans('tokens', 'user_id');

		return $count;
	});
});

// --------------------------------------------------------------------
// Activation/Deactivation Hooks
// --------------------------------------------------------------------

/**
 * Creates `tokens` table on plugin activation, if needed.
 *
 * @param  CI_Controller $ci
 * @return void
 */
once_action('plugin_activate_jwt', static function ($ci) {
	// Bail early if table exists.
	if ($ci->db->table_exists('tokens')) {
		return;
	}

	// Load database forge if needed.
	isset($ci->dbforge) || $ci->load->dbforge();

	// Define the table fields
	$fields = [
		'id' => [
			'type' => 'BIGINT',
			'constraint' => 20,
			'unsigned' => true,
			'auto_increment' => true,
		],
		'user_id' => [
			'type' => 'BIGINT',
			'constraint' => 20,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
		'refresh_selector' => [
			'type' => 'CHAR',
			'constraint' => 32,
			'null' => false,
		],
		'refresh_verifier_hash' => [
			'type' => 'VARCHAR',
			'constraint' => 128,
			'null' => false,
		],
		'refresh_expires_at' => [
			'type' => 'INT',
			'constraint' => 11,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
		'user_agent' => [
			'type' => 'VARCHAR',
			'constraint' => 512,
			'null' => true,
			'default' => null,
		],
		'ip_address' => [
			'type' => 'VARCHAR',
			'constraint' => 45,
			'null' => true,
			'default' => null,
		],
		'revoked' => [
			'type' => 'TINYINT',
			'constraint' => 1,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
		'revoked_at' => [
			'type' => 'INT',
			'constraint' => 11,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
		'last_used_at' => [
			'type' => 'INT',
			'constraint' => 11,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
		'created_at' => [
			'type' => 'INT',
			'constraint' => 11,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
		'updated_at' => [
			'type' => 'INT',
			'constraint' => 11,
			'unsigned' => true,
			'default' => 0,
			'null' => false,
		],
	];

	// Add fields and keys
	$ci->dbforge->add_field($fields);
	$ci->dbforge->add_key('id', true); // Primary key
	$ci->dbforge->add_key('user_id');
	$ci->dbforge->add_key('revoked');

	// Create table
	$ci->dbforge->create_table('tokens', true, [
		'ENGINE' => 'InnoDB',
		'DEFAULT CHARACTER SET' => $ci->db->char_set ?? 'utf8mb4',
		'COLLATE' => $ci->db->dbcollat ?? 'utf8mb4_unicode_520_ci',
	]);

	// Add unique index manually after creation
	$ci->db->query("ALTER TABLE {$ci->db->dbprefix('tokens')} ADD UNIQUE KEY `idx_refresh_selector` (`refresh_selector`)");
});

// --------------------------------------------------------------------

/**
 * Drops `tokens` table and token options on plugin deactivation.
 *
 * @param  CI_Controller $ci
 * @return void
 */
once_action('plugin_deactivate_jwt', static function ($ci) {
	// Load database forge if needed.
	isset($ci->dbforge) || $ci->load->dbforge();

	// Drop the table if it exists.
	$ci->dbforge->drop_table('tokens', true);

	// Delete JWT plugin options.
	$ci->config->delete('jwt_blacklist', 'next_jwt_purge');
});
