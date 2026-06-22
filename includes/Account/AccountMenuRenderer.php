<?php
/**
 * Header dropdown: classic account cabinet menu.
 *
 * @package ForWP\Account\Account
 */

namespace ForWP\Account\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the forwp/account-menu block markup.
 */
class AccountMenuRenderer {

	/**
	 * Register widget assets.
	 */
	public static function register_assets(): void {
		wp_register_style(
			'forwp-account-menu-widget',
			FORWP_ACCOUNT_PLUGIN_URL . 'assets/css/account-menu.css',
			array(),
			FORWP_ACCOUNT_VERSION
		);
		wp_register_script(
			'forwp-account-menu-widget',
			FORWP_ACCOUNT_PLUGIN_URL . 'assets/js/account-menu.js',
			array(),
			FORWP_ACCOUNT_VERSION,
			true
		);
	}

	/**
	 * Enqueue widget assets.
	 */
	public static function enqueue_assets(): void {
		self::register_assets();
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'forwp-account-menu-widget' );
		wp_enqueue_script( 'forwp-account-menu-widget' );
	}

	/**
	 * Resolve account page URL override.
	 *
	 * @param string $account_url Optional override URL.
	 */
	public static function resolve_account_url( string $account_url = '' ): string {
		$account_url = apply_filters( 'forwp_account_menu_account_url', $account_url );
		if ( $account_url !== '' ) {
			return $account_url;
		}

		return AccountMenu::get_account_page_url();
	}

	/**
	 * Icon class for a nav item.
	 *
	 * @param array<string, mixed> $item Nav row.
	 */
	public static function get_item_icon_class( array $item ): string {
		$type = (string) ( $item['type'] ?? '' );
		$id   = (string) ( $item['id'] ?? '' );

		if ( 'link' === $type ) {
			return 'dashicons-admin-links';
		}

		$map = array(
			'dashboard'     => 'dashicons-dashboard',
			'favorites'     => 'dashicons-heart',
			'notifications' => 'dashicons-bell',
			'woocommerce'   => 'dashicons-cart',
			'lms4wp'        => 'dashicons-welcome-learn-more',
			'other'         => 'dashicons-admin-generic',
		);

		$class = isset( $map[ $id ] ) ? $map[ $id ] : 'dashicons-admin-users';

		return (string) apply_filters( 'forwp_account_menu_item_icon_class', $class, $item );
	}

	/**
	 * Render account menu widget HTML.
	 *
	 * @param array<string, mixed> $args accountUrl.
	 * @return string
	 */
	public static function render( array $args = array() ): string {
		$args = wp_parse_args(
			$args,
			array(
				'account_url' => '',
			)
		);

		if ( ! is_user_logged_in() ) {
			return self::render_guest();
		}

		self::enqueue_assets();

		$user         = wp_get_current_user();
		$account_url  = self::resolve_account_url( (string) $args['account_url'] );
		$items        = AccountMenu::get_nav_items( AccountMenu::CONTEXT_HEADER );
		$current      = AccountMenu::is_account_page() ? AccountMenu::get_current_section() : '';
		$logout_url   = wp_logout_url( $account_url );
		$login_url    = AccountMenu::get_login_url( is_singular() ? (string) get_permalink() : '' );

		$i18n = array(
			'myAccount' => __( 'My account', '4wp-account' ),
			'logOut'    => __( 'Log out', '4wp-account' ),
			'signIn'    => __( 'Sign in', '4wp-account' ),
		);

		wp_localize_script( 'forwp-account-menu-widget', 'forwpAccountMenuI18n', $i18n );

		ob_start();
		?>
		<div class="forwp-account-menu" data-forwp-account-menu="1" data-forwp-i18n="<?php echo esc_attr( wp_json_encode( $i18n ) ); ?>">
			<button type="button" class="forwp-account-menu__button" aria-label="<?php esc_attr_e( 'Account menu', '4wp-account' ); ?>" aria-expanded="false" aria-haspopup="true">
				<span class="forwp-account-menu__icon" aria-hidden="true">
					<?php echo AccountRenderer::get_nav_icon_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from helper. ?>
				</span>
			</button>
			<div class="forwp-account-menu__dropdown">
				<div class="forwp-account-menu__dropdown-header">
					<p class="forwp-account-menu__user-name"><?php echo esc_html( $user->display_name ); ?></p>
					<p class="forwp-account-menu__user-email"><?php echo esc_html( $user->user_email ); ?></p>
				</div>
				<nav class="forwp-account-menu__list" aria-label="<?php esc_attr_e( 'Account sections', '4wp-account' ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<?php self::render_item( $item, $current ); ?>
					<?php endforeach; ?>
				</nav>
				<div class="forwp-account-menu__footer">
					<a class="forwp-account-menu__footer-link" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'My account', '4wp-account' ); ?></a>
					<a class="forwp-account-menu__footer-link forwp-account-menu__footer-link--logout" href="<?php echo esc_url( $logout_url ); ?>"><?php esc_html_e( 'Log out', '4wp-account' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		unset( $login_url );

		return (string) ob_get_clean();
	}

	/**
	 * Guest widget: icon opens dropdown with sign-in link.
	 *
	 * @return string
	 */
	private static function render_guest(): string {
		self::enqueue_assets();

		$login_url = AccountMenu::get_login_url(
			is_singular() ? (string) get_permalink() : ''
		);

		ob_start();
		?>
		<div class="forwp-account-menu forwp-account-menu--guest" data-forwp-account-menu="1">
			<button type="button" class="forwp-account-menu__button" aria-label="<?php esc_attr_e( 'Account menu', '4wp-account' ); ?>" aria-expanded="false" aria-haspopup="true">
				<span class="forwp-account-menu__icon" aria-hidden="true">
					<?php echo AccountRenderer::get_nav_icon_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from helper. ?>
				</span>
			</button>
			<div class="forwp-account-menu__dropdown">
				<nav class="forwp-account-menu__list" aria-label="<?php esc_attr_e( 'Account menu', '4wp-account' ); ?>">
					<a href="<?php echo esc_url( $login_url ); ?>" class="forwp-account-menu__item">
						<span class="forwp-account-menu__item-icon"><span class="dashicons dashicons-admin-users" aria-hidden="true"></span></span>
						<span class="forwp-account-menu__item-label"><?php esc_html_e( 'Sign in', '4wp-account' ); ?></span>
					</a>
				</nav>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Output a single nav row.
	 *
	 * @param array<string, mixed> $item    Nav row.
	 * @param string               $current Active section slug.
	 */
	public static function render_item( array $item, string $current = '' ): void {
		$id     = (string) ( $item['id'] ?? '' );
		$label  = (string) ( $item['label'] ?? '' );
		$url    = (string) ( $item['url'] ?? '#' );
		$target = (string) ( $item['target'] ?? '' );
		$icon   = self::get_item_icon_class( $item );
		$active = ( 'section' === ( $item['type'] ?? '' ) && $id !== '' && $id === $current );
		$class  = 'forwp-account-menu__item' . ( $active ? ' forwp-account-menu__item--active' : '' );
		$attrs  = '';

		if ( '_blank' === $target ) {
			$attrs = ' target="_blank" rel="noopener noreferrer"';
		}
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>"<?php echo $active ? ' aria-current="page"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="forwp-account-menu__item-icon"><span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span></span>
			<span class="forwp-account-menu__item-label"><?php echo esc_html( $label ); ?></span>
			<?php if ( '_blank' === $target ) : ?>
				<span class="forwp-account-menu__item-external dashicons dashicons-external" aria-hidden="true"></span>
			<?php endif; ?>
		</a>
		<?php
	}
}
