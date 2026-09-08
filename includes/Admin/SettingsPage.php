<?php
/**
 * Admin settings router (Auth / Settings tabs).
 *
 * @package ForWP\Account\Admin
 */

namespace ForWP\Account\Admin;

use ForWP\Account\Admin\Tabs\AppearanceTab;
use ForWP\Account\Admin\Tabs\AuthTab;
use ForWP\Account\Admin\Tabs\SettingsTab;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main 4WP Account admin page.
 */
class SettingsPage {

	public const PAGE_SLUG      = 'forwp-account';
	public const SETTINGS_GROUP = 'forwp_account_settings';

	public const TAB_AUTH       = 'auth';
	public const TAB_APPEARANCE = 'appearance';
	public const TAB_SETTINGS   = 'settings';

	/**
	 * Bootstrap admin hooks.
	 */
	public static function init(): void {
		add_action( 'admin_init', array( self::class, 'handle_post_save' ) );
	}

	/**
	 * Save settings before admin output (PRG redirect must run pre-headers).
	 */
	public static function handle_post_save(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['forwp_account_settings'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'forwp_account_settings', 'forwp_account_nonce' );

		$current_tab = self::get_save_tab();

		self::save_settings( $current_tab, wp_unslash( $_POST ) );

		$redirect_args = array(
			'page'             => self::PAGE_SLUG,
			'tab'              => $current_tab,
			'settings-updated' => 'true',
		);

		if ( self::TAB_SETTINGS === $current_tab && ! empty( $_POST['forwp_account_settings_sub'] ) ) {
			$redirect_args['section'] = sanitize_key( wp_unslash( $_POST['forwp_account_settings_sub'] ) );
		}

		if ( self::TAB_AUTH === $current_tab && ! empty( $_POST['forwp_account_open_provider'] ) ) {
			$redirect_args['provider'] = sanitize_key( wp_unslash( (string) $_POST['forwp_account_open_provider'] ) );
		}

		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_tabs(): array {
		return array(
			self::TAB_AUTH       => __( 'Auth / Register', '4wp-account' ),
			self::TAB_APPEARANCE => __( 'Appearance', '4wp-account' ),
			self::TAB_SETTINGS   => __( 'Settings', '4wp-account' ),
		);
	}

	/**
	 * @param string $tab Raw tab slug.
	 */
	public static function sanitize_tab( string $tab ): string {
		$tabs = self::get_tabs();

		return array_key_exists( $tab, $tabs ) ? $tab : self::TAB_AUTH;
	}

	/**
	 * Active admin tab from the query string (read-only navigation).
	 */
	private static function get_request_tab(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab switch; no state change.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : self::TAB_AUTH;

		return self::sanitize_tab( $tab );
	}

	/**
	 * Tab to save: posted hidden field first, then the query string.
	 */
	private static function get_save_tab(): string {
		if ( isset( $_POST['forwp_account_current_tab'] ) ) {
			return self::sanitize_tab( sanitize_key( wp_unslash( (string) $_POST['forwp_account_current_tab'] ) ) );
		}

		return self::get_request_tab();
	}

	/**
	 * Active Settings sub-section from the query string.
	 */
	private static function get_request_section( string $default ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only section switch.
		return isset( $_GET['section'] ) ? sanitize_key( wp_unslash( (string) $_GET['section'] ) ) : $default;
	}

	/**
	 * Whether the PRG flash flag is present after a successful save.
	 */
	private static function is_settings_updated_flash(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Redirect flash after PRG save.
		return isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( (string) $_GET['settings-updated'] ) );
	}

	/**
	 * Render admin page.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_tab = self::get_request_tab();

		if ( self::is_settings_updated_flash() ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', '4wp-account' ) . '</p></div>';
		}

		$tabs       = self::get_tabs();
		$heading_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" focusable="false" aria-hidden="true"><path fill="currentColor" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>';
		$svg_allowed = array(
			'svg'  => array(
				'xmlns'       => true,
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'fill'        => true,
				'focusable'   => true,
				'aria-hidden' => true,
			),
			'path' => array(
				'd'    => true,
				'fill' => true,
			),
		);
		?>
		<div class="wrap forwp-account-admin-shell">
			<h1 class="forwp-account-admin-heading">
				<span class="forwp-account-admin-heading__icon" aria-hidden="true"><?php echo wp_kses( $heading_svg, $svg_allowed ); ?></span>
				<span class="forwp-account-admin-heading__text"><?php echo esc_html__( '4WP Account', '4wp-account' ); ?></span>
			</h1>
			<p class="forwp-account-admin-lead"><?php esc_html_e( 'User account hub: sign-in methods, pages, and account blocks.', '4wp-account' ); ?></p>

			<div class="forwp-account-admin-app">
				<div class="forwp-account-tab-panel">
					<nav class="forwp-account-tab-panel__tabs" aria-label="<?php esc_attr_e( '4WP Account sections', '4wp-account' ); ?>">
						<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG, 'tab' => $tab_key ), admin_url( 'admin.php' ) ) ); ?>" class="forwp-account-tab<?php echo $current_tab === $tab_key ? ' is-active' : ''; ?>">
								<?php echo esc_html( $tab_label ); ?>
							</a>
						<?php endforeach; ?>
					</nav>

					<div class="forwp-account-tab-panel__content">
						<form method="post" action="">
							<?php wp_nonce_field( 'forwp_account_settings', 'forwp_account_nonce' ); ?>
							<input type="hidden" name="forwp_account_settings" value="1">
							<input type="hidden" name="forwp_account_current_tab" value="<?php echo esc_attr( $current_tab ); ?>">

							<?php
							$section = '';
							if ( self::TAB_SETTINGS === $current_tab ) {
								$section = self::get_request_section( SettingsTab::SUB_PAGES );
								SettingsTab::render( $section );
							} elseif ( self::TAB_APPEARANCE === $current_tab ) {
								AppearanceTab::render();
							} else {
								AuthTab::render();
							}

							if ( SettingsTab::SUB_BLOCKS !== $section ) {
								echo '<p class="forwp-account-form-actions">';
								submit_button( __( 'Save settings', '4wp-account' ), 'primary', 'submit', false );
								echo '</p>';
							}
							?>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param string               $tab  Active tab.
	 * @param array<string, mixed> $post POST data.
	 */
	private static function save_settings( string $tab, array $post ): void {
		if ( self::TAB_SETTINGS === $tab ) {
			SettingsTab::save( $post );
			return;
		}

		if ( self::TAB_APPEARANCE === $tab ) {
			AppearanceTab::save( $post );
			return;
		}

		AuthTab::save( $post );
	}
}
