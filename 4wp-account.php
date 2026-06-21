<?php
/**
 * Plugin Name:       4WP Account
 * Plugin URI:        https://github.com/4wpdev/4wp-account
 * Description:       Social login for WordPress — Gmail, Facebook, Instagram, and TikTok OAuth with shortcodes and WooCommerce support.
 * Version:           1.0.2
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

define( 'FORWP_ACCOUNT_VERSION', '1.0.2' );
define( 'FORWP_ACCOUNT_FILE', __FILE__ );
define( 'FORWP_ACCOUNT_PATH', plugin_dir_path( __FILE__ ) );
define( 'FORWP_ACCOUNT_URL', plugin_dir_url( __FILE__ ) );
define( 'FORWP_ACCOUNT_BASENAME', plugin_basename( __FILE__ ) );

// Back-compat constants (internal).
define( 'FORWP_AUTH_VERSION', FORWP_ACCOUNT_VERSION );
define( 'FORWP_AUTH_PLUGIN_FILE', FORWP_ACCOUNT_FILE );
define( 'FORWP_AUTH_PLUGIN_DIR', FORWP_ACCOUNT_PATH );
define( 'FORWP_AUTH_PLUGIN_URL', FORWP_ACCOUNT_URL );
define( 'FORWP_AUTH_PLUGIN_BASENAME', FORWP_ACCOUNT_BASENAME );

if ( file_exists( FORWP_ACCOUNT_PATH . 'vendor/autoload.php' ) ) {
	require_once FORWP_ACCOUNT_PATH . 'vendor/autoload.php';
} else {
	require_once FORWP_ACCOUNT_PATH . 'includes/autoload.php';
}

add_action(
	'plugins_loaded',
	static function () {
		if ( class_exists( 'ForWP\Auth\Core\Extension' ) ) {
			\ForWP\Auth\Core\Extension::get_instance();
		}
	},
	10
);
