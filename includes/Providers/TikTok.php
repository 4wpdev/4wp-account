<?php
/**
 * TikTok OAuth Provider (Login Kit v2).
 *
 * @package ForWP\Account\Providers
 */

namespace ForWP\Account\Providers;

use ForWP\Account\Auth\OAuthState;
use ForWP\Account\Auth\OAuthUrls;
use ForWP\Account\Auth\ProviderSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TikTok Login Kit — Client Key / Secret from developers.tiktok.com.
 */
class TikTok extends BaseProvider {

	/**
	 * @var TikTok|null
	 */
	private static $instance = null;

	protected $authorization_endpoint = 'https://www.tiktok.com/v2/auth/authorize/';

	protected $token_endpoint = 'https://open.tiktokapis.com/v2/oauth/token/';

	protected $user_info_endpoint = 'https://open.tiktokapis.com/v2/user/info/';

	/**
	 * @return TikTok
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->provider_id   = 'tiktok';
		$this->provider_name = 'TikTok';
		$this->client_id     = $this->get_option( 'client_key' );
		$this->client_secret = $this->get_option( 'client_secret' );
		$this->redirect_uri  = $this->get_redirect_uri();
		$this->scopes        = array( 'user.info.basic' );
	}

	/**
	 * @return bool
	 */
	public function is_enabled() {
		if ( ! ProviderSettings::is_enabled( $this->provider_id ) ) {
			return false;
		}

		return ! empty( $this->client_id ) && ! empty( $this->client_secret );
	}

	/**
	 * @return string
	 */
	public function get_authorization_url() {
		$state          = OAuthState::create( $this->provider_id );
		$code_verifier  = $this->create_code_verifier();
		$code_challenge = $this->code_challenge( $code_verifier );

		set_transient( $this->pkce_transient_key( $state ), $code_verifier, 10 * MINUTE_IN_SECONDS );

		$params = array(
			'client_key'             => $this->client_id,
			'redirect_uri'           => $this->redirect_uri,
			'scope'                  => implode( ',', $this->scopes ),
			'response_type'          => 'code',
			'state'                  => $state,
			'code_challenge'         => $code_challenge,
			'code_challenge_method'  => 'S256',
		);

		return $this->authorization_endpoint . '?' . http_build_query( $params );
	}

	/**
	 * @param string $code  Authorization code.
	 * @param string $state OAuth state.
	 * @return array|\WP_Error
	 */
	public function handle_callback( $code, $state = '' ) {
		$token_response = $this->exchange_code_for_token( $code, $state );

		if ( is_wp_error( $token_response ) ) {
			return $token_response;
		}

		$access_token = isset( $token_response['access_token'] ) ? (string) $token_response['access_token'] : '';
		if ( '' === $access_token ) {
			return new \WP_Error( 'oauth_error', __( 'Access token is missing', '4wp-account' ) );
		}

		$user_info = $this->get_user_info( $access_token );

		if ( is_wp_error( $user_info ) ) {
			return $user_info;
		}

		$open_id = (string) ( $user_info['open_id'] ?? '' );
		if ( '' === $open_id ) {
			return new \WP_Error( 'oauth_error', __( 'TikTok did not return a user id', '4wp-account' ) );
		}

		$username = (string) ( $user_info['username'] ?? $user_info['display_name'] ?? '' );
		$name     = (string) ( $user_info['display_name'] ?? $username );

		$user_data = array(
			'id'       => $open_id,
			'email'    => $this->placeholder_email( $open_id ),
			'username' => '' !== $username ? $username : ( 'tiktok_' . substr( sanitize_key( $open_id ), 0, 12 ) ),
			'name'     => $name,
			'avatar'   => (string) ( $user_info['avatar_url'] ?? '' ),
		);

		$user_id = $this->create_or_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$this->login_user( (int) $user_id );

		return array(
			'user_id'   => $user_id,
			'user_data' => $user_data,
		);
	}

	/**
	 * @param string $code  Authorization code.
	 * @param string $state OAuth state (for PKCE verifier).
	 * @return array|\WP_Error
	 */
	protected function exchange_code_for_token( $code, $state = '' ) {
		$body = array(
			'client_key'    => $this->client_id,
			'client_secret' => $this->client_secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->redirect_uri,
		);

		$verifier = get_transient( $this->pkce_transient_key( (string) $state ) );
		if ( is_string( $verifier ) && '' !== $verifier ) {
			$body['code_verifier'] = $verifier;
			delete_transient( $this->pkce_transient_key( (string) $state ) );
		}

		$response = wp_remote_post(
			$this->token_endpoint,
			array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			return new \WP_Error( 'oauth_error', __( 'Invalid token response from TikTok', '4wp-account' ) );
		}

		if ( ! empty( $decoded['error'] ) && 'ok' !== ( $decoded['error']['code'] ?? $decoded['error'] ) ) {
			$message = is_array( $decoded['error'] )
				? (string) ( $decoded['error']['message'] ?? $decoded['error']['code'] ?? 'OAuth error' )
				: (string) ( $decoded['error_description'] ?? $decoded['error'] );
			return new \WP_Error( 'oauth_error', $message );
		}

		$data = isset( $decoded['data'] ) && is_array( $decoded['data'] ) ? $decoded['data'] : $decoded;

		if ( empty( $data['access_token'] ) ) {
			return new \WP_Error( 'oauth_error', __( 'Access token is missing', '4wp-account' ) );
		}

		return $data;
	}

	/**
	 * @param string $access_token Access token.
	 * @return array|\WP_Error
	 */
	protected function get_user_info( $access_token ) {
		$url = add_query_arg(
			array(
				'fields' => 'open_id,union_id,avatar_url,display_name,username',
			),
			$this->user_info_endpoint
		);

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			return new \WP_Error( 'api_error', __( 'Invalid user response from TikTok', '4wp-account' ) );
		}

		$error_code = is_array( $decoded['error'] ?? null ) ? (string) ( $decoded['error']['code'] ?? '' ) : '';
		if ( '' !== $error_code && 'ok' !== $error_code ) {
			return new \WP_Error( 'api_error', (string) ( $decoded['error']['message'] ?? $error_code ) );
		}

		$user = array();
		if ( isset( $decoded['data']['user'] ) && is_array( $decoded['data']['user'] ) ) {
			$user = $decoded['data']['user'];
		} elseif ( isset( $decoded['data'] ) && is_array( $decoded['data'] ) ) {
			$user = $decoded['data'];
		}

		if ( empty( $user['open_id'] ) ) {
			return new \WP_Error( 'api_error', __( 'TikTok did not return a user id', '4wp-account' ) );
		}

		return $user;
	}

	/**
	 * @return string
	 */
	protected function get_redirect_uri() {
		return OAuthUrls::callback_url( $this->provider_id );
	}

	/**
	 * TikTok does not return email on user.info.basic — keep a stable unique address.
	 *
	 * @param string $open_id TikTok open_id.
	 */
	private function placeholder_email( string $open_id ): string {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$host = is_string( $host ) && '' !== $host ? $host : 'example.com';
		$hash = substr( md5( $open_id ), 0, 16 );

		return 'tiktok-' . $hash . '@' . $host;
	}

	/**
	 * @return string
	 */
	private function create_code_verifier(): string {
		return wp_generate_password( 64, false, false );
	}

	/**
	 * @param string $verifier PKCE verifier.
	 */
	private function code_challenge( string $verifier ): string {
		$hash = hash( 'sha256', $verifier, true );

		return rtrim( strtr( base64_encode( $hash ), '+/', '-_' ), '=' );
	}

	/**
	 * @param string $state OAuth state.
	 */
	private function pkce_transient_key( string $state ): string {
		return 'forwp_account_tiktok_pkce_' . md5( $state );
	}
}
