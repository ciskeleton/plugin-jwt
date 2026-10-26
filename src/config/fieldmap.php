<?php

/**
 * Plugin Tables Field Map
 *
 * This configuration file stores all database tables and their fields.
 *
 * @package     CiSkeleton\Plugins
 * @subpackage  JWT
 * @category    Configuration
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     Apache-2.0 — Apache License 2.0
 * @link        https://www.apache.org/licenses/LICENSE-2.0
 *
 * SPDX-License-Identifier: Apache-2.0
 */

// Tokens table.
$config['fieldmap']['tokens'] = [
	'id',
	'user_id',
	'refresh_selector',
	'refresh_verifier_hash',
	'refresh_expires_at',
	'user_agent',
	'ip_address',
	'revoked',
	'revoked_at',
	'last_used_at',
	'created_at',
	'updated_at',
];
