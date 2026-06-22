<?php
/**
 * OAuth CSRF state tokens for social login.
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create and verify OAuth state parameters.
 */
class OAuthState {

	private const TTL_SECONDS = 600;

	/**
	 * Create a one-time state value for an OAuth authorization request.
	 *
	 * @param string $provider_id Provider storage key.
	 */
	public static function create( string $provider_id ): string {
		$provider_id = sanitize_key( $provider_id );
		$state       = wp_generate_password( 32, false );

		set_transient( self::transient_key( $provider_id, $state ), $state, self::TTL_SECONDS );

		return $state;
	}

	/**
	 * Verify and consume a state value from the OAuth callback.
	 *
	 * @param string $provider_id Provider storage key.
	 * @param string $state       State from the provider redirect.
	 * @return true|\WP_Error
	 */
	public static function verify( string $provider_id, string $state ) {
		$provider_id = sanitize_key( $provider_id );
		$state       = sanitize_text_field( $state );

		if ( $state === '' ) {
			return new \WP_Error( 'missing_state', __( 'Missing state parameter', '4wp-account' ) );
		}

		$key          = self::transient_key( $provider_id, $state );
		$stored_state = get_transient( $key );

		if ( $stored_state !== $state ) {
			return new \WP_Error( 'invalid_state', __( 'Invalid state parameter', '4wp-account' ) );
		}

		delete_transient( $key );

		return true;
	}

	/**
	 * @param string $provider_id Provider storage key.
	 * @param string $state       State token.
	 */
	private static function transient_key( string $provider_id, string $state ): string {
		return 'forwp_account_oauth_state_' . $provider_id . '_' . $state;
	}
}
