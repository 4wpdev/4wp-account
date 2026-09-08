<?php
/**
 * Social sign-in buttons (native brand colors + icons).
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Markup for provider buttons. Brand fills stay native; the card tokens do not recolor them.
 */
class SocialButtons {

	/**
	 * Wrapper classes for a button group.
	 *
	 * @param array<string, mixed> $args Optional. account => add --account layout.
	 */
	public static function wrapper_class( array $args = array() ): string {
		$style   = Appearance::social_style();
		$classes = array(
			'forwp-account-signin',
			'forwp-account-signin--style-' . $style,
		);

		if ( ! empty( $args['account'] ) ) {
			$classes[] = 'forwp-account-signin--account';
		}

		if ( ! empty( $args['woocommerce'] ) ) {
			$classes[] = 'forwp-account-signin--woocommerce';
		}

		return implode( ' ', $classes );
	}

	/**
	 * @param string[]             $providers Provider ID => label, or list of IDs.
	 * @param array<string, mixed> $args      Wrapper args + button args.
	 */
	public static function render_list( array $providers, array $args = array() ): string {
		$labels = ProviderSettings::get_button_labels();
		$items  = array();

		foreach ( $providers as $key => $value ) {
			if ( is_int( $key ) ) {
				$id = sanitize_key( (string) $value );
				if ( isset( $labels[ $id ] ) ) {
					$items[ $id ] = $labels[ $id ];
				}
				continue;
			}

			$id = sanitize_key( (string) $key );
			if ( '' === $id ) {
				continue;
			}
			$items[ $id ] = (string) $value;
		}

		if ( empty( $items ) ) {
			return '';
		}

		$html = '<div class="' . esc_attr( self::wrapper_class( $args ) ) . '">';
		foreach ( $items as $id => $label ) {
			$html .= self::render_button( (string) $id, (string) $label, $args );
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * @param array<string, mixed> $args tag, class, preview, extra_class.
	 */
	public static function render_button( string $provider, string $label, array $args = array() ): string {
		$provider = sanitize_key( $provider );
		$style    = Appearance::social_style();
		$tag      = ( isset( $args['tag'] ) && 'span' === $args['tag'] ) ? 'span' : 'button';
		$preview  = ! empty( $args['preview'] );
		$classes  = array(
			'forwp-account-signin-btn',
			'forwp-account-signin-btn-' . $provider,
			'forwp-account-signin-btn--' . $style,
		);

		if ( ! empty( $args['extra_class'] ) ) {
			$classes[] = (string) $args['extra_class'];
		}

		$icon = self::icon_html( $provider );
		$text = '<span class="forwp-account-signin-btn__label">' . esc_html( $label ) . '</span>';

		$attrs = array(
			'class="' . esc_attr( implode( ' ', $classes ) ) . '"',
			'data-provider="' . esc_attr( $provider ) . '"',
			'aria-label="' . esc_attr( $label ) . '"',
		);

		if ( 'button' === $tag && ! $preview ) {
			$attrs[] = 'type="button"';
		}

		return '<' . $tag . ' ' . implode( ' ', $attrs ) . '>' . $icon . $text . '</' . $tag . '>';
	}

	/**
	 * Google outputs both the four-color G and a mono mark; CSS picks one per style.
	 */
	public static function icon_html( string $provider ): string {
		if ( 'gmail' === $provider ) {
			return '<span class="forwp-account-signin-btn__icon forwp-account-signin-btn__icon--brand" aria-hidden="true">' . self::google_color_svg() . '</span>'
				. '<span class="forwp-account-signin-btn__icon forwp-account-signin-btn__icon--mono" aria-hidden="true">' . self::mono_svg( 'gmail' ) . '</span>';
		}

		$svg = self::mono_svg( $provider );
		if ( '' === $svg ) {
			return '';
		}

		return '<span class="forwp-account-signin-btn__icon forwp-account-signin-btn__icon--mono" aria-hidden="true">' . $svg . '</span>';
	}

	/**
	 * Four-color Google G (native button on white).
	 */
	private static function google_color_svg(): string {
		return '<svg viewBox="0 0 24 24" width="18" height="18" focusable="false"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>';
	}

	/**
	 * Monochrome marks (currentColor) for solid / icon buttons.
	 */
	private static function mono_svg( string $provider ): string {
		$paths = array(
			'gmail'    => '<path fill="currentColor" d="M21.35 11.1H12.17v2.96h5.27c-.23 1.22-1.4 3.58-5.27 3.58-3.17 0-5.76-2.62-5.76-5.84s2.59-5.84 5.76-5.84c1.8 0 3.01.77 3.7 1.43l2.52-2.43C16.54 3.54 14.48 2.6 12.17 2.6 7.36 2.6 3.5 6.5 3.5 11.8s3.86 9.2 8.67 9.2c5 0 8.33-3.51 8.33-8.46 0-.57-.06-1-.15-1.44z"/>',
			'github'   => '<path fill="currentColor" d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.26.8-.58v-2.02c-3.34.73-4.04-1.61-4.04-1.61-.55-1.39-1.33-1.76-1.33-1.76-1.09-.74.08-.73.08-.73 1.2.09 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49 1 .1-.78.42-1.3.76-1.6-2.66-.3-5.46-1.33-5.46-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.29-1.55 3.3-1.23 3.3-1.23.66 1.66.25 2.88.12 3.18a4.63 4.63 0 0 1 1.23 3.22c0 4.61-2.8 5.62-5.48 5.92.43.37.82 1.1.82 2.22v3.29c0 .32.2.69.82.58A12 12 0 0 0 12 .3z"/>',
			'facebook' => '<path fill="currentColor" d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H8.08V12h2.36V9.8c0-2.33 1.39-3.62 3.52-3.62.7 0 1.87.12 2.35.18v2.59h-1.33c-1.31 0-1.72.81-1.72 1.65V12h2.93l-.47 2.89h-2.46v6.99A10 10 0 0 0 22 12z"/>',
			'tiktok'   => '<path fill="currentColor" d="M14.5 3c.4 2.6 1.9 4.2 4.5 4.5v3.1c-1.5 0-2.9-.5-4.1-1.3v6.6c0 3.4-2.6 6.1-6.2 6.1S2.5 19.3 2.5 15.9c0-3.3 2.6-6 5.9-6.1v3.2c-1.5.1-2.7 1.3-2.7 2.9 0 1.6 1.3 2.9 2.9 2.9s2.9-1.3 2.9-2.9V3h3z"/>',
			'x'        => '<path fill="currentColor" d="M14.7 10.3 22.4 2h-2.2l-6.6 7.2L8.4 2H2l8.1 11.3L2 22h2.2l7-7.7L15.6 22H22L14.7 10.3zm-2.5 2.7-.8-1.1L5 3.5h2.7l5.1 6.9.8 1.1 6.7 9H17.6l-5.4-7.5z"/>',
		);

		if ( ! isset( $paths[ $provider ] ) ) {
			return '';
		}

		return '<svg viewBox="0 0 24 24" width="18" height="18" focusable="false">' . $paths[ $provider ] . '</svg>';
	}
}
