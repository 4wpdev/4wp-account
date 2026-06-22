<?php
/**
 * Which auth providers are available and enabled in admin.
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single source of truth for provider availability + admin toggles.
 */
class ProviderSettings {

	/**
	 * @return array<string, array<string, string>>
	 */
	public static function get_registry(): array {
		return array(
			'gmail'    => array(
				'label'  => __( 'Google', '4wp-account' ),
				'status' => 'active',
			),
			'github'   => array(
				'label'  => __( 'GitHub', '4wp-account' ),
				'status' => 'active',
			),
			'facebook' => array(
				'label'  => __( 'Facebook', '4wp-account' ),
				'status' => 'soon',
			),
			'tiktok'   => array(
				'label'  => __( 'TikTok', '4wp-account' ),
				'status' => 'soon',
			),
		);
	}

	/**
	 * Provider IDs that can be enabled in admin (not "coming soon").
	 *
	 * @return string[]
	 */
	public static function get_available_ids(): array {
		$ids = array();

		foreach ( self::get_registry() as $id => $meta ) {
			if ( 'active' === ( $meta['status'] ?? '' ) ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * @param string $provider_id Provider storage key.
	 */
	public static function is_available( string $provider_id ): bool {
		$registry = self::get_registry();

		return isset( $registry[ $provider_id ] ) && 'active' === ( $registry[ $provider_id ]['status'] ?? '' );
	}

	/**
	 * Admin toggle + availability (ignores stale DB flags for "soon" providers).
	 *
	 * @param string $provider_id Provider storage key.
	 */
	public static function is_enabled( string $provider_id ): bool {
		$provider_id = sanitize_key( $provider_id );

		if ( ! self::is_available( $provider_id ) ) {
			return false;
		}

		return '1' === get_option( 'forwp_account_provider_enabled_' . $provider_id, '0' );
	}

	/**
	 * Button labels for the sign-in UI.
	 *
	 * @return array<string, string>
	 */
	public static function get_button_labels(): array {
		return array(
			'gmail'    => __( 'Sign in with Google', '4wp-account' ),
			'github'   => __( 'Sign in with GitHub', '4wp-account' ),
			'facebook' => __( 'Sign in with Facebook', '4wp-account' ),
			'tiktok'   => __( 'Sign in with TikTok', '4wp-account' ),
		);
	}

	/**
	 * Enabled providers for frontend buttons.
	 *
	 * @return array<string, string> Provider ID => button label.
	 */
	public static function get_enabled_for_display(): array {
		$enabled = array();

		foreach ( self::get_button_labels() as $id => $label ) {
			if ( self::is_enabled( $id ) ) {
				$enabled[ $id ] = $label;
			}
		}

		return $enabled;
	}

	/**
	 * Keep only providers that are enabled in settings.
	 *
	 * @param string[] $provider_ids Requested provider IDs.
	 * @return string[]
	 */
	public static function filter_enabled_ids( array $provider_ids ): array {
		$filtered = array();

		foreach ( $provider_ids as $provider_id ) {
			$provider_id = sanitize_key( (string) $provider_id );
			if ( $provider_id !== '' && self::is_enabled( $provider_id ) ) {
				$filtered[] = $provider_id;
			}
		}

		return array_values( array_unique( $filtered ) );
	}

	/**
	 * Parse comma-separated provider list from shortcode/block attributes.
	 *
	 * @param string $providers_attr Raw providers attribute.
	 * @return string[]
	 */
	public static function parse_provider_list( string $providers_attr ): array {
		if ( $providers_attr === '' || $providers_attr === 'auto' || $providers_attr === '*' ) {
			return array_keys( self::get_enabled_for_display() );
		}

		$requested = array_filter(
			array_map(
				static function ( $id ) {
					return sanitize_key( trim( (string) $id ) );
				},
				explode( ',', $providers_attr )
			)
		);

		$enabled = self::filter_enabled_ids( array_values( $requested ) );

		return ! empty( $enabled ) ? $enabled : array_keys( self::get_enabled_for_display() );
	}
}
