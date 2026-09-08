<?php
/**
 * Post-OAuth redirect targets (absolute URLs only).
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize and send the browser away from the REST callback.
 */
class OAuthRedirect {

	public const COOKIE_NAME = 'forwp_account_redirect_to';

	/**
	 * Return an absolute http(s) URL on this site, or the fallback.
	 *
	 * @param string $url      Candidate URL (may be encoded or relative).
	 * @param string $fallback Used when $url is empty or unsafe.
	 */
	public static function normalize( string $url, string $fallback = '' ): string {
		$url      = self::decode_url( $url );
		$fallback = trim( $fallback );

		if ( '' === $url ) {
			return '' !== $fallback ? self::ensure_absolute( $fallback ) : '';
		}

		$url = self::ensure_absolute( $url );

		if ( self::is_unsafe_target( $url ) || ! self::has_scheme_and_host( $url ) ) {
			return '' !== $fallback ? self::ensure_absolute( $fallback ) : '';
		}

		$validated = wp_validate_redirect( $url, '' );

		if ( '' === $validated || self::is_unsafe_target( $validated ) || ! self::has_scheme_and_host( $validated ) ) {
			return '' !== $fallback ? self::ensure_absolute( $fallback ) : '';
		}

		return $validated;
	}

	/**
	 * Send a real HTTP redirect instead of a REST JSON 302 body.
	 *
	 * @param string $url Target URL.
	 * @return \WP_REST_Response
	 */
	public static function rest_response( string $url ): \WP_REST_Response {
		$url = self::normalize( $url, home_url( '/' ) );

		add_filter(
			'rest_pre_serve_request',
			static function ( $served ) use ( $url ) {
				unset( $served );

				if ( ! headers_sent() ) {
					nocache_headers();
					wp_safe_redirect( $url, 302 );
					exit;
				}

				self::print_html_fallback( $url );
				exit;
			},
			0
		);

		$response = new \WP_REST_Response( null, 302 );
		$response->header( 'Location', $url );

		return $response;
	}

	/**
	 * Persist a safe redirect target until the OAuth callback.
	 *
	 * @param string $url Raw redirect_to value.
	 */
	public static function persist_cookie( string $url ): void {
		$url = self::normalize( $url, '' );

		if ( '' === $url || self::is_unsafe_target( $url ) ) {
			return;
		}

		$path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		setcookie( self::COOKIE_NAME, $url, time() + 600, $path, $domain, is_ssl(), true );
	}

	/**
	 * Read and clear the post-login cookie.
	 *
	 * @param string $default_url Fallback URL.
	 */
	public static function consume_cookie( string $default_url ): string {
		$raw = '';

		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			$raw = (string) $_COOKIE[ self::COOKIE_NAME ];
		}

		self::clear_cookie();

		return self::normalize( $raw, $default_url );
	}

	/**
	 * @param string $url Raw value.
	 */
	private static function decode_url( string $url ): string {
		$url     = trim( wp_unslash( $url ) );
		$decoded = rawurldecode( $url );

		if ( str_contains( $decoded, '%3A' ) || str_contains( $decoded, '%2F' ) ) {
			$decoded = rawurldecode( $decoded );
		}

		return trim( $decoded );
	}

	/**
	 * @param string $url Candidate.
	 */
	private static function ensure_absolute( string $url ): string {
		$url = trim( $url );

		if ( '' === $url ) {
			return home_url( '/' );
		}

		if ( str_starts_with( $url, '//' ) ) {
			$url = ( is_ssl() ? 'https:' : 'http:' ) . $url;
		}

		if ( str_starts_with( $url, '/' ) ) {
			return home_url( $url );
		}

		if ( self::has_scheme_and_host( $url ) ) {
			return $url;
		}

		return home_url( '/' . ltrim( $url, '/' ) );
	}

	/**
	 * @param string $url Candidate.
	 */
	private static function has_scheme_and_host( string $url ): bool {
		$parts = wp_parse_url( $url );

		if ( ! is_array( $parts ) ) {
			return false;
		}

		$scheme = strtolower( (string) ( $parts['scheme'] ?? '' ) );
		$host   = (string) ( $parts['host'] ?? '' );

		return '' !== $host && in_array( $scheme, array( 'http', 'https' ), true );
	}

	/**
	 * Never send the browser back into the REST OAuth callback.
	 *
	 * @param string $url Candidate.
	 */
	private static function is_unsafe_target( string $url ): bool {
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );

		if ( str_contains( $path, '/wp-json/' ) ) {
			return true;
		}

		if ( str_contains( $path, '/forwp-account/v1/callback' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Clear the redirect cookie.
	 */
	private static function clear_cookie(): void {
		$path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		setcookie( self::COOKIE_NAME, '', time() - HOUR_IN_SECONDS, $path, $domain, is_ssl(), true );

		unset( $_COOKIE[ self::COOKIE_NAME ] );
	}

	/**
	 * Last-resort redirect if headers were already sent.
	 *
	 * @param string $url Absolute URL.
	 */
	private static function print_html_fallback( string $url ): void {
		header( 'Content-Type: text/html; charset=utf-8' );

		echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
		echo '<meta http-equiv="refresh" content="0;url=' . esc_attr( $url ) . '">';
		echo '</head><body>';
		echo '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Continue', '4wp-account' ) . '</a>';
		echo '<script>window.location.replace(' . wp_json_encode( $url ) . ');</script>';
		echo '</body></html>';
	}
}
