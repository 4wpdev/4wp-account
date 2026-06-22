<?php
/**
 * Admin tab: Auth providers (Google active; others coming soon).
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

use ForWP\Account\Auth\OAuthUrls;

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
		$hide_toolbar   = get_option( 'forwp_account_hide_toolbar_subscribers', '0' );
		$redirect_url   = get_option( 'forwp_account_subscriber_redirect_url', '' );
		$wc_integration = get_option( 'forwp_account_woocommerce_integration', '0' );
		$wc_active      = class_exists( 'WooCommerce' );
		$providers      = self::get_providers();
		?>
		<div class="forwp-account-panel">
			<h2><?php esc_html_e( 'Auth', '4wp-account' ); ?></h2>

			<h3><?php esc_html_e( 'Social networks', '4wp-account' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Select the social networks you want to enable for user authentication.', '4wp-account' ); ?>
			</p>
			<div class="forwp-account-social-grid">
				<?php foreach ( $providers as $provider ) : ?>
					<?php
					$storage_key = (string) $provider['storage'];
					$is_active   = ( 'active' === ( $provider['status'] ?? '' ) );
					$enabled     = get_option( 'forwp_account_provider_enabled_' . $storage_key, '0' );
					?>
					<label>
						<?php if ( $is_active ) : ?>
							<input type="checkbox" name="forwp_account_provider_enabled_<?php echo esc_attr( $storage_key ); ?>" value="1" <?php checked( $enabled, '1' ); ?> />
							<?php
							printf(
								/* translators: %s: provider label */
								esc_html__( 'Enable %s', '4wp-account' ),
								esc_html( (string) $provider['label'] )
							);
							?>
						<?php else : ?>
							<input type="checkbox" disabled />
							<?php
							printf(
								/* translators: %s: provider label */
								esc_html__( 'Enable %s (coming soon)', '4wp-account' ),
								esc_html( (string) $provider['label'] )
							);
							?>
							<input type="hidden" name="forwp_account_provider_enabled_<?php echo esc_attr( $storage_key ); ?>" value="0">
						<?php endif; ?>
					</label>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $providers as $provider_key => $provider ) : ?>
				<?php
				$storage_key = (string) $provider['storage'];
				$is_active   = ( 'active' === ( $provider['status'] ?? '' ) );
				?>
				<div class="forwp-account-provider<?php echo $is_active ? '' : ' forwp-account-provider--soon'; ?>">
					<div class="forwp-account-provider__head">
						<h3><?php echo esc_html( (string) $provider['label'] ); ?></h3>
						<?php if ( ! $is_active ) : ?>
							<span class="forwp-account-badge forwp-account-badge--soon"><?php esc_html_e( 'Coming soon', '4wp-account' ); ?></span>
						<?php endif; ?>
					</div>

					<?php if ( $is_active ) : ?>
						<?php
						if ( 'gmail' === $storage_key ) {
							self::render_google_credentials();
						} elseif ( 'github' === $storage_key ) {
							self::render_github_credentials();
						}
						?>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Provider credentials UI is disabled until this integration is released.', '4wp-account' ); ?></p>
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
						<p><code>forwp/account-menu</code> — <?php esc_html_e( 'header icon with account dropdown', '4wp-account' ); ?></p>
						<p><code>forwp/account</code> — <?php esc_html_e( 'full account page (sign-in or cabinet)', '4wp-account' ); ?></p>
						<p><code>forwp/auth-buttons</code> — <?php esc_html_e( 'social sign-in buttons', '4wp-account' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Menu link', '4wp-account' ); ?></th>
					<td>
						<code>[forwp_account_menu]</code>
						<p class="description"><?php esc_html_e( 'Shortcode equivalent of the account-menu block.', '4wp-account' ); ?></p>
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
	 * OAuth callback URL for a provider slug.
	 *
	 * @param string $provider Provider slug (gmail, github, …).
	 */
	public static function get_callback_url( string $provider ): string {
		return OAuthUrls::callback_url( $provider );
	}

	/**
	 * Read-only redirect URI field with copy button and setup hint.
	 *
	 * @param string $provider     Provider slug.
	 * @param string $important_hint Setup instructions for the provider console.
	 */
	private static function render_redirect_uri_row( string $provider, string $important_hint ): void {
		$url      = self::get_callback_url( $provider );
		$input_id = 'forwp_account_redirect_uri_' . sanitize_key( $provider );
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Redirect URI', '4wp-account' ); ?></th>
			<td>
				<div class="forwp-account-uri-field">
					<input
						type="text"
						class="large-text code forwp-account-uri-field__input"
						id="<?php echo esc_attr( $input_id ); ?>"
						value="<?php echo esc_attr( $url ); ?>"
						readonly
						onclick="this.select();"
					>
					<button
						type="button"
						class="button forwp-account-uri-field__copy"
						data-copy-target="<?php echo esc_attr( $input_id ); ?>"
						data-copied-label="<?php esc_attr_e( 'Copied!', '4wp-account' ); ?>"
					><?php esc_html_e( 'Copy', '4wp-account' ); ?></button>
				</div>
				<p class="description forwp-account-notice forwp-account-notice--info forwp-account-oauth-hint">
					<strong><?php esc_html_e( 'IMPORTANT:', '4wp-account' ); ?></strong>
					<?php echo esc_html( $important_hint ); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * External link helper for credential field descriptions.
	 *
	 * @param string $credential_label Field label (Client ID, App ID, …).
	 * @param string $console_label    Console name shown in the link.
	 * @param string $url              Destination URL.
	 */
	private static function render_external_link_hint( string $credential_label, string $console_label, string $url ): void {
		printf(
			'<p class="description">%s</p>',
			wp_kses(
				sprintf(
					/* translators: 1: credential label, 2: link URL, 3: console name */
					__( 'Get your %1$s from <a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a>.', '4wp-account' ),
					esc_html( $credential_label ),
					esc_url( $url ),
					esc_html( $console_label )
				),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
				)
			)
		);
	}

	/**
	 * Google OAuth credentials (legacy option keys: forwp_account_gmail_*).
	 */
	private static function render_google_credentials(): void {
		?>
		<h4><?php esc_html_e( 'Gmail', '4wp-account' ); ?></h4>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="forwp_account_gmail_client_id"><?php esc_html_e( 'Client ID', '4wp-account' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="forwp_account_gmail_client_id" name="forwp_account_gmail_client_id" value="<?php echo esc_attr( get_option( 'forwp_account_gmail_client_id', '' ) ); ?>" autocomplete="off" />
					<?php self::render_external_link_hint( __( 'Client ID', '4wp-account' ), __( 'Google Cloud Console', '4wp-account' ), 'https://console.cloud.google.com/apis/credentials' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="forwp_account_gmail_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label></th>
				<td>
					<input type="password" class="regular-text" id="forwp_account_gmail_client_secret" name="forwp_account_gmail_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_gmail_client_secret', '' ) ); ?>" autocomplete="new-password" />
				</td>
			</tr>
			<?php
			self::render_redirect_uri_row(
				'gmail',
				__( 'Copy the URL above and add it to Google Cloud Console → Credentials → Authorized redirect URIs. The URL must be EXACTLY the same (including the port on local sites).', '4wp-account' )
			);
			?>
		</table>
		<?php
	}

	/**
	 * GitHub OAuth credentials.
	 */
	private static function render_github_credentials(): void {
		?>
		<h4><?php esc_html_e( 'GitHub', '4wp-account' ); ?></h4>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="forwp_account_github_client_id"><?php esc_html_e( 'Client ID', '4wp-account' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="forwp_account_github_client_id" name="forwp_account_github_client_id" value="<?php echo esc_attr( get_option( 'forwp_account_github_client_id', '' ) ); ?>" autocomplete="off" />
					<?php self::render_external_link_hint( __( 'Client ID', '4wp-account' ), __( 'GitHub Developer Settings', '4wp-account' ), 'https://github.com/settings/developers' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="forwp_account_github_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label></th>
				<td>
					<input type="password" class="regular-text" id="forwp_account_github_client_secret" name="forwp_account_github_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_github_client_secret', '' ) ); ?>" autocomplete="new-password" />
				</td>
			</tr>
			<?php
			self::render_redirect_uri_row(
				'github',
				__( 'Add this URL to your GitHub OAuth App → Authorization callback URL. The URL must be EXACTLY the same (including the port on local sites).', '4wp-account' )
			);
			?>
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
