<?php
/**
 * Admin tab: guest sign-in appearance (preview + tokens).
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

use ForWP\Account\Auth\Appearance;
use ForWP\Account\Auth\SocialButtons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appearance settings for the guest account card.
 */
class AppearanceTab {

	/**
	 * Render tab content.
	 */
	public static function render(): void {
		$overrides     = Appearance::get_overrides();
		$defaults      = Appearance::site_defaults();
		$resolved      = Appearance::resolve();
		$preview_width = Appearance::preview_width();
		$social_style  = Appearance::social_style();
		$is_narrow     = ( 'narrow' === $preview_width );
		?>
		<div class="forwp-account-intro-card">
			<h2 class="forwp-account-intro-card__title"><?php esc_html_e( 'Appearance', '4wp-account' ); ?></h2>
			<p class="forwp-account-intro-card__text"><?php esc_html_e( 'Layout is fixed: email and password side by side, Sign in below, social buttons at the bottom. On a narrow screen the fields stack. Empty colors keep theme.json / plugin defaults.', '4wp-account' ); ?></p>
		</div>

		<div class="forwp-account-appearance">
			<div class="forwp-account-appearance__preview-col">
				<div class="forwp-account-preview">
					<p class="forwp-account-preview__caption"><?php esc_html_e( 'Sign-in card', '4wp-account' ); ?></p>
					<div class="forwp-account-preview__toolbar">
						<button type="button" class="button button-small<?php echo $is_narrow ? '' : ' is-active'; ?>" id="forwp-account-preview-wide" data-preview-width="wide"><?php esc_html_e( 'Default', '4wp-account' ); ?></button>
						<button type="button" class="button button-small<?php echo $is_narrow ? ' is-active' : ''; ?>" id="forwp-account-preview-narrow" data-preview-width="narrow"><?php esc_html_e( 'Narrow', '4wp-account' ); ?></button>
					</div>
					<input type="hidden" name="forwp_account_appearance[preview_width]" id="forwp-account-preview-width" value="<?php echo esc_attr( $preview_width ); ?>">
					<div class="forwp-account-preview__frame">
						<div class="forwp-account-preview__chrome">
							<span class="forwp-account-preview__dot"></span>
							<span class="forwp-account-preview__dot"></span>
							<span class="forwp-account-preview__dot"></span>
							<span class="forwp-account-preview__chrome-label"><?php esc_html_e( 'Account', '4wp-account' ); ?></span>
						</div>
						<div class="forwp-account-preview-stage<?php echo $is_narrow ? ' is-narrow' : ''; ?>" id="forwp-account-preview-stage">
							<div class="forwp-account forwp-account--guest" id="forwp-account-preview-card" style="<?php echo esc_attr( Appearance::preview_style_attr() ); ?>">
								<div class="forwp-account-guest">
									<h2 class="forwp-account-guest__title"><?php esc_html_e( 'Sign in to your account', '4wp-account' ); ?></h2>
									<p class="forwp-account-guest__lead"><?php esc_html_e( 'Sign in with email or a social account to access courses, orders, and saved items.', '4wp-account' ); ?></p>
									<div class="forwp-account-password">
										<div class="forwp-account-password-form">
											<div class="forwp-account-password-fields">
												<p class="forwp-account-password-field">
													<label><?php esc_html_e( 'Email or username', '4wp-account' ); ?></label>
													<span class="forwp-account-preview-input"></span>
												</p>
												<p class="forwp-account-password-field">
													<label><?php esc_html_e( 'Password', '4wp-account' ); ?></label>
													<span class="forwp-account-preview-input"></span>
												</p>
											</div>
											<p class="forwp-account-password-row">
												<label class="forwp-account-password-remember">
													<input type="checkbox" disabled>
													<?php esc_html_e( 'Remember me', '4wp-account' ); ?>
												</label>
											</p>
											<p class="forwp-account-password-actions">
												<span class="forwp-account-password-submit forwp-account-preview-submit"><?php esc_html_e( 'Sign in', '4wp-account' ); ?></span>
											</p>
										</div>
										<p class="forwp-account-password-links"><span class="forwp-account-preview-link"><?php esc_html_e( 'Forgot password?', '4wp-account' ); ?></span></p>
									</div>
									<p class="forwp-account-signin-divider"><span><?php esc_html_e( 'or continue with', '4wp-account' ); ?></span></p>
									<?php
									echo SocialButtons::render_list( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plugin SVG + escaped labels.
										array(
											'gmail'  => __( 'Sign in with Google', '4wp-account' ),
											'github' => __( 'Sign in with GitHub', '4wp-account' ),
										),
										array(
											'account' => true,
											'tag'     => 'span',
											'preview' => true,
										)
									);
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="forwp-account-appearance__fields-col">
				<div class="forwp-account-card">
					<h3 class="forwp-account-section-title"><?php esc_html_e( 'Card', '4wp-account' ); ?></h3>
					<p class="forwp-account-muted"><?php esc_html_e( 'Leave a field empty to use the theme. Save to apply on the account page.', '4wp-account' ); ?></p>

					<?php
					self::render_color_row(
						'bg_color',
						__( 'Background', '4wp-account' ),
						$overrides['bg_color'],
						$defaults['bg_color']
					);
					self::render_color_row(
						'border_color',
						__( 'Border color', '4wp-account' ),
						$overrides['border_color'],
						$defaults['border_color']
					);
					?>

					<div class="forwp-account-field">
						<label class="forwp-account-field__label" for="forwp_account_appearance_border_width"><?php esc_html_e( 'Border width', '4wp-account' ); ?></label>
						<input
							type="range"
							id="forwp_account_appearance_border_width"
							name="forwp_account_appearance[border_width]"
							min="0"
							max="8"
							step="1"
							value="<?php echo esc_attr( $resolved['border_width'] ); ?>"
							data-appearance-key="border-width"
							data-default="<?php echo esc_attr( $defaults['border_width'] ); ?>"
						>
						<span class="forwp-account-range-value" data-range-for="forwp_account_appearance_border_width"><?php echo esc_html( $resolved['border_width'] ); ?>px</span>
					</div>

					<div class="forwp-account-field">
						<label class="forwp-account-field__label" for="forwp_account_appearance_border_radius"><?php esc_html_e( 'Border radius', '4wp-account' ); ?></label>
						<input
							type="range"
							id="forwp_account_appearance_border_radius"
							name="forwp_account_appearance[border_radius]"
							min="0"
							max="48"
							step="1"
							value="<?php echo esc_attr( $resolved['border_radius'] ); ?>"
							data-appearance-key="radius"
							data-default="<?php echo esc_attr( $defaults['border_radius'] ); ?>"
						>
						<span class="forwp-account-range-value" data-range-for="forwp_account_appearance_border_radius"><?php echo esc_html( $resolved['border_radius'] ); ?>px</span>
					</div>
				</div>

				<div class="forwp-account-card">
					<h3 class="forwp-account-section-title"><?php esc_html_e( 'Social buttons', '4wp-account' ); ?></h3>
					<p class="forwp-account-muted"><?php esc_html_e( 'Native keeps each network’s own colors and mark. Solid fills the button. Icons is mark-only.', '4wp-account' ); ?></p>
					<fieldset class="forwp-account-choice-set">
						<legend class="screen-reader-text"><?php esc_html_e( 'Social button style', '4wp-account' ); ?></legend>
						<?php
						$choices = array(
							Appearance::SOCIAL_NATIVE => __( 'Native', '4wp-account' ),
							Appearance::SOCIAL_SOLID  => __( 'Solid', '4wp-account' ),
							Appearance::SOCIAL_ICONS  => __( 'Icons', '4wp-account' ),
						);
						foreach ( $choices as $value => $label ) :
							?>
							<label class="forwp-account-choice">
								<input
									type="radio"
									name="forwp_account_appearance[social_style]"
									value="<?php echo esc_attr( $value ); ?>"
									<?php checked( $social_style, $value ); ?>
									data-social-style="<?php echo esc_attr( $value ); ?>"
								>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<p class="forwp-account-appearance-actions">
						<button type="button" class="button" id="forwp-account-appearance-reset"><?php esc_html_e( 'Reset to theme defaults', '4wp-account' ); ?></button>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Color picker + hex field.
	 *
	 * @param string $key       Setting key.
	 * @param string $label     Field label.
	 * @param string $value     Stored override.
	 * @param string $fallback  Site default.
	 */
	private static function render_color_row( string $key, string $label, string $value, string $fallback ): void {
		$shown = preg_match( '/^#[0-9a-fA-F]{6}$/', $value ) ? $value : $fallback;
		$id    = 'forwp_account_appearance_' . $key;
		$css   = ( 'bg_color' === $key ) ? 'bg' : 'border';
		?>
		<div class="forwp-account-field forwp-account-color-row">
			<span class="forwp-account-field__label"><?php echo esc_html( $label ); ?></span>
			<div class="forwp-account-color-row__controls">
				<input
					type="color"
					id="<?php echo esc_attr( $id ); ?>_swatch"
					value="<?php echo esc_attr( $shown ); ?>"
					aria-label="<?php echo esc_attr( $label ); ?>"
					data-sync-text="<?php echo esc_attr( $id ); ?>"
				>
				<input
					type="text"
					class="regular-text code"
					id="<?php echo esc_attr( $id ); ?>"
					name="forwp_account_appearance[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( $value ); ?>"
					placeholder="<?php echo esc_attr( $fallback ); ?>"
					data-appearance-key="<?php echo esc_attr( $css ); ?>"
					data-default="<?php echo esc_attr( $fallback ); ?>"
					autocomplete="off"
				>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $post Raw POST.
	 */
	public static function save( array $post ): void {
		$raw = isset( $post['forwp_account_appearance'] ) && is_array( $post['forwp_account_appearance'] )
			? $post['forwp_account_appearance']
			: array();

		Appearance::set_overrides( $raw );
	}
}
