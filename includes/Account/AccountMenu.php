<?php
/**
 * Account menu sections registry (Dashboard, Favorites, WC, LMS, …).
 *
 * @package ForWP\Account\Account
 */

namespace ForWP\Account\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines account navigation for header dropdown and account page sidebar.
 */
class AccountMenu {

	public const CONTEXT_HEADER = 'header';
	public const CONTEXT_PAGE   = 'page';

	public const OPTION_PAGE_ID = 'forwp_account_page_id';

	private const LEGACY_SECTIONS     = 'forwp_account_menu_sections';
	private const LEGACY_CUSTOM_LINKS = 'forwp_account_menu_custom_links';
	private const MIGRATED_FLAG       = 'forwp_account_menu_contexts_migrated';

	/**
	 * Default menu sections grouped by integration level.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_default_sections(): array {
		return array(
			'dashboard' => array(
				'label'       => __( 'Dashboard', '4wp-account' ),
				'description' => __( 'Overview and quick links for the signed-in user.', '4wp-account' ),
				'group'       => 'core',
				'enabled'     => true,
				'order'       => 10,
			),
			'favorites' => array(
				'label'       => __( 'Favorites', '4wp-account' ),
				'description' => __( 'Saved favorites from 4WP Notifications.', '4wp-account' ),
				'group'       => '4wp-notifications',
				'plugin'      => '4wp-notifications/4wp-notifications.php',
				'enabled'     => true,
				'order'       => 20,
			),
			'notifications' => array(
				'label'       => __( 'Notifications', '4wp-account' ),
				'description' => __( 'In-app notification inbox from 4WP Notifications.', '4wp-account' ),
				'group'       => '4wp-notifications',
				'plugin'      => '4wp-notifications/4wp-notifications.php',
				'enabled'     => true,
				'order'       => 25,
			),
			'woocommerce' => array(
				'label'       => __( 'WooCommerce', '4wp-account' ),
				'description' => __( 'Orders, addresses, and store account endpoints.', '4wp-account' ),
				'group'       => 'woocommerce',
				'plugin'      => 'woocommerce/woocommerce.php',
				'enabled'     => true,
				'order'       => 30,
			),
			'lms4wp'    => array(
				'label'       => __( 'LMS4WP', '4wp-account' ),
				'description' => __( 'Courses, progress, and learning paths.', '4wp-account' ),
				'group'       => 'lms4wp',
				'plugin'      => 'lms4wp/lms4wp.php',
				'enabled'     => true,
				'order'       => 40,
			),
			'other'     => array(
				'label'       => __( 'Other', '4wp-account' ),
				'description' => __( 'Additional links registered by other plugins.', '4wp-account' ),
				'group'       => 'other',
				'enabled'     => true,
				'order'       => 90,
			),
		);
	}

	/**
	 * @param string $context header|page.
	 */
	public static function sanitize_context( string $context ): string {
		return self::CONTEXT_PAGE === $context ? self::CONTEXT_PAGE : self::CONTEXT_HEADER;
	}

	/**
	 * Option key for section overrides.
	 *
	 * @param string $context header|page.
	 */
	private static function sections_option_key( string $context ): string {
		return self::CONTEXT_PAGE === $context
			? 'forwp_account_page_menu_sections'
			: 'forwp_account_header_menu_sections';
	}

	/**
	 * Option key for custom links.
	 *
	 * @param string $context header|page.
	 */
	private static function links_option_key( string $context ): string {
		return self::CONTEXT_PAGE === $context
			? 'forwp_account_page_menu_custom_links'
			: 'forwp_account_header_menu_custom_links';
	}

	/**
	 * Copy legacy unified menu settings into header + page contexts once.
	 */
	public static function maybe_migrate_legacy_menu_options(): void {
		if ( get_option( self::MIGRATED_FLAG ) ) {
			return;
		}

		$legacy_sections = get_option( self::LEGACY_SECTIONS, array() );
		$legacy_links    = get_option( self::LEGACY_CUSTOM_LINKS, array() );

		if ( is_array( $legacy_sections ) && $legacy_sections !== array() ) {
			if ( get_option( self::sections_option_key( self::CONTEXT_HEADER ), null ) === null ) {
				update_option( self::sections_option_key( self::CONTEXT_HEADER ), $legacy_sections, false );
			}
			if ( get_option( self::sections_option_key( self::CONTEXT_PAGE ), null ) === null ) {
				update_option( self::sections_option_key( self::CONTEXT_PAGE ), $legacy_sections, false );
			}
		}

		if ( is_array( $legacy_links ) && $legacy_links !== array() ) {
			if ( get_option( self::links_option_key( self::CONTEXT_HEADER ), null ) === null ) {
				update_option( self::links_option_key( self::CONTEXT_HEADER ), $legacy_links, false );
			}
			if ( get_option( self::links_option_key( self::CONTEXT_PAGE ), null ) === null ) {
				update_option( self::links_option_key( self::CONTEXT_PAGE ), $legacy_links, false );
			}
		}

		update_option( self::MIGRATED_FLAG, 1, false );
	}

	/**
	 * Merged sections: defaults + saved admin state.
	 *
	 * @param string $context header|page.
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_sections( string $context = self::CONTEXT_PAGE ): array {
		self::maybe_migrate_legacy_menu_options();

		$context  = self::sanitize_context( $context );
		$defaults = self::get_default_sections();
		$saved    = get_option( self::sections_option_key( $context ), array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$merged = array();

		foreach ( $defaults as $key => $default ) {
			$override = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();
			$merged[ $key ] = array_merge( $default, $override );

			if ( ! empty( $override['label'] ) ) {
				$merged[ $key ]['label'] = sanitize_text_field( (string) $override['label'] );
			}
		}

		uasort(
			$merged,
			static function ( array $a, array $b ): int {
				return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
			}
		);

		/**
		 * Filter account menu sections before rendering.
		 *
		 * @param array<string, array<string, mixed>> $merged   Sections keyed by slug.
		 * @param string                              $context  header|page.
		 */
		return apply_filters( 'forwp_account_menu_sections', $merged, $context );
	}

	/**
	 * Custom menu links for a context.
	 *
	 * @param string $context header|page.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_custom_links( string $context = self::CONTEXT_PAGE ): array {
		self::maybe_migrate_legacy_menu_options();

		$context = self::sanitize_context( $context );
		$saved   = get_option( self::links_option_key( $context ), array() );

		if ( ! is_array( $saved ) ) {
			return array();
		}

		$links = array();

		foreach ( $saved as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$id = isset( $row['id'] ) ? sanitize_key( (string) $row['id'] ) : '';
			if ( $id === '' ) {
				continue;
			}

			$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
			$url   = isset( $row['url'] ) ? self::resolve_link_url( (string) $row['url'] ) : '';

			if ( $label === '' || $url === '' ) {
				continue;
			}

			$links[] = array(
				'id'      => $id,
				'label'   => $label,
				'url'     => $url,
				'order'   => isset( $row['order'] ) ? absint( $row['order'] ) : 50,
				'enabled' => ! array_key_exists( 'enabled', $row ) || ! empty( $row['enabled'] ),
				'target'  => ( ! empty( $row['target'] ) && '_blank' === $row['target'] ) ? '_blank' : '',
			);
		}

		usort(
			$links,
			static function ( array $a, array $b ): int {
				return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
			}
		);

		/**
		 * Filter custom account menu links.
		 *
		 * @param array<int, array<string, mixed>> $links   Custom link rows.
		 * @param string                           $context header|page.
		 */
		return apply_filters( 'forwp_account_menu_custom_links', $links, $context );
	}

	/**
	 * @param string $context header|page.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_active_custom_links( string $context = self::CONTEXT_PAGE ): array {
		$active = array();

		foreach ( self::get_custom_links( $context ) as $link ) {
			if ( empty( $link['enabled'] ) ) {
				continue;
			}

			$active[] = $link;
		}

		return $active;
	}

	/**
	 * Raw custom links for admin forms.
	 *
	 * @param string $context header|page.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_stored_custom_links( string $context = self::CONTEXT_PAGE ): array {
		self::maybe_migrate_legacy_menu_options();

		$context = self::sanitize_context( $context );
		$saved   = get_option( self::links_option_key( $context ), array() );

		return is_array( $saved ) ? $saved : array();
	}

	/**
	 * Built-in sections + custom links (sorted by order).
	 *
	 * @param string $context header|page.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_nav_items( string $context = self::CONTEXT_PAGE ): array {
		$context = self::sanitize_context( $context );
		$items   = array();

		foreach ( self::get_active_sections( $context ) as $key => $section ) {
			$items[] = array(
				'id'     => (string) $key,
				'type'   => 'section',
				'label'  => (string) ( $section['label'] ?? $key ),
				'url'    => self::get_section_url( (string) $key ),
				'order'  => (int) ( $section['order'] ?? 0 ),
				'target' => '',
			);
		}

		foreach ( self::get_active_custom_links( $context ) as $link ) {
			$items[] = array(
				'id'     => (string) ( $link['id'] ?? '' ),
				'type'   => 'link',
				'label'  => (string) ( $link['label'] ?? '' ),
				'url'    => (string) ( $link['url'] ?? '' ),
				'order'  => (int) ( $link['order'] ?? 50 ),
				'target' => (string) ( $link['target'] ?? '' ),
			);
		}

		usort(
			$items,
			static function ( array $a, array $b ): int {
				return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
			}
		);

		/**
		 * Filter merged account navigation items.
		 *
		 * @param array<int, array<string, mixed>> $items   Nav rows.
		 * @param string                           $context header|page.
		 */
		return apply_filters( 'forwp_account_nav_items', $items, $context );
	}

	/**
	 * @param string $context header|page.
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_active_sections( string $context = self::CONTEXT_PAGE ): array {
		$sections = self::get_sections( $context );
		$active   = array();

		foreach ( $sections as $key => $section ) {
			if ( empty( $section['enabled'] ) ) {
				continue;
			}

			if ( ! empty( $section['plugin'] ) && ! self::is_plugin_active( (string) $section['plugin'] ) ) {
				continue;
			}

			$active[ $key ] = $section;
		}

		return $active;
	}

	/**
	 * @param string $plugin_file Plugin bootstrap file relative to wp-content/plugins.
	 */
	public static function is_plugin_active( string $plugin_file ): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file );
	}

	/**
	 * Whether 4WP Notifications is available for cabinet sections.
	 */
	public static function is_notifications_active(): bool {
		return self::is_plugin_active( '4wp-notifications/4wp-notifications.php' );
	}

	/**
	 * @param array<string, mixed> $input   Raw POST data keyed by section slug.
	 * @param string               $context header|page.
	 */
	public static function save_sections( array $input, string $context = self::CONTEXT_PAGE ): void {
		$context  = self::sanitize_context( $context );
		$defaults = self::get_default_sections();
		$saved    = array();

		foreach ( $defaults as $key => $default ) {
			$row = isset( $input[ $key ] ) && is_array( $input[ $key ] ) ? $input[ $key ] : array();

			$saved[ $key ] = array(
				'enabled' => ! empty( $row['enabled'] ) ? true : false,
				'order'   => isset( $row['order'] ) ? absint( $row['order'] ) : (int) ( $default['order'] ?? 0 ),
			);

			$label         = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
			$default_label = (string) ( $default['label'] ?? '' );

			if ( $label !== '' && $label !== $default_label ) {
				$saved[ $key ]['label'] = $label;
			}
		}

		update_option( self::sections_option_key( $context ), $saved, false );
	}

	/**
	 * @param array<string, mixed> $input   Raw POST rows keyed by row id or index.
	 * @param string               $context header|page.
	 */
	public static function save_custom_links( array $input, string $context = self::CONTEXT_PAGE ): void {
		$context = self::sanitize_context( $context );
		$saved   = array();

		foreach ( $input as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			if ( ! empty( $row['_delete'] ) ) {
				continue;
			}

			$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
			$url   = isset( $row['url'] ) ? trim( (string) $row['url'] ) : '';

			if ( $label === '' || $url === '' ) {
				continue;
			}

			$id = isset( $row['id'] ) ? sanitize_key( (string) $row['id'] ) : '';
			if ( $id === '' ) {
				$id = 'link_' . wp_generate_password( 8, false, false );
			}

			$saved[] = array(
				'id'      => $id,
				'label'   => $label,
				'url'     => self::resolve_link_url( $url ),
				'order'   => isset( $row['order'] ) ? absint( $row['order'] ) : 50,
				'enabled' => ! empty( $row['enabled'] ),
				'target'  => ( ! empty( $row['target'] ) && '_blank' === $row['target'] ) ? '_blank' : '',
			);
		}

		usort(
			$saved,
			static function ( array $a, array $b ): int {
				return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
			}
		);

		update_option( self::links_option_key( $context ), $saved, false );
	}

	/**
	 * Normalize admin-entered URLs (absolute or site-relative).
	 *
	 * @param string $url Raw URL or path.
	 */
	private static function resolve_link_url( string $url ): string {
		$url = trim( $url );

		if ( $url === '' ) {
			return '';
		}

		if ( preg_match( '#^https?://#i', $url ) ) {
			return esc_url_raw( $url );
		}

		if ( str_starts_with( $url, '/' ) ) {
			return esc_url_raw( home_url( $url ) );
		}

		return esc_url_raw( home_url( '/' . ltrim( $url, '/' ) ) );
	}

	/**
	 * Account page post ID (0 = not set).
	 */
	public static function get_account_page_id(): int {
		return absint( get_option( self::OPTION_PAGE_ID, 0 ) );
	}

	/**
	 * @param int $page_id Page ID or 0.
	 */
	public static function set_account_page_id( int $page_id ): void {
		update_option( self::OPTION_PAGE_ID, $page_id, false );
	}

	/**
	 * Resolved account page URL.
	 */
	public static function get_account_page_url(): string {
		$page_id = self::get_account_page_id();

		if ( $page_id > 0 ) {
			$url = get_permalink( $page_id );
			if ( is_string( $url ) && $url !== '' ) {
				return $url;
			}
		}

		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$wc_url = wc_get_page_permalink( 'myaccount' );
			if ( is_string( $wc_url ) && $wc_url !== '' ) {
				return $wc_url;
			}
		}

		return home_url( '/my-account/' );
	}

	/**
	 * Whether the current request is the configured account page.
	 */
	public static function is_account_page(): bool {
		if ( ! is_singular( 'page' ) ) {
			return false;
		}

		$page_id = self::get_account_page_id();

		if ( $page_id > 0 ) {
			return (int) get_queried_object_id() === $page_id;
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return true;
		}

		return false;
	}

	/**
	 * Active section slug from ?section= query arg.
	 */
	public static function get_current_section(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'dashboard';

		return $section !== '' ? $section : 'dashboard';
	}

	/**
	 * URL for an account section on the account page.
	 *
	 * @param string $section Section slug.
	 */
	public static function get_section_url( string $section ): string {
		$base = self::get_account_page_url();
		$url  = add_query_arg( 'section', sanitize_key( $section ), $base );

		/**
		 * Filter URL for an account section.
		 *
		 * @param string $url     Section URL.
		 * @param string $section Section slug.
		 */
		return (string) apply_filters( 'forwp_account_section_url', $url, $section );
	}

	/**
	 * Sign-in URL for guests (account page, not wp-login.php).
	 *
	 * @param string $redirect Optional URL to return to after sign-in.
	 */
	public static function get_login_url( string $redirect = '' ): string {
		$url = self::get_account_page_url();

		if ( $redirect !== '' ) {
			$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
		}

		/**
		 * Filter sign-in URL for 4WP Account.
		 *
		 * @param string $url      Sign-in URL.
		 * @param string $redirect Redirect target after sign-in.
		 */
		return (string) apply_filters( 'forwp_account_login_url', $url, $redirect );
	}
}
