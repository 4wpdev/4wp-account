<?php
/**
 * @package ForWP\Auth
 */

declare( strict_types=1 );

namespace ForWP\Auth\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

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
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'shortcode_atts' )->alias(
			static function ( $pairs, $atts ) {
				return array_merge( $pairs, is_array( $atts ) ? $atts : array() );
			}
		);
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
		$html = \ForWP\Auth\Shortcodes::render_login_button(
			array(
				'provider' => 'gmail',
				'text'     => 'Sign in with Gmail',
			)
		);

		$this->assertStringContainsString( 'forwp-auth-btn', $html );
		$this->assertStringContainsString( 'data-provider="gmail"', $html );
	}
}
