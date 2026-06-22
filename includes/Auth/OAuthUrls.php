<?php
/**
 * REST URLs for OAuth flows.
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OAuth-related REST URLs.
 */
class OAuthUrls {

	/**
	 * OAuth callback URL for a provider.
	 *
	 * @param string $provider Provider storage key (gmail, github, …).
	 */
	public static function callback_url( string $provider ): string {
		$provider = sanitize_key( $provider );

		return esc_url_raw( rest_url( 'forwp-account/v1/callback/' . $provider ) );
	}
}
