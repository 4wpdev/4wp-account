<?php
/**
 * Admin Menu
 *
 * @package ForWP\Account\Admin
 */

namespace ForWP\Account\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menu class
 */
class Menu {

	/**
	 * @var Menu|null
	 */
	private static $instance = null;

	/**
	 * @return Menu
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
		if ( is_admin() ) {
			SettingsPage::init();
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'redirect_subscribers_from_admin' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		}

		add_filter( 'show_admin_bar', array( $this, 'hide_toolbar_for_subscribers' ), 999 );
	}

	/**
	 * @param string $hook_suffix Admin hook.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		unset( $hook_suffix );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['page'] ) || SettingsPage::PAGE_SLUG !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		wp_enqueue_style(
			'forwp-account-admin',
			FORWP_ACCOUNT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			FORWP_ACCOUNT_VERSION
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$section = sanitize_key( (string) ( $_GET['section'] ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = sanitize_key( (string) ( $_GET['tab'] ?? SettingsPage::TAB_AUTH ) );

		if ( 'account-menu' === $section ) {
			wp_enqueue_script(
				'forwp-account-admin-menu',
				FORWP_ACCOUNT_PLUGIN_URL . 'assets/js/admin-account-menu.js',
				array(),
				FORWP_ACCOUNT_VERSION,
				true
			);
		}

		if ( SettingsPage::TAB_AUTH === $tab ) {
			wp_enqueue_script(
				'forwp-account-admin-auth',
				FORWP_ACCOUNT_PLUGIN_URL . 'assets/js/admin-auth.js',
				array(),
				FORWP_ACCOUNT_VERSION,
				true
			);
		}
	}

	/**
	 * @param bool $show Whether to show the toolbar.
	 */
	public function hide_toolbar_for_subscribers( $show ) {
		if ( get_option( 'forwp_account_hide_toolbar_subscribers', '0' ) !== '1' ) {
			return $show;
		}

		if ( ! is_user_logged_in() ) {
			return $show;
		}

		$user = wp_get_current_user();

		if ( ! empty( $user->roles ) && in_array( 'subscriber', $user->roles, true ) && ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Redirect subscribers from admin area
	 */
	public function redirect_subscribers_from_admin() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$redirect_url = get_option( 'forwp_account_subscriber_redirect_url', '' );
		if ( empty( $redirect_url ) ) {
			$redirect_url = \ForWP\Account\Account\AccountMenu::get_account_page_url();
		}

		if ( empty( $redirect_url ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		global $pagenow;
		if ( in_array( $pagenow, array( 'admin-ajax.php', 'admin-post.php' ), true ) ) {
			return;
		}

		$user = wp_get_current_user();

		if ( ! empty( $user->roles ) && in_array( 'subscriber', $user->roles, true ) && ! current_user_can( 'edit_posts' ) ) {
			if ( strpos( $redirect_url, 'http' ) !== 0 ) {
				$redirect_url = home_url( $redirect_url );
			}

			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$current_url = home_url( $request_uri );
			$redirect_url_parsed = wp_parse_url( $redirect_url );
			$current_url_parsed  = wp_parse_url( $current_url );

			if ( ! empty( $redirect_url_parsed['path'] ) && ! empty( $current_url_parsed['path'] ) && $redirect_url_parsed['path'] === $current_url_parsed['path'] ) {
				return;
			}

			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( '4WP Account', '4wp-account' ),
			__( '4WP Account', '4wp-account' ),
			'manage_options',
			SettingsPage::PAGE_SLUG,
			array( SettingsPage::class, 'render' ),
			'dashicons-id',
			31
		);
	}
}
