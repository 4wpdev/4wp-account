<?php
/**
 * Gmail OAuth Provider
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
 * Gmail provider class
 */
class Gmail extends BaseProvider {

	/**
	 * Provider instance
	 *
	 * @var Gmail
	 */
	private static $instance = null;

	/**
	 * Authorization endpoint
	 *
	 * @var string
	 */
	protected $authorization_endpoint = 'https://accounts.google.com/o/oauth2/v2/auth';

	/**
	 * Token endpoint
	 *
	 * @var string
	 */
	protected $token_endpoint = 'https://oauth2.googleapis.com/token';

	/**
	 * User info endpoint
	 *
	 * @var string
	 */
	protected $user_info_endpoint = 'https://www.googleapis.com/oauth2/v2/userinfo';

	/**
	 * Get provider instance
	 *
	 * @return Gmail
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->provider_id   = 'gmail';
		$this->provider_name = 'Gmail';
		$this->client_id     = $this->get_option( 'client_id' );
		$this->client_secret = $this->get_option( 'client_secret' );
		$this->redirect_uri  = $this->get_redirect_uri();
		$this->scopes        = [ 'openid', 'email', 'profile' ];
	}

	/**
	 * Check if provider is enabled
	 *
	 * @return bool
	 */
	public function is_enabled() {
		if ( ! ProviderSettings::is_enabled( $this->provider_id ) ) {
			return false;
		}

		return ! empty( $this->client_id ) && ! empty( $this->client_secret );
	}

	/**
	 * Get authorization URL
	 *
	 * @return string
	 */
	public function get_authorization_url() {
		$state = OAuthState::create( $this->provider_id );

		$params = [
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->redirect_uri,
			'scope'         => implode( ' ', $this->scopes ),
			'response_type' => 'code',
			'state'         => $state,
			'access_type'   => 'offline',
			'prompt'        => 'consent',
		];

		return $this->authorization_endpoint . '?' . http_build_query( $params );
	}

	/**
	 * Handle OAuth callback
	 *
	 * @param string $code Authorization code.
	 * @param string $state State parameter.
	 * @return array|WP_Error
	 */
	public function handle_callback( $code, $state = '' ) {
		// State verified in Routes::handle_callback() before this runs.

		// Exchange code for token
		$token_response = $this->exchange_code_for_token( $code );

		if ( is_wp_error( $token_response ) ) {
			return $token_response;
		}

		$access_token = $token_response['access_token'];

		// Get user info
		$user_info = $this->get_user_info( $access_token );

		if ( is_wp_error( $user_info ) ) {
			return $user_info;
		}

		// Create or update user
		$user_data = [
			'id'         => $user_info['id'],
			'email'      => $user_info['email'],
			'name'       => $user_info['name'],
			'first_name' => $user_info['given_name'] ?? '',
			'last_name'  => $user_info['family_name'] ?? '',
			'avatar'     => $user_info['picture'] ?? '',
		];

		$user_id = $this->create_or_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$this->login_user( (int) $user_id );

		return [
			'user_id' => $user_id,
			'user_data' => $user_data,
		];
	}

	/**
	 * Exchange authorization code for access token
	 *
	 * @param string $code Authorization code.
	 * @return array|WP_Error
	 */
	protected function exchange_code_for_token( $code ) {
		$response = wp_remote_post(
			$this->token_endpoint,
			[
				'body' => [
					'code'          => $code,
					'client_id'     => $this->client_id,
					'client_secret' => $this->client_secret,
					'redirect_uri'  => $this->redirect_uri,
					'grant_type'    => 'authorization_code',
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'oauth_error', $body['error_description'] ?? $body['error'] );
		}

		return $body;
	}

	/**
	 * Get user info from provider
	 *
	 * @param string $access_token Access token.
	 * @return array|WP_Error
	 */
	protected function get_user_info( $access_token ) {
		$response = wp_remote_get(
			$this->user_info_endpoint,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error( 'api_error', $body['error']['message'] ?? 'Unknown error' );
		}

		return $body;
	}

	/**
	 * Get redirect URI
	 *
	 * @return string
	 */
	protected function get_redirect_uri() {
		return OAuthUrls::callback_url( $this->provider_id );
	}
}





