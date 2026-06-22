<?php
/**
 * Plugin Extension Core
 *
 * @package ForWP\Account\Core
 */

namespace ForWP\Account\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Extension class
 */
class Extension {

	/**
	 * Plugin instance
	 *
	 * @var Extension
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Extension
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin
	 */
	private function init() {
		if ( class_exists( '\ForWP\Account\Blocks\AccountBlocks' ) ) {
			\ForWP\Account\Blocks\AccountBlocks::init();
		}

		add_action( 'init', [ $this, 'load_modules' ], 1 );
	}

	/**
	 * Load plugin modules
	 */
	public function load_modules() {
		// Load Auth Manager
		if ( class_exists( '\ForWP\Account\Auth\AuthManager' ) ) {
			\ForWP\Account\Auth\AuthManager::get_instance();
		}

		// Load API routes
		if ( class_exists( '\ForWP\Account\API\Routes' ) ) {
			\ForWP\Account\API\Routes::get_instance();
		}

		// Load Admin panel (always load to enable toolbar hiding on frontend)
		if ( class_exists( '\ForWP\Account\Admin\Menu' ) ) {
			\ForWP\Account\Admin\Menu::get_instance();
		}

		// Load Shortcodes
		if ( class_exists( '\ForWP\Account\Shortcodes' ) ) {
			\ForWP\Account\Shortcodes::init();
		}

		// Account page (auth + cabinet shell)
		if ( class_exists( '\ForWP\Account\Account\AccountPage' ) ) {
			\ForWP\Account\Account\AccountPage::init();
		}

		// Load WooCommerce integration
		if ( class_exists( '\ForWP\Account\Integrations\WooCommerce' ) ) {
			\ForWP\Account\Integrations\WooCommerce::get_instance();
		}

		// Run migrations
		if ( class_exists( '\ForWP\Account\Storage\Migrations' ) ) {
			\ForWP\Account\Storage\Migrations::run();
		}
	}
}

