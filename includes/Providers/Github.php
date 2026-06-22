<?php
/**
 * GitHub OAuth Provider
 *
 * @package ForWP\Account\Providers
 */

namespace ForWP\Account\Providers;

use ForWP\Account\Auth\ProviderSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub OAuth 2.0 provider.
 */
class Github extends BaseProvider {

	/**
	 * @var Github|null
	 */
	private static $instance = null;

	protected $authorization_endpoint = 'https://github.com/login/oauth/authorize';

	protected $token_endpoint = 'https://github.com/login/oauth/access_token';

	protected $user_info_endpoint = 'https://api.github.com/user';

	/**
	 * @return Github
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->provider_id   = 'github';
		$this->provider_name = 'GitHub';
		$this->client_id     = $this->get_option( 'client_id' );
		$this->client_secret = $this->get_option( 'client_secret' );
		$this->redirect_uri  = $this->get_redirect_uri();
		$this->scopes        = array( 'read:user', 'user:email' );
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
		$state = wp_generate_password( 32, false );
		set_transient( 'forwp_account_github_state_' . $state, $state, 600 );

		$params = array(
			'client_id'    => $this->client_id,
			'redirect_uri' => $this->redirect_uri,
			'scope'        => implode( ' ', $this->scopes ),
			'state'        => $state,
		);

		return $this->authorization_endpoint . '?' . http_build_query( $params );
	}

	/**
	 * @param string $code  Authorization code.
	 * @param string $state State parameter.
	 * @return array|\WP_Error
	 */
	public function handle_callback( $code, $state = '' ) {
		if ( ! empty( $state ) ) {
			$stored_state = get_transient( 'forwp_account_github_state_' . $state );
			if ( $stored_state !== $state ) {
				return new \WP_Error( 'invalid_state', __( 'Invalid state parameter', '4wp-account' ) );
			}
			delete_transient( 'forwp_account_github_state_' . $state );
		}

		$token_response = $this->exchange_code_for_token( $code );

		if ( is_wp_error( $token_response ) ) {
			return $token_response;
		}

		$access_token = $token_response['access_token'] ?? '';
		if ( $access_token === '' ) {
			return new \WP_Error( 'oauth_error', __( 'Access token is missing', '4wp-account' ) );
		}

		$user_info = $this->get_user_info( $access_token );

		if ( is_wp_error( $user_info ) ) {
			return $user_info;
		}

		$email = isset( $user_info['email'] ) && is_string( $user_info['email'] ) ? $user_info['email'] : '';
		if ( $email === '' ) {
			$email = $this->resolve_primary_email( $access_token );
		}

		if ( $email === '' ) {
			return new \WP_Error( 'no_email', __( 'GitHub did not return a verified email address', '4wp-account' ) );
		}

		$user_data = array(
			'id'         => (string) ( $user_info['id'] ?? '' ),
			'email'      => $email,
			'username'   => $user_info['login'] ?? '',
			'name'       => $user_info['name'] ?? ( $user_info['login'] ?? '' ),
			'first_name' => '',
			'last_name'  => '',
			'avatar'     => $user_info['avatar_url'] ?? '',
		);

		$user_id = $this->create_or_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		return array(
			'user_id'   => $user_id,
			'user_data' => $user_data,
		);
	}

	/**
	 * @param string $code Authorization code.
	 * @return array|\WP_Error
	 */
	protected function exchange_code_for_token( $code ) {
		$response = wp_remote_post(
			$this->token_endpoint,
			array(
				'headers' => array(
					'Accept' => 'application/json',
				),
				'body'    => array(
					'client_id'     => $this->client_id,
					'client_secret' => $this->client_secret,
					'code'          => $code,
					'redirect_uri'  => $this->redirect_uri,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new \WP_Error( 'oauth_error', __( 'Invalid token response from GitHub', '4wp-account' ) );
		}

		if ( isset( $body['error'] ) ) {
			$message = is_string( $body['error_description'] ?? null )
				? $body['error_description']
				: (string) $body['error'];
			return new \WP_Error( 'oauth_error', $message );
		}

		return $body;
	}

	/**
	 * @param string $access_token Access token.
	 * @return array|\WP_Error
	 */
	protected function get_user_info( $access_token ) {
		$response = wp_remote_get(
			$this->user_info_endpoint,
			array(
				'headers' => $this->api_headers( $access_token ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new \WP_Error( 'api_error', __( 'Invalid user response from GitHub', '4wp-account' ) );
		}

		if ( isset( $body['message'] ) ) {
			return new \WP_Error( 'api_error', (string) $body['message'] );
		}

		return $body;
	}

	/**
	 * @param string $access_token Access token.
	 */
	private function resolve_primary_email( string $access_token ): string {
		$response = wp_remote_get(
			'https://api.github.com/user/emails',
			array(
				'headers' => $this->api_headers( $access_token ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$emails = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $emails ) ) {
			return '';
		}

		foreach ( $emails as $row ) {
			if ( ! is_array( $row ) || empty( $row['email'] ) || empty( $row['verified'] ) ) {
				continue;
			}

			if ( ! empty( $row['primary'] ) ) {
				return (string) $row['email'];
			}
		}

		foreach ( $emails as $row ) {
			if ( is_array( $row ) && ! empty( $row['email'] ) && ! empty( $row['verified'] ) ) {
				return (string) $row['email'];
			}
		}

		return '';
	}

	/**
	 * @param string $access_token Access token.
	 * @return array<string, string>
	 */
	private function api_headers( string $access_token ): array {
		return array(
			'Authorization' => 'Bearer ' . $access_token,
			'Accept'        => 'application/vnd.github+json',
			'User-Agent'    => '4WP-Account',
		);
	}

	/**
	 * @return string
	 */
	protected function get_redirect_uri() {
		return home_url( '/wp-json/forwp-account/v1/callback/github' );
	}
}
