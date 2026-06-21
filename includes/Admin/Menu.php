<?php
/**
 * Admin Menu
 *
 * @package ForWP\Auth\Admin
 */

namespace ForWP\Auth\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menu class
 */
class Menu {

	/**
	 * Plugin instance
	 *
	 * @var Menu
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
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
		// Admin-only hooks
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'redirect_subscribers_from_admin' ) );
		}

		// Hide toolbar for subscribers if enabled (works on frontend and admin)
		add_filter( 'show_admin_bar', array( $this, 'hide_toolbar_for_subscribers' ), 999 );
	}

	/**
	 * Hide toolbar for subscribers
	 *
	 * @param bool $show Whether to show the toolbar.
	 * @return bool
	 */
	public function hide_toolbar_for_subscribers( $show ) {
		// Only hide if the option is enabled
		if ( get_option( 'forwp_auth_hide_toolbar_subscribers', '0' ) !== '1' ) {
			return $show;
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return $show;
		}

		// Get current user
		$user = wp_get_current_user();

		// Hide toolbar for users with subscriber role only
		if ( ! empty( $user->roles ) && in_array( 'subscriber', $user->roles, true ) && ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Redirect subscribers from admin area
	 */
	public function redirect_subscribers_from_admin() {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Get redirect URL from settings
		$redirect_url = get_option( 'forwp_auth_subscriber_redirect_url', '' );
		if ( empty( $redirect_url ) ) {
			return;
		}

		// Skip redirect for AJAX requests
		if ( wp_doing_ajax() ) {
			return;
		}

		// Skip redirect for admin-ajax.php and admin-post.php
		global $pagenow;
		if ( in_array( $pagenow, array( 'admin-ajax.php', 'admin-post.php' ), true ) ) {
			return;
		}

		// Get current user
		$user = wp_get_current_user();

		// Check if user is subscriber and can't edit posts
		if ( ! empty( $user->roles ) && in_array( 'subscriber', $user->roles, true ) && ! current_user_can( 'edit_posts' ) ) {
			// Convert relative URL to absolute if needed
			if ( ! empty( $redirect_url ) && strpos( $redirect_url, 'http' ) !== 0 ) {
				// It's a relative path, prepend home_url
				$redirect_url = home_url( $redirect_url );
			}

			// Skip redirect if already on the redirect URL.
			$request_uri         = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$current_url         = home_url( $request_uri );
			$redirect_url_parsed = wp_parse_url( $redirect_url );
			$current_url_parsed  = wp_parse_url( $current_url );

			// Compare paths to avoid redirect loops
			if ( ! empty( $redirect_url_parsed['path'] ) && ! empty( $current_url_parsed['path'] ) ) {
				if ( $redirect_url_parsed['path'] === $current_url_parsed['path'] ) {
					return;
				}
			}

			// Redirect to custom URL
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
			'forwp-account',
			array( SettingsPage::class, 'render' ),
			'dashicons-groups',
			30
		);

		add_submenu_page(
			'forwp-account',
			__( 'Settings', '4wp-account' ),
			__( 'Settings', '4wp-account' ),
			'manage_options',
			'forwp-account',
			array( SettingsPage::class, 'render' )
		);
	}
}
