<?php
/**
 * REST API Routes
 *
 * @package ForWP\Account\API
 */

namespace ForWP\Account\API;

use ForWP\Account\Auth\OAuthRedirect;
use ForWP\Account\Auth\OAuthState;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Routes class
 */
class Routes {

	/**
	 * Plugin instance
	 *
	 * @var Routes
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Routes
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
		$this->init();
	}

	/**
	 * Initialize
	 */
	private function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		$namespace = 'forwp-account/v1';

		// Authorization URLs
		register_rest_route(
			$namespace,
			'/auth/(?P<provider>[a-zA-Z0-9-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_auth_url' ],
				'permission_callback' => '__return_true',
			]
		);

		// OAuth callbacks — public redirect endpoint; CSRF mitigated via required state token.
		register_rest_route(
			$namespace,
			'/callback/(?P<provider>[a-zA-Z0-9-]+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'handle_callback' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Get authorization URL for provider
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_auth_url( $request ) {
		$provider_id = $request->get_param( 'provider' );

		$auth_manager = \ForWP\Account\Auth\AuthManager::get_instance();
		$provider     = $auth_manager->get_provider( $provider_id );

		if ( ! $provider ) {
			return new \WP_Error( 'invalid_provider', __( 'Invalid provider', '4wp-account' ), [ 'status' => 400 ] );
		}

		if ( ! $provider->is_enabled() ) {
			return new \WP_Error( 'provider_disabled', __( 'Provider is not enabled', '4wp-account' ), [ 'status' => 403 ] );
		}

		$auth_url = $provider->get_authorization_url();

		return self::nocache_response(
			new \WP_REST_Response(
				[
					'auth_url' => $auth_url,
				],
				200
			)
		);
	}

	/**
	 * Handle OAuth callback
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_callback( $request ) {
		$provider_id = $request->get_param( 'provider' );
		$code        = $request->get_param( 'code' );
		$state       = $request->get_param( 'state' );
		$error       = $request->get_param( 'error' );

		if ( $error ) {
			$error_description = $request->get_param( 'error_description' );
			return $this->redirect_with_error( $error . ( $error_description ? ': ' . $error_description : '' ) );
		}

		if ( empty( $code ) ) {
			return $this->redirect_with_error( __( 'Authorization code is missing', '4wp-account' ) );
		}

		$auth_manager = \ForWP\Account\Auth\AuthManager::get_instance();
		$provider     = $auth_manager->get_provider( $provider_id );

		if ( ! $provider ) {
			return $this->redirect_with_error( __( 'Invalid provider', '4wp-account' ) );
		}

		$state_check = OAuthState::verify( $provider_id, is_string( $state ) ? $state : '' );
		if ( is_wp_error( $state_check ) ) {
			return $this->redirect_with_error( $state_check->get_error_message() );
		}

		$result = $provider->handle_callback( $code, $state );

		if ( is_wp_error( $result ) ) {
			return $this->redirect_with_error( $result->get_error_message() );
		}

		$redirect_url = apply_filters( 'forwp_account_redirect_url', home_url( '/' ) );
		$redirect_url = OAuthRedirect::consume_cookie( $redirect_url );

		return OAuthRedirect::rest_response( $redirect_url );
	}

	/**
	 * Redirect with error message
	 *
	 * @param string $error_message Error message.
	 * @return \WP_REST_Response
	 */
	private function redirect_with_error( $error_message ) {
		$redirect_url = add_query_arg(
			array(
				'forwp_account_error' => rawurlencode( $error_message ),
			),
			\ForWP\Account\Account\AccountMenu::get_account_page_url()
		);

		return OAuthRedirect::rest_response( $redirect_url );
	}

	/**
	 * OAuth auth URL JSON must not be cached (stale state breaks login).
	 *
	 * @param \WP_REST_Response $response REST response.
	 * @return \WP_REST_Response
	 */
	private static function nocache_response( \WP_REST_Response $response ): \WP_REST_Response {
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Expires', '0' );

		return $response;
	}
}
