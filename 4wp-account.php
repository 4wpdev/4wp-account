<?php
/**
 * Plugin Name:       4WP Account
 * Plugin URI:        https://github.com/4wpdev/4wp-account
 * Description:       User account hub — Google/GitHub social login, account page, and Gutenberg blocks.
 * Version:           1.0.3
 * Requires at least: 6.4
 * Tested up to:      7.0
 * Requires PHP:      8.0
 * Author:            4wpdev
 * Author URI:        https://4wp.dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       4wp-account
 * Domain Path:       /languages
 *
 * @package ForWP\Account
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FORWP_ACCOUNT_VERSION', '1.0.3' );
define( 'FORWP_ACCOUNT_PLUGIN_FILE', __FILE__ );
define( 'FORWP_ACCOUNT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FORWP_ACCOUNT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FORWP_ACCOUNT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( 'FORWP_ACCOUNT_FILE', FORWP_ACCOUNT_PLUGIN_FILE );
define( 'FORWP_ACCOUNT_PATH', FORWP_ACCOUNT_PLUGIN_DIR );
define( 'FORWP_ACCOUNT_URL', FORWP_ACCOUNT_PLUGIN_URL );
define( 'FORWP_ACCOUNT_BASENAME', FORWP_ACCOUNT_PLUGIN_BASENAME );

define( 'FORWP_AUTH_VERSION', FORWP_ACCOUNT_VERSION );
define( 'FORWP_AUTH_PLUGIN_FILE', FORWP_ACCOUNT_PLUGIN_FILE );
define( 'FORWP_AUTH_PLUGIN_DIR', FORWP_ACCOUNT_PLUGIN_DIR );
define( 'FORWP_AUTH_PLUGIN_URL', FORWP_ACCOUNT_PLUGIN_URL );
define( 'FORWP_AUTH_PLUGIN_BASENAME', FORWP_ACCOUNT_PLUGIN_BASENAME );

if ( file_exists( FORWP_ACCOUNT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once FORWP_ACCOUNT_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	require_once FORWP_ACCOUNT_PLUGIN_DIR . 'includes/autoload.php';
}

add_action(
	'plugins_loaded',
	static function () {
		if ( class_exists( 'ForWP\\Account\\Core\\Extension' ) ) {
			\ForWP\Account\Core\Extension::get_instance();
		}
	},
	10
);
