<?php
/**
 * Authentication Manager
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main authentication manager class
 */
class AuthManager {

	/**
	 * Plugin instance
	 *
	 * @var AuthManager
	 */
	private static $instance = null;

	/**
	 * Registered providers
	 *
	 * @var array
	 */
	private $providers = [];

	/**
	 * Get plugin instance
	 *
	 * @return AuthManager
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
	 * Initialize
	 */
	private function init() {
		$this->register_providers();
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Register OAuth providers
	 */
	private function register_providers() {
		$providers = [
			'gmail'     => '\ForWP\Account\Providers\Gmail',
			'github'    => '\ForWP\Account\Providers\Github',
			'facebook'  => '\ForWP\Account\Providers\Facebook',
			// 'tiktok'     => '\ForWP\Account\Providers\TikTok',
		];

		foreach ( $providers as $id => $class ) {
			if ( class_exists( $class ) ) {
				$this->providers[ $id ] = $class::get_instance();
			}
		}
	}

	/**
	 * Get provider by ID
	 *
	 * @param string $provider_id Provider ID.
	 * @return object|null
	 */
	public function get_provider( $provider_id ) {
		return $this->providers[ $provider_id ] ?? null;
	}

	/**
	 * Get all registered providers
	 *
	 * @return array
	 */
	public function get_providers() {
		return $this->providers;
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_scripts() {
		if ( is_user_logged_in() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$this->enqueue_scripts_for_context();
	}

	/**
	 * Enqueue auth assets (account page, shortcodes, WC forms).
	 */
	public function enqueue_scripts_for_context() {
		if ( is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script(
			'forwp-account-signin',
			FORWP_ACCOUNT_PLUGIN_URL . 'assets/js/auth.js',
			array( 'jquery' ),
			FORWP_ACCOUNT_VERSION,
			true
		);

		wp_enqueue_style(
			'forwp-account-signin',
			FORWP_ACCOUNT_PLUGIN_URL . 'assets/css/auth.css',
			array(),
			FORWP_ACCOUNT_VERSION
		);

		wp_localize_script(
			'forwp-account-signin',
			'forwpAccountSignin',
			array(
				'apiUrl' => rest_url( 'forwp-account/v1/' ),
				'nonce'  => wp_create_nonce( 'forwp_account_nonce' ),
			)
		);
	}
}

