<?php
/**
 * Replace wp-login.php only when a published account page already hosts an auth form.
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

use ForWP\Account\Account\AccountMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safe gate in front of the default WordPress login screen.
 */
class WpLoginGate {

	public const OPTION = 'forwp_account_replace_wp_login';

	/**
	 * Shortcodes that render a sign-in form on a page.
	 *
	 * @var string[]
	 */
	private const AUTH_SHORTCODES = array(
		'forwp_account',
		'forwp_account_signin_buttons',
		'forwp_account_login',
		'woocommerce_my_account',
	);

	/**
	 * Blocks that render a sign-in form on a page.
	 *
	 * @var string[]
	 */
	private const AUTH_BLOCKS = array(
		'forwp/account',
		'forwp/auth-buttons',
	);

	/**
	 * Bootstrap.
	 */
	public static function init(): void {
		add_action( 'login_init', array( self::class, 'maybe_redirect' ) );
		add_filter( 'login_url', array( self::class, 'filter_login_url' ), 10, 3 );
	}

	/**
	 * Option is on and the configured page still has an auth entry.
	 */
	public static function is_active(): bool {
		if ( '1' !== get_option( self::OPTION, '0' ) ) {
			return false;
		}

		return self::has_entry_point();
	}

	/**
	 * Explicit published page with a login block or shortcode.
	 */
	public static function has_entry_point(): bool {
		return self::page_has_auth_entry( AccountMenu::get_account_page_id() );
	}

	/**
	 * @param int $page_id Page ID.
	 */
	public static function page_has_auth_entry( int $page_id ): bool {
		if ( $page_id <= 0 ) {
			return false;
		}

		$post = get_post( $page_id );
		if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}

		return '' !== self::detect_auth_marker( $post );
	}

	/**
	 * Human-readable marker found on the page, or empty.
	 *
	 * @param \WP_Post $post Page.
	 */
	public static function detect_auth_marker( \WP_Post $post ): string {
		foreach ( self::AUTH_BLOCKS as $block ) {
			if ( has_block( $block, $post ) ) {
				return $block;
			}
		}

		$content = (string) $post->post_content;
		foreach ( self::AUTH_SHORTCODES as $shortcode ) {
			if ( has_shortcode( $content, $shortcode ) ) {
				return '[' . $shortcode . ']';
			}
		}

		return '';
	}

	/**
	 * Persist the toggle only when the page is a real entry point.
	 *
	 * @param bool $want  Requested on/off.
	 * @param int  $page_id Page being saved.
	 */
	public static function set_enabled( bool $want, int $page_id ): void {
		$on = $want && self::page_has_auth_entry( $page_id );
		update_option( self::OPTION, $on ? '1' : '0', false );
	}

	/**
	 * Send visitors from wp-login.php to the account page.
	 * Logout, password reset, and POST login stay on wp-login.php.
	 */
	public static function maybe_redirect(): void {
		if ( ! self::is_active() ) {
			return;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$keep   = array(
			'logout',
			'lostpassword',
			'retrievepassword',
			'resetpass',
			'rp',
			'postpass',
			'confirmaction',
		);

		if ( in_array( $action, $keep, true ) ) {
			return;
		}

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		$url = AccountMenu::get_login_url();
		if ( $url === '' ) {
			return;
		}

		if ( ! empty( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$redirect = esc_url_raw( wp_unslash( (string) $_REQUEST['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $redirect !== '' ) {
				$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
			}
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * @param string $login_url    Core login URL.
	 * @param string $redirect     Redirect after login.
	 * @param bool   $force_reauth Unused.
	 */
	public static function filter_login_url( string $login_url, string $redirect, bool $force_reauth ): string {
		unset( $force_reauth );

		if ( ! self::is_active() ) {
			return $login_url;
		}

		$custom = AccountMenu::get_login_url( $redirect );

		return $custom !== '' ? $custom : $login_url;
	}
}
