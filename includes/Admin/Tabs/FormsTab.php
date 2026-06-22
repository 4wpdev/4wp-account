<?php
/**
 * Admin tab: Forms (coming soon — WooCommerce forms for now).
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forms settings tab.
 */
class FormsTab {

	/**
	 * Render tab content.
	 */
	public static function render(): void {
		$wc_active = class_exists( 'WooCommerce' );
		?>
		<div class="forwp-account-panel">
			<h2><?php esc_html_e( 'Forms', '4wp-account' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Unified login, registration, and profile forms will live here. Until then, use WooCommerce or WordPress core forms.', '4wp-account' ); ?>
			</p>

			<div class="forwp-account-notice forwp-account-notice--soon">
				<strong><?php esc_html_e( 'Coming soon', '4wp-account' ); ?></strong>
				<p><?php esc_html_e( 'Custom form builder and block-based auth forms are planned for a future release.', '4wp-account' ); ?></p>
			</div>

			<h3><?php esc_html_e( 'Current recommendation', '4wp-account' ); ?></h3>
			<ul class="forwp-account-list">
				<?php if ( $wc_active ) : ?>
					<li>
						<?php
						printf(
							/* translators: %s: WooCommerce My Account settings URL */
							esc_html__( 'Use WooCommerce login & registration on the %s page.', '4wp-account' ),
							'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=account' ) ) . '">' . esc_html__( 'My Account', '4wp-account' ) . '</a>'
						);
						?>
					</li>
					<li><?php esc_html_e( 'Enable social login on WC forms under 4WP Account → Auth.', '4wp-account' ); ?></li>
				<?php else : ?>
					<li><?php esc_html_e( 'Install WooCommerce for store-ready login and registration forms.', '4wp-account' ); ?></li>
					<li><?php esc_html_e( 'Or use the Auth block / shortcode on any page (Settings → Blocks).', '4wp-account' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $post Raw POST.
	 */
	public static function save( array $post ): void {
		unset( $post );
		// No persisted settings yet.
	}
}
