<?php
/**
 * WooCommerce Integration
 *
 * @package ForWP\Account\Integrations
 */

namespace ForWP\Account\Integrations;

use ForWP\Account\Auth\ProviderSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce integration class
 */
class WooCommerce {

	/**
	 * Plugin instance
	 *
	 * @var WooCommerce
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return WooCommerce
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
		// Check if WooCommerce is active and integration is enabled
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( get_option( 'forwp_account_woocommerce_integration', '0' ) !== '1' ) {
			return;
		}

		// Add social login buttons to WooCommerce login form
		add_action( 'woocommerce_login_form_end', [ $this, 'render_login_buttons' ], 10 );

		// Add social login buttons to WooCommerce registration form
		add_action( 'woocommerce_register_form_end', [ $this, 'render_register_buttons' ], 10 );
	}

	/**
	 * Render social login buttons on login form
	 */
	public function render_login_buttons() {
		$this->render_buttons();
	}

	/**
	 * Render social login buttons on registration form
	 */
	public function render_register_buttons() {
		$this->render_buttons();
	}

	/**
	 * Render social login buttons
	 */
	private function render_buttons() {
		// Get enabled providers
		$providers = $this->get_enabled_providers();
		
		if ( empty( $providers ) ) {
			return;
		}

		// Ensure scripts are enqueued
		$this->enqueue_scripts();

		?>
		<div class="forwp-account-woocommerce-signin" style="margin-top: 20px; margin-bottom: 20px;">
			<?php foreach ( $providers as $provider_key => $provider_name ) : ?>
				<button 
					type="button" 
					class="forwp-account-signin-btn forwp-account-signin-btn-<?php echo esc_attr( $provider_key ); ?> button" 
					data-provider="<?php echo esc_attr( $provider_key ); ?>"
					style="width: 100%; margin-bottom: 10px;"
				>
					<?php echo esc_html( $provider_name ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Get enabled providers
	 *
	 * @return array
	 */
	private function get_enabled_providers() {
		return ProviderSettings::get_enabled_for_display();
	}

	/**
	 * Enqueue scripts
	 */
	private function enqueue_scripts() {
		if ( ! function_exists( 'wp_enqueue_script' ) ) {
			return;
		}

		// Enqueue auth script if not already enqueued
		if ( ! wp_script_is( 'forwp-account-signin', 'enqueued' ) ) {
			wp_enqueue_script(
				'forwp-account-signin',
				FORWP_ACCOUNT_PLUGIN_URL . 'assets/js/auth.js',
				[ 'jquery' ],
				FORWP_ACCOUNT_VERSION,
				true
			);

			wp_localize_script(
				'forwp-account-signin',
				'forwpAccountSignin',
				[
					'apiUrl' => rest_url( 'forwp-account/v1/' ),
					'nonce'   => wp_create_nonce( 'forwp_account_nonce' ),
				]
			);
		}

		// Enqueue styles if not already enqueued
		if ( ! wp_style_is( 'forwp-account-signin', 'enqueued' ) ) {
			wp_enqueue_style(
				'forwp-account-signin',
				FORWP_ACCOUNT_PLUGIN_URL . 'assets/css/auth.css',
				[],
				FORWP_ACCOUNT_VERSION
			);
		}
	}
}

