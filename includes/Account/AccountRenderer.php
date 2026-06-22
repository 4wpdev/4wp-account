<?php
/**
 * Account page shell: guest auth or logged-in layout (WC-style).
 *
 * @package ForWP\Account\Account
 */

namespace ForWP\Account\Account;

use ForWP\Account\Auth\ProviderSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders account page markup.
 */
class AccountRenderer {

	/**
	 * Full account page output.
	 */
	public static function render(): string {
		if ( is_user_logged_in() ) {
			return self::render_logged_in();
		}

		return self::render_guest();
	}

	/**
	 * Guest: Google / social sign-in.
	 */
	public static function render_guest(): string {
		self::persist_redirect_target_from_request();

		$providers = ProviderSettings::get_enabled_for_display();
		$lead      = self::get_guest_lead_text( $providers );

		ob_start();
		?>
		<div class="forwp-account forwp-account--guest">
			<div class="forwp-account-guest">
				<h2 class="forwp-account-guest__title"><?php esc_html_e( 'Sign in to your account', '4wp-account' ); ?></h2>
				<p class="forwp-account-guest__lead"><?php echo esc_html( $lead ); ?></p>

				<?php if ( empty( $providers ) ) : ?>
					<p class="forwp-account-notice forwp-account-notice--warning">
						<?php esc_html_e( 'Sign-in is not configured yet. Enable Google in 4WP Account → Auth.', '4wp-account' ); ?>
					</p>
				<?php else : ?>
					<div class="forwp-account-signin forwp-account-signin--account">
						<?php foreach ( $providers as $provider_key => $label ) : ?>
							<button type="button" class="forwp-account-signin-btn forwp-account-signin-btn-<?php echo esc_attr( $provider_key ); ?>" data-provider="<?php echo esc_attr( $provider_key ); ?>">
								<?php echo esc_html( $label ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php self::render_auth_error_notice(); ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Logged in: left nav + section body.
	 */
	public static function render_logged_in(): string {
		$sections = AccountMenu::get_active_sections( AccountMenu::CONTEXT_PAGE );
		$current  = AccountMenu::get_current_section();
		$user     = wp_get_current_user();

		if ( ! isset( $sections[ $current ] ) ) {
			$keys    = array_keys( $sections );
			$current = $keys[0] ?? 'dashboard';
		}

		ob_start();
		?>
		<div class="forwp-account forwp-account--logged-in">
			<nav class="forwp-account-nav" aria-label="<?php esc_attr_e( 'Account menu', '4wp-account' ); ?>">
				<p class="forwp-account-nav__user">
					<strong><?php echo esc_html( $user->display_name ); ?></strong>
					<span><?php echo esc_html( $user->user_email ); ?></span>
				</p>
				<ul>
					<?php foreach ( AccountMenu::get_nav_items( AccountMenu::CONTEXT_PAGE ) as $item ) : ?>
						<?php
						$is_section = ( $item['type'] ?? '' ) === 'section';
						$is_active  = $is_section && (string) ( $item['id'] ?? '' ) === $current;
						$target     = (string) ( $item['target'] ?? '' );
						$target_attr = '_blank' === $target ? ' target="_blank" rel="noopener noreferrer"' : '';
						?>
						<li class="<?php echo esc_attr( trim( ( $is_active ? 'is-active' : '' ) . ( $is_section ? '' : ' forwp-account-nav__custom-link' ) ) ); ?>">
							<a href="<?php echo esc_url( (string) ( $item['url'] ?? '#' ) ); ?>"<?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $is_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?>
								<?php if ( '_blank' === $target ) : ?>
									<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', '4wp-account' ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
					<li class="forwp-account-nav__logout">
						<a href="<?php echo esc_url( wp_logout_url( AccountMenu::get_account_page_url() ) ); ?>">
							<?php esc_html_e( 'Log out', '4wp-account' ); ?>
						</a>
					</li>
				</ul>
			</nav>

			<div class="forwp-account-content">
				<?php echo self::render_section( $current ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param string $section Section slug.
	 */
	public static function render_section( string $section ): string {
		$sections = AccountMenu::get_active_sections( AccountMenu::CONTEXT_PAGE );
		$label    = isset( $sections[ $section ]['label'] ) ? (string) $sections[ $section ]['label'] : ucfirst( $section );

		ob_start();
		?>
		<div class="forwp-account-section forwp-account-section--<?php echo esc_attr( $section ); ?>">
			<h2 class="forwp-account-section__title"><?php echo esc_html( $label ); ?></h2>
			<div class="forwp-account-section__body">
				<?php
				$custom = apply_filters( 'forwp_account_section_content', null, $section, $sections[ $section ] ?? array() );
				if ( is_string( $custom ) && $custom !== '' ) {
					echo $custom; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo self::render_default_section( $section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Built-in section bodies.
	 *
	 * @param string $section Section slug.
	 */
	private static function render_default_section( string $section ): string {
		switch ( $section ) {
			case 'dashboard':
				return self::render_dashboard();

			case 'favorites':
				return self::render_placeholder(
					__( 'Saved favorites will appear here.', '4wp-account' ),
					AccountMenu::is_notifications_active()
						? __( 'Enable 4WP Notifications on this site to load favorites.', '4wp-account' )
						: __( 'Install and activate 4WP Notifications.', '4wp-account' )
				);

			case 'notifications':
				return self::render_placeholder(
					__( 'Your notifications will appear here.', '4wp-account' ),
					AccountMenu::is_notifications_active()
						? __( 'Enable 4WP Notifications on this site to load the inbox.', '4wp-account' )
						: __( 'Install and activate 4WP Notifications.', '4wp-account' )
				);

			case 'woocommerce':
				return self::render_woocommerce();

			case 'lms4wp':
				return self::render_placeholder(
					__( 'Your courses and progress will appear here.', '4wp-account' ),
					__( 'LMS4WP integration — coming soon.', '4wp-account' )
				);

			default:
				return self::render_placeholder(
					__( 'This section is not configured yet.', '4wp-account' )
				);
		}
	}

	/**
	 * Dashboard overview.
	 */
	private static function render_dashboard(): string {
		$user = wp_get_current_user();

		ob_start();
		?>
		<p><?php esc_html_e( 'Welcome back. Choose a section in the menu to manage your account.', '4wp-account' ); ?></p>
		<dl class="forwp-account-dl">
			<dt><?php esc_html_e( 'Name', '4wp-account' ); ?></dt>
			<dd><?php echo esc_html( $user->display_name ); ?></dd>
			<dt><?php esc_html_e( 'Email', '4wp-account' ); ?></dt>
			<dd><?php echo esc_html( $user->user_email ); ?></dd>
		</dl>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * WooCommerce shortcuts (native endpoints stay on WC my-account URLs).
	 */
	private static function render_woocommerce(): string {
		if ( ! function_exists( 'wc_get_account_menu_items' ) || ! function_exists( 'wc_get_account_endpoint_url' ) ) {
			return self::render_placeholder( __( 'WooCommerce is not active.', '4wp-account' ) );
		}

		$items = wc_get_account_menu_items();
		if ( empty( $items ) ) {
			return self::render_placeholder( __( 'No store account links available.', '4wp-account' ) );
		}

		ob_start();
		?>
		<p><?php esc_html_e( 'Open a store account area:', '4wp-account' ); ?></p>
		<ul class="forwp-account-links">
			<?php foreach ( $items as $endpoint => $label ) : ?>
				<li>
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param string      $message Primary message.
	 * @param string|null $hint    Optional secondary line.
	 */
	private static function render_placeholder( string $message, ?string $hint = null ): string {
		ob_start();
		?>
		<p><?php echo esc_html( $message ); ?></p>
		<?php if ( $hint ) : ?>
			<p class="description"><?php echo esc_html( $hint ); ?></p>
		<?php endif; ?>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * OAuth error from query string.
	 */
	private static function render_auth_error_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['forwp_account_error'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$message = sanitize_text_field( wp_unslash( urldecode( (string) $_GET['forwp_account_error'] ) ) );
		if ( $message === '' ) {
			return;
		}

		printf(
			'<p class="forwp-account-notice forwp-account-notice--error" role="alert">%s</p>',
			esc_html( $message )
		);
	}

	/**
	 * @param array<string, string> $providers Enabled provider buttons.
	 */
	private static function get_guest_lead_text( array $providers ): string {
		$names = array();

		foreach ( array_keys( $providers ) as $provider_id ) {
			$registry = ProviderSettings::get_registry();
			if ( isset( $registry[ $provider_id ]['label'] ) ) {
				$names[] = (string) $registry[ $provider_id ]['label'];
			}
		}

		if ( count( $names ) === 1 ) {
			return sprintf(
				/* translators: %s: provider name, e.g. Google */
				__( 'Use your %s account to access courses, orders, and saved items.', '4wp-account' ),
				$names[0]
			);
		}

		return __( 'Sign in with one of the enabled providers below to access courses, orders, and saved items.', '4wp-account' );
	}

	/**
	 * Header / nav link with icon (guest vs logged-in).
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 */
	public static function render_nav_link( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'label'       => '',
				'label_guest' => __( 'Sign in', '4wp-account' ),
				'label_user'  => __( 'My account', '4wp-account' ),
				'class'       => '',
			),
			$atts,
			'forwp_account_link'
		);

		$url   = AccountMenu::get_account_page_url();
		$label = is_user_logged_in()
			? ( $atts['label'] !== '' ? $atts['label'] : $atts['label_user'] )
			: ( $atts['label'] !== '' ? $atts['label'] : $atts['label_guest'] );

		$classes = trim( 'forwp-account-nav-link ' . $atts['class'] );
		$icon    = self::get_nav_icon_html();

		return sprintf(
			'<a href="%s" class="%s">%s<span class="forwp-account-nav-link__text">%s</span></a>',
			esc_url( $url ),
			esc_attr( $classes ),
			$icon,
			esc_html( $label )
		);
	}

	/**
	 * Inline SVG icons for menu (guest + user states).
	 */
	public static function get_nav_icon_html(): string {
		$guest = '<svg class="forwp-account-nav-icon forwp-account-nav-icon--guest" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" stroke="currentColor" stroke-width="1.8"/><path d="M4 20a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
		$user  = '<svg class="forwp-account-nav-icon forwp-account-nav-icon--user" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3a5 5 0 1 1-5 5 5 5 0 0 1 5-5Z" fill="currentColor" opacity=".25"/><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" stroke="currentColor" stroke-width="1.8"/><path d="M4 20a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="18" cy="6" r="3" fill="currentColor"/></svg>';

		return $guest . $user;
	}

	/**
	 * Remember ?redirect_to= on the account page until OAuth completes.
	 */
	private static function persist_redirect_target_from_request(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['redirect_to'] ) ) {
			return;
		}

		$redirect_to = wp_validate_redirect( wp_unslash( $_GET['redirect_to'] ), '' );
		if ( '' === $redirect_to ) {
			return;
		}

		$path = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		setcookie(
			'forwp_account_redirect_to',
			rawurlencode( $redirect_to ),
			time() + 600,
			$path,
			$domain,
			is_ssl(),
			true
		);
	}
}
