<?php
/**
 * Guest sign-in card appearance (theme.json defaults + optional overrides).
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve, sanitize, and emit CSS variables for the guest account card.
 */
class Appearance {

	public const OPTION = 'forwp_account_appearance';

	public const KEYS = array(
		'bg_color',
		'border_color',
		'border_radius',
		'border_width',
		'social_style',
		'preview_width',
	);

	public const SOCIAL_NATIVE = 'native';
	public const SOCIAL_SOLID  = 'solid';
	public const SOCIAL_ICONS  = 'icons';

	public const SOCIAL_STYLES = array(
		self::SOCIAL_NATIVE,
		self::SOCIAL_SOLID,
		self::SOCIAL_ICONS,
	);

	/**
	 * @var array<string, string>|null
	 */
	private static $theme_json_cache = null;

	/**
	 * Plugin fallbacks when theme.json has no hex.
	 *
	 * @return array<string, string>
	 */
	public static function plugin_fallbacks(): array {
		return array(
			'bg_color'       => '#ffffff',
			'border_color'   => '#dddddd',
			'border_radius'  => '8',
			'border_width'   => '1',
			'social_style'   => self::SOCIAL_NATIVE,
			'preview_width'  => 'wide',
		);
	}

	/**
	 * Empty overrides (use site defaults).
	 *
	 * @return array<string, string>
	 */
	public static function empty_overrides(): array {
		$out = array();
		foreach ( self::KEYS as $key ) {
			$out[ $key ] = '';
		}

		return $out;
	}

	/**
	 * Site defaults: plugin → theme.json.
	 *
	 * @return array<string, string>
	 */
	public static function site_defaults(): array {
		$out = self::plugin_fallbacks();
		foreach ( self::from_theme_json() as $key => $value ) {
			if ( '' !== $value ) {
				$out[ $key ] = $value;
			}
		}

		return $out;
	}

	/**
	 * Stored overrides (empty string = site default).
	 *
	 * @return array<string, string>
	 */
	public static function get_overrides(): array {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return self::sanitize( $stored );
	}

	/**
	 * @param array<string, mixed> $raw Posted appearance.
	 */
	public static function set_overrides( array $raw ): void {
		update_option( self::OPTION, self::sanitize( $raw ), false );
	}

	/**
	 * Site default with overrides applied.
	 *
	 * @return array<string, string>
	 */
	public static function resolve(): array {
		$defaults  = self::site_defaults();
		$overrides = self::get_overrides();
		$out       = $defaults;

		foreach ( self::KEYS as $key ) {
			if ( isset( $overrides[ $key ] ) && '' !== $overrides[ $key ] ) {
				$out[ $key ] = $overrides[ $key ];
			}
		}

		return $out;
	}

	/**
	 * @param array<string, mixed> $raw Raw map.
	 * @return array<string, string>
	 */
	public static function sanitize( array $raw ): array {
		$out = self::empty_overrides();

		foreach ( self::KEYS as $key ) {
			if ( ! array_key_exists( $key, $raw ) ) {
				continue;
			}
			$out[ $key ] = self::sanitize_value( $key, $raw[ $key ] );
		}

		return $out;
	}

	/**
	 * Social button treatment: native brand, solid fill, or icons only.
	 */
	public static function social_style(): string {
		$resolved = self::resolve();
		$style    = isset( $resolved['social_style'] ) ? (string) $resolved['social_style'] : self::SOCIAL_NATIVE;

		return in_array( $style, self::SOCIAL_STYLES, true ) ? $style : self::SOCIAL_NATIVE;
	}

	/**
	 * Admin Sign-in card preview width (not used on the frontend).
	 */
	public static function preview_width(): string {
		$resolved = self::resolve();
		$width    = isset( $resolved['preview_width'] ) ? (string) $resolved['preview_width'] : 'wide';

		return ( 'narrow' === $width ) ? 'narrow' : 'wide';
	}

	/**
	 * Inline CSS for the guest card.
	 */
	public static function inline_css(): string {
		$vars = self::css_variables( self::resolve() );
		$parts = array();

		foreach ( $vars as $name => $value ) {
			$parts[] = $name . ': ' . $value;
		}

		return '.forwp-account.forwp-account--guest{' . implode( ';', $parts ) . ';}';
	}

	/**
	 * @param array<string, string> $resolved Resolved appearance.
	 * @return array<string, string>
	 */
	public static function css_variables( array $resolved ): array {
		$radius = isset( $resolved['border_radius'] ) ? (string) $resolved['border_radius'] : '8';
		$width  = isset( $resolved['border_width'] ) ? (string) $resolved['border_width'] : '1';

		return array(
			'--forwp-account-bg'            => isset( $resolved['bg_color'] ) ? (string) $resolved['bg_color'] : '#ffffff',
			'--forwp-account-border'        => isset( $resolved['border_color'] ) ? (string) $resolved['border_color'] : '#dddddd',
			'--forwp-account-border-width'  => $width . 'px',
			'--forwp-account-radius'        => $radius . 'px',
		);
	}

	/**
	 * Inline style attribute for admin preview.
	 */
	public static function preview_style_attr(): string {
		$parts = array();
		foreach ( self::css_variables( self::resolve() ) as $name => $value ) {
			$parts[] = $name . ': ' . $value;
		}

		return implode( '; ', $parts );
	}

	/**
	 * @param string $key   Setting key.
	 * @param mixed  $value Raw value.
	 */
	private static function sanitize_value( string $key, $value ): string {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';

		if ( '' === $value ) {
			return '';
		}

		if ( 'bg_color' === $key || 'border_color' === $key ) {
			if ( preg_match( '/^#([0-9a-fA-F]{3})$/', $value, $match ) ) {
				$hex = $match[1];

				return '#' . $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			}

			if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $value ) ) {
				return strtolower( $value );
			}

			return '';
		}

		if ( 'border_radius' === $key ) {
			$n = (int) $value;

			return (string) max( 0, min( 48, $n ) );
		}

		if ( 'border_width' === $key ) {
			$n = (int) $value;

			return (string) max( 0, min( 8, $n ) );
		}

		if ( 'social_style' === $key ) {
			return in_array( $value, self::SOCIAL_STYLES, true ) ? $value : '';
		}

		if ( 'preview_width' === $key ) {
			return in_array( $value, array( 'wide', 'narrow' ), true ) ? $value : '';
		}

		return '';
	}

	/**
	 * Hex values from theme.json when they resolve.
	 *
	 * @return array<string, string>
	 */
	private static function from_theme_json(): array {
		if ( null !== self::$theme_json_cache ) {
			return self::$theme_json_cache;
		}

		$out = array();

		if ( function_exists( 'wp_get_global_styles' ) ) {
			$styles = wp_get_global_styles();
			if ( is_array( $styles ) ) {
				if ( ! empty( $styles['color']['background'] ) ) {
					$color = self::hex_or_empty( (string) $styles['color']['background'] );
					if ( '' !== $color ) {
						$out['bg_color'] = $color;
					}
				}
				if ( ! empty( $styles['border']['color'] ) ) {
					$color = self::hex_or_empty( (string) $styles['border']['color'] );
					if ( '' !== $color ) {
						$out['border_color'] = $color;
					}
				}
				if ( isset( $styles['border']['radius'] ) ) {
					$radius = self::css_size_to_px( $styles['border']['radius'] );
					if ( '' !== $radius ) {
						$out['border_radius'] = $radius;
					}
				}
				if ( isset( $styles['border']['width'] ) ) {
					$width = self::css_size_to_px( $styles['border']['width'] );
					if ( '' !== $width ) {
						$out['border_width'] = $width;
					}
				}
			}
		}

		if ( function_exists( 'wp_get_global_settings' ) ) {
			$settings = wp_get_global_settings();
			$palette  = array();
			if ( isset( $settings['color']['palette']['theme'] ) && is_array( $settings['color']['palette']['theme'] ) ) {
				$palette = $settings['color']['palette']['theme'];
			}

			foreach ( $palette as $swatch ) {
				if ( ! is_array( $swatch ) ) {
					continue;
				}
				$slug  = isset( $swatch['slug'] ) ? (string) $swatch['slug'] : '';
				$color = isset( $swatch['color'] ) ? self::hex_or_empty( (string) $swatch['color'] ) : '';
				if ( '' === $color ) {
					continue;
				}
				if ( 'base' === $slug && empty( $out['bg_color'] ) ) {
					$out['bg_color'] = $color;
				}
				if ( ( 'contrast-3' === $slug || 'contrast' === $slug ) && empty( $out['border_color'] ) ) {
					$out['border_color'] = $color;
				}
			}
		}

		self::$theme_json_cache = $out;

		return $out;
	}

	/**
	 * @param string $raw Color.
	 */
	private static function hex_or_empty( string $raw ): string {
		$raw = trim( $raw );
		if ( 0 === strpos( $raw, 'var:' ) || 0 === strpos( $raw, 'var(' ) ) {
			return '';
		}

		return self::sanitize_value( 'bg_color', $raw );
	}

	/**
	 * @param mixed $raw Size.
	 */
	private static function css_size_to_px( $raw ): string {
		if ( is_array( $raw ) ) {
			if ( isset( $raw['top'] ) ) {
				$raw = $raw['top'];
			} elseif ( isset( $raw['size'] ) ) {
				$unit = isset( $raw['unit'] ) ? (string) $raw['unit'] : 'px';
				$raw  = (string) $raw['size'] . $unit;
			}
		}

		if ( ! is_string( $raw ) && ! is_numeric( $raw ) ) {
			return '';
		}

		$raw = trim( (string) $raw );
		if ( preg_match( '/^(\d+(?:\.\d+)?)px$/', $raw, $match ) ) {
			return (string) (int) round( (float) $match[1] );
		}
		if ( preg_match( '/^\d+$/', $raw ) ) {
			return $raw;
		}

		return '';
	}
}
