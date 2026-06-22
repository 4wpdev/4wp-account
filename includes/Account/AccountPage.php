<?php
/**
 * Account page routing, shortcodes, assets, nav menu icons.
 *
 * @package ForWP\Account\Account
 */

namespace ForWP\Account\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend account hub (auth + cabinet on one page).
 */
class AccountPage {

	/**
	 * Bootstrap hooks.
	 */
	public static function init(): void {
		add_shortcode( 'forwp_account', array( self::class, 'shortcode_account' ) );
		add_shortcode( 'forwp_account_link', array( self::class, 'shortcode_account_link' ) );
		add_shortcode( 'forwp_account_menu', array( self::class, 'shortcode_account_menu' ) );

		add_filter( 'forwp_account_redirect_url', array( self::class, 'default_auth_redirect' ) );
		add_filter( 'the_content', array( self::class, 'maybe_replace_wc_shortcode' ), 5 );
		add_filter( 'the_content', array( self::class, 'maybe_inject_account_shell' ), 99 );
		add_filter( 'walker_nav_menu_start_el', array( self::class, 'inject_nav_menu_icon' ), 10, 4 );
		add_filter( 'nav_menu_link_attributes', array( self::class, 'nav_menu_link_attributes' ), 10, 3 );

		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public static function shortcode_account( $atts = array() ): string {
		unset( $atts );
		self::ensure_auth_assets();

		return AccountRenderer::render();
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public static function shortcode_account_link( $atts = array() ): string {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		return AccountRenderer::render_nav_link( $atts );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public static function shortcode_account_menu( $atts = array() ): string {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			array(
				'account_url' => '',
			),
			$atts,
			'forwp_account_menu'
		);

		return AccountMenuRenderer::render(
			array(
				'account_url' => (string) $atts['account_url'],
			)
		);
	}

	/**
	 * After Google OAuth, land on the account page.
	 *
	 * @param string $url Default redirect URL.
	 */
	public static function default_auth_redirect( string $url ): string {
		$account_url = AccountMenu::get_account_page_url();

		return $account_url !== '' ? $account_url : $url;
	}

	/**
	 * Swap WooCommerce my-account shortcode on the configured account page.
	 *
	 * @param string $content Post content.
	 */
	public static function maybe_replace_wc_shortcode( string $content ): string {
		if ( ! AccountMenu::is_account_page() || is_admin() ) {
			return $content;
		}

		if ( has_shortcode( $content, 'forwp_account' ) ) {
			return $content;
		}

		if ( has_shortcode( $content, 'woocommerce_my_account' ) ) {
			return '[forwp_account]';
		}

		return $content;
	}

	/**
	 * Render account shell on the configured page when no shortcode was added.
	 *
	 * @param string $content Post content.
	 */
	public static function maybe_inject_account_shell( string $content ): string {
		if ( ! AccountMenu::is_account_page() || is_admin() ) {
			return $content;
		}

		if ( str_contains( $content, 'forwp-account' ) || has_shortcode( $content, 'forwp_account' ) ) {
			return $content;
		}

		self::ensure_auth_assets();

		return AccountRenderer::render();
	}

	/**
	 * Add icon + class to menu items that point at the account page.
	 *
	 * @param string   $item_output Menu item HTML.
	 * @param \WP_Post $item        Menu item object.
	 * @param int      $depth       Depth.
	 * @param mixed    $args        Walker args.
	 */
	public static function inject_nav_menu_icon( string $item_output, $item, int $depth, $args ): string {
		unset( $args );

		if ( $depth > 0 || ! $item instanceof \WP_Post ) {
			return $item_output;
		}

		if ( ! self::is_account_menu_item( $item ) ) {
			return $item_output;
		}

		$icon = AccountRenderer::get_nav_icon_html();

		if ( false === strpos( $item_output, 'forwp-account-nav-icon' ) ) {
			$item_output = preg_replace( '/(<a\b[^>]*>)/i', '$1' . $icon, $item_output, 1 ) ?? $item_output;
		}

		return $item_output;
	}

	/**
	 * @param array<string, string> $atts  Link attributes.
	 * @param \WP_Post              $item  Menu item.
	 * @param mixed                 $args  Walker args.
	 * @return array<string, string>
	 */
	public static function nav_menu_link_attributes( array $atts, $item, $args ): array {
		unset( $args );

		if ( $item instanceof \WP_Post && self::is_account_menu_item( $item ) ) {
			$existing = isset( $atts['class'] ) ? (string) $atts['class'] : '';
			$atts['class'] = trim( $existing . ' forwp-account-nav-item' );
		}

		return $atts;
	}

	/**
	 * @param \WP_Post $item Menu item.
	 */
	private static function is_account_menu_item( \WP_Post $item ): bool {
		if ( in_array( 'forwp-account-nav-item', (array) $item->classes, true ) ) {
			return true;
		}

		$account_url = AccountMenu::get_account_page_url();
		if ( $account_url === '' || empty( $item->url ) ) {
			return false;
		}

		return self::urls_match( (string) $item->url, $account_url );
	}

	/**
	 * Compare paths (ignore scheme/host/trailing slash).
	 *
	 * @param string $a First URL.
	 * @param string $b Second URL.
	 */
	private static function urls_match( string $a, string $b ): bool {
		$path_a = untrailingslashit( (string) wp_parse_url( $a, PHP_URL_PATH ) );
		$path_b = untrailingslashit( (string) wp_parse_url( $b, PHP_URL_PATH ) );

		return $path_a !== '' && $path_a === $path_b;
	}

	/**
	 * Frontend styles for account layout + nav icons.
	 */
	public static function enqueue_assets(): void {
		if ( ! self::should_load_account_styles() ) {
			return;
		}

		wp_enqueue_style(
			'forwp-account',
			FORWP_ACCOUNT_PLUGIN_URL . 'assets/css/account.css',
			array(),
			FORWP_ACCOUNT_VERSION
		);
	}

	/**
	 * Load auth script on account page for guests.
	 */
	public static function ensure_auth_assets(): void {
		if ( is_user_logged_in() || ! class_exists( '\ForWP\Account\Auth\AuthManager' ) ) {
			return;
		}

		$manager = \ForWP\Account\Auth\AuthManager::get_instance();
		$manager->enqueue_scripts_for_context();
	}

	/**
	 * @return bool
	 */
	private static function should_load_account_styles(): bool {
		if ( AccountMenu::is_account_page() ) {
			return true;
		}

		if ( is_singular() ) {
			global $post;
			if ( $post instanceof \WP_Post && has_shortcode( (string) $post->post_content, 'forwp_account' ) ) {
				return true;
			}
		}

		return (bool) apply_filters( 'forwp_account_enqueue_assets', false );
	}
}
