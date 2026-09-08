<?php
/**
 * Admin tab: Auth / Register methods (one card per provider).
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

use ForWP\Account\Auth\OAuthUrls;
use ForWP\Account\Auth\ProviderSettings;

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
			'email'    => array(
				'label'   => __( 'Login / Register form', '4wp-account' ),
				'storage' => 'email',
				'status'  => 'active',
				'type'    => ProviderSettings::TYPE_FORM,
				'blurb'   => __( 'Email and password on the account page. No Client ID.', '4wp-account' ),
			),
			'gmail'    => array(
				'label'   => __( 'Google', '4wp-account' ),
				'storage' => 'gmail',
				'status'  => 'active',
				'type'    => ProviderSettings::TYPE_OAUTH,
				'blurb'   => __( 'Google Cloud OAuth. Paste Client ID and Secret, then copy the Redirect URI.', '4wp-account' ),
			),
			'facebook' => array(
				'label'   => __( 'Facebook', '4wp-account' ),
				'storage' => 'facebook',
				'status'  => 'soon',
				'type'    => ProviderSettings::TYPE_OAUTH,
				'blurb'   => __( 'Meta app review blocked — later (YaKulinar).', '4wp-account' ),
			),
			'github'   => array(
				'label'   => __( 'GitHub', '4wp-account' ),
				'storage' => 'github',
				'status'  => 'active',
				'type'    => ProviderSettings::TYPE_OAUTH,
				'blurb'   => __( 'GitHub OAuth App. Paste Client ID and Secret, then copy the callback URL.', '4wp-account' ),
			),
			'tiktok'   => array(
				'label'   => __( 'TikTok', '4wp-account' ),
				'storage' => 'tiktok',
				'status'  => 'active',
				'type'    => ProviderSettings::TYPE_OAUTH,
				'blurb'   => __( 'TikTok Login Kit. Paste Client Key and Secret, then copy the Redirect URI.', '4wp-account' ),
			),
			'x'        => array(
				'label'   => __( 'X', '4wp-account' ),
				'storage' => 'x',
				'status'  => 'soon',
				'type'    => ProviderSettings::TYPE_OAUTH,
				'blurb'   => __( 'Feature — not available yet.', '4wp-account' ),
			),
		);
	}

	/**
	 * Render tab content.
	 */
	public static function render(): void {
		$wc_integration = get_option( 'forwp_account_woocommerce_integration', '0' );
		$wc_active      = class_exists( 'WooCommerce' );
		$providers      = self::get_providers();
		$open_provider  = self::get_request_provider();
		?>
		<div class="forwp-account-intro-card" id="forwp-account-auth-intro">
			<h2 class="forwp-account-intro-card__title"><?php esc_html_e( 'Auth / Register methods', '4wp-account' ); ?></h2>
			<p class="forwp-account-intro-card__text"><?php esc_html_e( 'Pick a method below. Live methods open their settings here — enable, then fill only what that method needs. The front end follows the active theme. WooCommerce is optional.', '4wp-account' ); ?></p>
		</div>

		<input type="hidden" name="forwp_account_open_provider" id="forwp_account_open_provider" value="<?php echo esc_attr( $open_provider ); ?>">

		<div id="forwp-account-provider-detail-wrap" class="forwp-account-provider-detail-wrap" data-open-provider="<?php echo esc_attr( $open_provider ); ?>" hidden>
			<div class="forwp-account-provider-detail-head">
				<h3 class="forwp-account-section-title forwp-account-provider-detail-head__title" id="forwp-account-provider-detail-title"><?php esc_html_e( 'Method', '4wp-account' ); ?></h3>
				<button type="button" class="button" id="forwp-account-provider-back"><?php esc_html_e( '← Back to overview', '4wp-account' ); ?></button>
			</div>
			<?php foreach ( $providers as $provider ) : ?>
				<?php self::render_provider_detail( $provider ); ?>
			<?php endforeach; ?>
		</div>

		<h3 class="forwp-account-section-title forwp-account-registry-title"><?php esc_html_e( 'Methods', '4wp-account' ); ?></h3>
		<p class="forwp-account-muted forwp-account-registry-hint"><?php esc_html_e( 'Click a card to open settings or roadmap details.', '4wp-account' ); ?></p>
		<div class="forwp-account-provider-grid" id="forwp-account-provider-grid">
			<?php foreach ( $providers as $provider ) : ?>
				<?php self::render_provider_card( $provider ); ?>
			<?php endforeach; ?>
		</div>

		<?php if ( $wc_active ) : ?>
		<div class="forwp-account-card">
			<h3 class="forwp-account-section-title"><?php esc_html_e( 'WooCommerce', '4wp-account' ); ?></h3>
			<div class="forwp-account-field">
				<label class="forwp-account-toggle-row">
					<input type="checkbox" name="forwp_account_woocommerce_integration" value="1" <?php checked( $wc_integration, '1' ); ?> />
					<span><?php esc_html_e( 'Show social buttons on WooCommerce login/register forms', '4wp-account' ); ?></span>
				</label>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Compact registry card (Drive-style). Fields live in the detail panel.
	 *
	 * @param array<string, mixed> $provider Provider row from get_providers().
	 */
	private static function render_provider_card( array $provider ): void {
		$storage_key = (string) ( $provider['storage'] ?? '' );
		$is_active   = ( 'active' === ( $provider['status'] ?? '' ) );
		$label       = (string) ( $provider['label'] ?? '' );
		$blurb       = (string) ( $provider['blurb'] ?? '' );
		?>
		<div
			class="forwp-account-provider-card"
			role="button"
			tabindex="0"
			data-provider="<?php echo esc_attr( $storage_key ); ?>"
			data-label="<?php echo esc_attr( $label ); ?>"
			aria-pressed="false"
		>
			<div class="forwp-account-provider-card__head">
				<div>
					<div class="forwp-account-provider-card__label"><?php echo esc_html( $label ); ?></div>
					<div class="forwp-account-provider-card__slug"><code><?php echo esc_html( $storage_key ); ?></code></div>
				</div>
				<?php if ( $is_active ) : ?>
					<span class="forwp-account-badge forwp-account-badge--live"><?php esc_html_e( 'Live', '4wp-account' ); ?></span>
				<?php else : ?>
					<span class="forwp-account-badge forwp-account-badge--soon"><?php esc_html_e( 'Coming soon', '4wp-account' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="forwp-account-provider-card__status"><?php echo esc_html( $blurb ); ?></p>
		</div>
		<?php
	}

	/**
	 * Expanded settings for one method.
	 *
	 * @param array<string, mixed> $provider Provider row from get_providers().
	 */
	private static function render_provider_detail( array $provider ): void {
		$storage_key = (string) ( $provider['storage'] ?? '' );
		$is_active   = ( 'active' === ( $provider['status'] ?? '' ) );
		$type        = (string) ( $provider['type'] ?? ProviderSettings::TYPE_OAUTH );
		$enabled     = ProviderSettings::is_enabled( $storage_key );
		$blurb       = (string) ( $provider['blurb'] ?? '' );
		?>
		<div class="forwp-account-provider-detail" data-provider="<?php echo esc_attr( $storage_key ); ?>" hidden>
			<?php if ( $is_active ) : ?>
				<div class="forwp-account-provider-detail__panel">
					<label class="forwp-account-toggle-row">
						<input type="checkbox" name="forwp_account_provider_enabled_<?php echo esc_attr( $storage_key ); ?>" value="1" <?php checked( $enabled ); ?> />
						<span><?php esc_html_e( 'Enabled', '4wp-account' ); ?></span>
					</label>

					<?php
					if ( ProviderSettings::TYPE_FORM === $type ) {
						self::render_email_fields();
					} elseif ( 'gmail' === $storage_key ) {
						self::render_google_fields();
					} elseif ( 'github' === $storage_key ) {
						self::render_github_fields();
					} elseif ( 'tiktok' === $storage_key ) {
						self::render_tiktok_fields();
					}
					?>
				</div>
			<?php else : ?>
				<div class="notice notice-info inline">
					<p><?php echo esc_html( $blurb !== '' ? $blurb : __( 'Not available yet.', '4wp-account' ) ); ?></p>
				</div>
				<input type="hidden" name="forwp_account_provider_enabled_<?php echo esc_attr( $storage_key ); ?>" value="0">
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Opened provider after save / deep link.
	 */
	public static function get_request_provider(): string {
		$providers = self::get_providers();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only provider panel.
		$raw = isset( $_GET['provider'] ) ? sanitize_key( wp_unslash( (string) $_GET['provider'] ) ) : '';

		foreach ( $providers as $provider ) {
			if ( $raw === (string) ( $provider['storage'] ?? '' ) ) {
				return $raw;
			}
		}

		return '';
	}

	/**
	 * Core email / password — no OAuth keys.
	 */
	private static function render_email_fields(): void {
		$wp_allows = (bool) get_option( 'users_can_register' );
		?>
		<p class="forwp-account-source-card__desc"><?php esc_html_e( 'Email and password on the account page. No Client ID. Works without WooCommerce. Styling comes from the theme.', '4wp-account' ); ?></p>
		<?php if ( $wp_allows ) : ?>
			<p class="forwp-account-field__help"><?php esc_html_e( 'Registration is on: Settings → General → Anyone can register.', '4wp-account' ); ?></p>
		<?php else : ?>
			<p class="forwp-account-field__help">
				<?php
				printf(
					/* translators: %s: Settings → General URL */
					esc_html__( 'The “Create an account” link stays hidden until membership is enabled in %s.', '4wp-account' ),
					'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Settings → General', '4wp-account' ) . '</a>'
				);
				?>
			</p>
		<?php endif; ?>
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
	 * @param string $provider Provider slug.
	 * @param string $hint     Setup instructions for the provider console.
	 */
	private static function render_redirect_uri_field( string $provider, string $hint ): void {
		$url      = self::get_callback_url( $provider );
		$input_id = 'forwp_account_redirect_uri_' . sanitize_key( $provider );
		?>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Redirect URI', '4wp-account' ); ?></label>
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
			<p class="forwp-account-field__help"><?php echo esc_html( $hint ); ?></p>
		</div>
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
			'<p class="forwp-account-field__help">%s</p>',
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
	 * Google OAuth fields (legacy option keys: forwp_account_gmail_*).
	 */
	private static function render_google_fields(): void {
		?>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="forwp_account_gmail_client_id"><?php esc_html_e( 'Client ID', '4wp-account' ); ?></label>
			<input type="text" class="regular-text" id="forwp_account_gmail_client_id" name="forwp_account_gmail_client_id" value="<?php echo esc_attr( get_option( 'forwp_account_gmail_client_id', '' ) ); ?>" autocomplete="off" />
			<?php self::render_external_link_hint( __( 'Client ID', '4wp-account' ), __( 'Google Cloud Console', '4wp-account' ), 'https://console.cloud.google.com/apis/credentials' ); ?>
		</div>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="forwp_account_gmail_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label>
			<input type="password" class="regular-text" id="forwp_account_gmail_client_secret" name="forwp_account_gmail_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_gmail_client_secret', '' ) ); ?>" autocomplete="new-password" />
		</div>
		<?php
		self::render_redirect_uri_field(
			'gmail',
			__( 'Add this exact URL to Google Cloud Console → Credentials → Authorized redirect URIs (including the port on local sites).', '4wp-account' )
		);
	}

	/**
	 * GitHub OAuth fields.
	 */
	private static function render_github_fields(): void {
		?>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="forwp_account_github_client_id"><?php esc_html_e( 'Client ID', '4wp-account' ); ?></label>
			<input type="text" class="regular-text" id="forwp_account_github_client_id" name="forwp_account_github_client_id" value="<?php echo esc_attr( get_option( 'forwp_account_github_client_id', '' ) ); ?>" autocomplete="off" />
			<?php self::render_external_link_hint( __( 'Client ID', '4wp-account' ), __( 'GitHub Developer Settings', '4wp-account' ), 'https://github.com/settings/developers' ); ?>
		</div>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="forwp_account_github_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label>
			<input type="password" class="regular-text" id="forwp_account_github_client_secret" name="forwp_account_github_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_github_client_secret', '' ) ); ?>" autocomplete="new-password" />
		</div>
		<?php
		self::render_redirect_uri_field(
			'github',
			__( 'Add this exact URL to GitHub OAuth App → Authorization callback URL (including the port on local sites).', '4wp-account' )
		);
	}

	/**
	 * TikTok OAuth fields (Client Key / Secret).
	 */
	private static function render_tiktok_fields(): void {
		?>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="forwp_account_tiktok_client_key"><?php esc_html_e( 'Client Key', '4wp-account' ); ?></label>
			<input type="text" class="regular-text" id="forwp_account_tiktok_client_key" name="forwp_account_tiktok_client_key" value="<?php echo esc_attr( get_option( 'forwp_account_tiktok_client_key', '' ) ); ?>" autocomplete="off" />
			<?php self::render_external_link_hint( __( 'Client Key', '4wp-account' ), __( 'TikTok Developers', '4wp-account' ), 'https://developers.tiktok.com/apps/' ); ?>
		</div>
		<div class="forwp-account-field">
			<label class="forwp-account-field__label" for="forwp_account_tiktok_client_secret"><?php esc_html_e( 'Client Secret', '4wp-account' ); ?></label>
			<input type="password" class="regular-text" id="forwp_account_tiktok_client_secret" name="forwp_account_tiktok_client_secret" value="<?php echo esc_attr( get_option( 'forwp_account_tiktok_client_secret', '' ) ); ?>" autocomplete="new-password" />
		</div>
		<?php
		self::render_redirect_uri_field(
			'tiktok',
			__( 'Add this exact URL to TikTok Login Kit → Redirect URI (including the port on local sites).', '4wp-account' )
		);
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
		if ( isset( $post['forwp_account_tiktok_client_key'] ) ) {
			update_option( 'forwp_account_tiktok_client_key', sanitize_text_field( $post['forwp_account_tiktok_client_key'] ) );
		}
		if ( isset( $post['forwp_account_tiktok_client_secret'] ) ) {
			update_option( 'forwp_account_tiktok_client_secret', sanitize_text_field( $post['forwp_account_tiktok_client_secret'] ) );
		}

		update_option( 'forwp_account_woocommerce_integration', ! empty( $post['forwp_account_woocommerce_integration'] ) ? '1' : '0' );
	}
}
