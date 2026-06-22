<?php
/**
 * Admin tab: Auth providers (Google active; others coming soon).
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication providers settings.
 */
class AuthTab {

	/**
	 * Provider registry for admin UI.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_providers(): array {
		return array(
			'google'    => array(
				'label'    => __( 'Google', '4wp-account' ),
				'storage'  => 'gmail',
				'status'   => 'active',
				'doc_url'  => 'https://console.cloud.google.com/apis/credentials',
			),
			'facebook'  => array(
				'label'    => __( 'Facebook', '4wp-account' ),
				'storage'  => 'facebook',
				'status'   => 'soon',
			),
			'github'    => array(
				'label'    => __( 'GitHub', '4wp-account' ),
				'storage'  => 'github',
				'status'   => 'active',
				'doc_url'  => 'https://github.com/settings/developers',
			),
			'tiktok'    => array(
				'label'    => __( 'TikTok', '4wp-account' ),
				'storage'  => 'tiktok',
				'status'   => 'soon',
			),
		);
	}

	/**
	 * Render tab content.
	 */
	public static function render(): void {
		$hide_toolbar     = get_option( 'forwp_account_hide_toolbar_subscribers', '0' );
		$redirect_url     = get_option( 'forwp_account_subscriber_redirect_url', '' );
		$wc_integration   = get_option( 'forwp_account_woocommerce_integration', '0' );
		$wc_active        = class_exists( 'WooCommerce' );
		$providers        = self::get_providers();
		?>
		<div class="forwp-account-panel">
			<h2><?php esc_html_e( 'Auth', '4wp-account' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Social and OAuth sign-in providers. Google and GitHub are available now; additional networks will be enabled here as they ship.', '4wp-account' ); ?>
			</p>

			<?php foreach ( $providers as $provider_key => $provider ) : ?>
				<?php
				$storage_key = (string) $provider['storage'];
				$is_active   = ( 'active' === ( $provider['status'] ?? '' ) );
				$enabled     = get_option( 'forwp_account_provider_enabled_' . $storage_key, '0' );
				?>
				<div class="forwp-account-provider<?php echo $is_active ? '' : ' forwp-account-provider--soon'; ?>">
					<div class="forwp-account-provider__head">
						<h3><?php echo esc_html( (string) $provider['label'] ); ?></h3>
						<?php if ( ! $is_active ) : ?>
							<span class="forwp-account-badge forwp-account-badge--soon"><?php esc_html_e( 'Coming soon', '4wp-account' ); ?></span>
						<?php endif; ?>
					</div>

					<?php if ( $is_active ) : ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable', '4wp-account' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="forwp_account_provider_enabled_<?php echo esc_attr( $storage_key ); ?>" value="1" <?php checked( $enabled, '1' ); ?> />
										<?php printf( esc_html__( 'Allow sign-in with %s', '4wp-account' ), esc_html( (string) $provider['label'] ) ); ?>
									</label>
								</td>
							</tr>
						</table>
						<?php
						if ( 'gmail' === $storage_key ) {
							self::render_google_credentials();
						} elseif ( 'github' === $storage_key ) {
							self::render_github_credentials();
						}
						?>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Provider credentials UI is disabled until this integration is released.', '4wp-account' ); ?></p>
						<input type="hidden" name="forwp_account_provider_enabled_<?php echo esc_attr( $storage_key ); ?>" value="0">
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<h3><?php esc_html_e( 'Behavior', '4wp-account' ); ?></h3>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'WordPress toolbar', '4wp-account' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="forwp_account_hide_toolbar_subscribers" value="1" <?php checked( $hide_toolbar, '1' ); ?> />
							<?php esc_html_e( 'Hide admin bar for subscribers', '4wp-account' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="forwp_account_subscriber_redirect_url"><?php esc_html_e( 'Redirect after login', '4wp-account' ); ?></label>
					</th>
					<td>
						<input type="text" class="regular-text" id="forwp_account_subscriber_redirect_url" name="forwp_account_subscriber_redirect_url" value="<?php echo esc_attr( $redirect_url ); ?>" placeholder="/my-account" />
						<p class="description"><?php esc_html_e( 'Where subscribers land when visiting /wp-admin/. Leave empty to disable.', '4wp-account' ); ?></p>
					</td>
				</tr>
				<?php if ( $wc_active ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'WooCommerce', '4wp-account' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="forwp_account_woocommerce_integration" value="1" <?php checked( $wc_integration, '1' ); ?> />
							<?php esc_html_e( 'Show social buttons on WooCommerce login/register forms', '4wp-account' ); ?>
						</label>
					</td>
				</tr>
				<?php endif; ?>
			</table>

			<h3><?php esc_html_e( 'Usage', '4wp-account' ); ?></h3>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Account page', '4wp-account' ); ?></th>
					<td>
						<code>[forwp_account]</code>
						<p class="description"><?php esc_html_e( 'Sign-in (Google) when logged out; cabinet with left menu when logged in.', '4wp-account' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Blocks', '4wp-account' ); ?></th>
					<td>
						<p><code>forwp/account-link</code> — <?php esc_html_e( 'header/menu link with icon (Site Editor → search “4WP Account Link”)', '4wp-account' ); ?></p>
						<p><code>forwp/account</code> — <?php esc_html_e( 'full account page (sign-in or cabinet)', '4wp-account' ); ?></p>
						<p><code>forwp/auth-buttons</code> — <?php esc_html_e( 'social sign-in buttons', '4wp-account' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Menu link', '4wp-account' ); ?></th>
					<td>
						<code>[forwp_account_link]</code>
						<p class="description"><?php esc_html_e( 'Shortcode equivalent of the account-link block.', '4wp-account' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Shortcode', '4wp-account' ); ?></th>
					<td>
						<code>[forwp_account_login provider="gmail"]</code>
						<code>[forwp_account_login provider="github"]</code>
						<code>[forwp_account_signin_buttons providers="gmail,github"]</code>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'HTML', '4wp-account' ); ?></th>
					<td>
						<code>&lt;button class="forwp-account-signin-btn" data-provider="gmail"&gt;Sign in with Google&lt;/button&gt;</code>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Google OAuth credentials (legacy option keys: forwp_account_gmail_*).
	 */
	private static function render_google_credentials(): void {
		?>
		<h4><?php esc_html_e( 'Google OAuth credentials', '4wp-account' ); ?></h4>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="forwp_account_gmail_client_id"><?php esc_html_e( 'Client ID', '4wp-account' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="forwp_account_gmail_client_id" name="forwp_account_gmail_client_id" value="<?php echo esc_attr( get_option( 'forwp_account_gmail_client_id', '' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="forwp_account_gmail_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label></th>
				<td>
					<input type="password" class="regular-text" id="forwp_account_gmail_client_secret" name="forwp_account_gmail_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_gmail_client_secret', '' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Redirect URI', '4wp-account' ); ?></th>
				<td>
					<code><?php echo esc_html( home_url( '/wp-json/forwp-account/v1/callback/gmail' ) ); ?></code>
					<p class="description"><?php esc_html_e( 'Add this exact URL to Google Cloud Console → Authorized redirect URIs.', '4wp-account' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * GitHub OAuth credentials.
	 */
	private static function render_github_credentials(): void {
		?>
		<h4><?php esc_html_e( 'GitHub OAuth credentials', '4wp-account' ); ?></h4>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="forwp_account_github_client_id"><?php esc_html_e( 'Client ID', '4wp-account' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="forwp_account_github_client_id" name="forwp_account_github_client_id" value="<?php echo esc_attr( get_option( 'forwp_account_github_client_id', '' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="forwp_account_github_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label></th>
				<td>
					<input type="password" class="regular-text" id="forwp_account_github_client_secret" name="forwp_account_github_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_github_client_secret', '' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Authorization callback URL', '4wp-account' ); ?></th>
				<td>
					<code><?php echo esc_html( home_url( '/wp-json/forwp-account/v1/callback/github' ) ); ?></code>
					<p class="description"><?php esc_html_e( 'Add this exact URL to your GitHub OAuth App settings.', '4wp-account' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * @param array<string, mixed> $post Raw POST.
	 */
	public static function save( array $post ): void {
		$providers = self::get_providers();

		foreach ( $providers as $provider ) {
			$storage_key = (string) ( $provider['storage'] ?? '' );
			if ( $storage_key === '' ) {
				continue;
			}

			$option     = 'forwp_account_provider_enabled_' . $storage_key;
			$is_active  = ( 'active' === ( $provider['status'] ?? '' ) );
			$is_enabled = $is_active && ! empty( $post[ $option ] );

			update_option( $option, $is_enabled ? '1' : '0' );
		}

		if ( isset( $post['forwp_account_gmail_client_id'] ) ) {
			update_option( 'forwp_account_gmail_client_id', sanitize_text_field( $post['forwp_account_gmail_client_id'] ) );
		}
		if ( isset( $post['forwp_account_gmail_client_secret'] ) ) {
			update_option( 'forwp_account_gmail_client_secret', sanitize_text_field( $post['forwp_account_gmail_client_secret'] ) );
		}
		if ( isset( $post['forwp_account_github_client_id'] ) ) {
			update_option( 'forwp_account_github_client_id', sanitize_text_field( $post['forwp_account_github_client_id'] ) );
		}
		if ( isset( $post['forwp_account_github_client_secret'] ) ) {
			update_option( 'forwp_account_github_client_secret', sanitize_text_field( $post['forwp_account_github_client_secret'] ) );
		}

		update_option( 'forwp_account_hide_toolbar_subscribers', ! empty( $post['forwp_account_hide_toolbar_subscribers'] ) ? '1' : '0' );

		if ( isset( $post['forwp_account_subscriber_redirect_url'] ) ) {
			update_option( 'forwp_account_subscriber_redirect_url', sanitize_text_field( $post['forwp_account_subscriber_redirect_url'] ) );
		}

		update_option( 'forwp_account_woocommerce_integration', ! empty( $post['forwp_account_woocommerce_integration'] ) ? '1' : '0' );
	}
}
