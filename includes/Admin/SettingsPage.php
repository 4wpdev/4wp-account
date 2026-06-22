<?php
/**
 * Admin settings router (Forms / Auth / Settings tabs).
 *
 * @package ForWP\Account\Admin
 */

namespace ForWP\Account\Admin;

use ForWP\Account\Admin\Tabs\AuthTab;
use ForWP\Account\Admin\Tabs\FormsTab;
use ForWP\Account\Admin\Tabs\SettingsTab;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main 4WP Account admin page.
 */
class SettingsPage {

	public const PAGE_SLUG       = 'forwp-account';
	public const SETTINGS_GROUP  = 'forwp_account_settings';

	public const TAB_FORMS    = 'forms';
	public const TAB_AUTH     = 'auth';
	public const TAB_SETTINGS = 'settings';

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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab = self::sanitize_tab( (string) ( $_GET['tab'] ?? self::TAB_AUTH ) );

		self::save_settings( $current_tab, wp_unslash( $_POST ) );

		$redirect_args = array(
			'page'             => self::PAGE_SLUG,
			'tab'              => $current_tab,
			'settings-updated' => 'true',
		);

		if ( self::TAB_SETTINGS === $current_tab && ! empty( $_POST['forwp_account_settings_sub'] ) ) {
			$redirect_args['section'] = sanitize_key( wp_unslash( $_POST['forwp_account_settings_sub'] ) );
		}

		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_tabs(): array {
		return array(
			self::TAB_FORMS    => __( 'Forms', '4wp-account' ),
			self::TAB_AUTH     => __( 'Auth', '4wp-account' ),
			self::TAB_SETTINGS => __( 'Settings', '4wp-account' ),
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
	 * Render admin page.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab = self::sanitize_tab( (string) ( $_GET['tab'] ?? self::TAB_AUTH ) );

		if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', '4wp-account' ) . '</p></div>';
		}

		$tabs = self::get_tabs();
		?>
		<div class="wrap forwp-account-admin">
			<h1><?php echo esc_html__( '4WP Account', '4wp-account' ); ?></h1>
			<p class="description"><?php esc_html_e( 'User account hub: forms, authentication, pages, and account blocks.', '4wp-account' ); ?></p>

			<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( '4WP Account sections', '4wp-account' ); ?>">
				<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG, 'tab' => $tab_key ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab<?php echo $current_tab === $tab_key ? ' nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="">
				<?php wp_nonce_field( 'forwp_account_settings', 'forwp_account_nonce' ); ?>
				<input type="hidden" name="forwp_account_settings" value="1">

				<?php
				if ( self::TAB_FORMS === $current_tab ) {
					FormsTab::render();
				} elseif ( self::TAB_SETTINGS === $current_tab ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$section = sanitize_key( (string) ( $_GET['section'] ?? SettingsTab::SUB_PAGES ) );
					SettingsTab::render( $section );
				} else {
					AuthTab::render();
				}
				?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param string               $tab  Active tab.
	 * @param array<string, mixed> $post POST data.
	 */
	private static function save_settings( string $tab, array $post ): void {
		if ( self::TAB_FORMS === $tab ) {
			FormsTab::save( $post );
			return;
		}

		if ( self::TAB_SETTINGS === $tab ) {
			SettingsTab::save( $post );
			return;
		}

		AuthTab::save( $post );
	}
}
