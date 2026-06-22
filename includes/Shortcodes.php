<?php
/**
 * Shortcodes
 *
 * @package ForWP\Account
 */

namespace ForWP\Account;

use ForWP\Account\Auth\ProviderSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes class
 */
class Shortcodes {

	/**
	 * Initialize shortcodes
	 */
	public static function init() {
		add_shortcode( 'forwp_account_login', [ __CLASS__, 'render_login_button' ] );
		add_shortcode( 'forwp_account_signin_buttons', [ __CLASS__, 'render_auth_buttons' ] );
	}

	/**
	 * Render login button shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_login_button( $atts ) {
		$atts = shortcode_atts(
			[
				'provider' => 'gmail',
				'text'     => '',
			],
			$atts,
			'forwp_account_login'
		);

		$provider = sanitize_key( (string) $atts['provider'] );

		if ( ! ProviderSettings::is_enabled( $provider ) ) {
			return '';
		}

		$labels = ProviderSettings::get_button_labels();
		$text   = ! empty( $atts['text'] ) ? sanitize_text_field( $atts['text'] ) : ( $labels[ $provider ] ?? __( 'Sign in', '4wp-account' ) );

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return '<div class="forwp-account-signin-logged-in">' . esc_html__( 'You are logged in as:', '4wp-account' ) . ' <strong>' . esc_html( $current_user->display_name ) . '</strong></div>';
		}

		return sprintf(
			'<button class="forwp-account-signin-btn" data-provider="%s">%s</button>',
			esc_attr( $provider ),
			esc_html( $text )
		);
	}

	/**
	 * Render enabled auth buttons only.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_auth_buttons( $atts ) {
		$atts = shortcode_atts(
			[
				'providers' => 'auto',
			],
			$atts,
			'forwp_account_signin_buttons'
		);

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return '<div class="forwp-account-signin-logged-in">' . esc_html__( 'You are logged in as:', '4wp-account' ) . ' <strong>' . esc_html( $current_user->display_name ) . '</strong></div>';
		}

		return self::render_buttons_markup( ProviderSettings::parse_provider_list( (string) $atts['providers'] ) );
	}

	/**
	 * @param string[] $provider_ids Enabled provider IDs.
	 */
	public static function render_buttons_markup( array $provider_ids ): string {
		$labels = ProviderSettings::get_button_labels();
		$output = '<div class="forwp-account-signin">';

		foreach ( $provider_ids as $provider ) {
			if ( ! isset( $labels[ $provider ] ) ) {
				continue;
			}

			$output .= sprintf(
				'<button type="button" class="forwp-account-signin-btn forwp-account-signin-btn-%1$s" data-provider="%1$s">%2$s</button>',
				esc_attr( $provider ),
				esc_html( $labels[ $provider ] )
			);
		}

		$output .= '</div>';

		return $output;
	}
}
