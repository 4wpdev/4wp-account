<?php
/**
 * @package ForWP\Account
 */

declare( strict_types=1 );

namespace ForWP\Account\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use ForWP\Account\Shortcodes;

/**
 * Shortcode registration smoke tests.
 */
class ShortcodesTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->returnArg( 1 );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->returnArg( 1 );
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'shortcode_atts' )->alias(
			static function ( $pairs, $atts ) {
				return array_merge( $pairs, is_array( $atts ) ? $atts : array() );
			}
		);
		Functions\when( 'get_option' )->justReturn( '1' );
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_login_shortcode_renders_button(): void {
		$html = Shortcodes::render_login_button(
			array(
				'provider' => 'gmail',
				'text'     => 'Sign in with Google',
			)
		);

		$this->assertStringContainsString( 'forwp-account-signin-btn', $html );
		$this->assertStringContainsString( 'data-provider="gmail"', $html );
	}
}
