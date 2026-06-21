<?php
/**
 * PHPUnit bootstrap for 4wp-account.
 *
 * @package ForWP\Auth
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'FORWP_ACCOUNT_PATH' ) ) {
	define( 'FORWP_ACCOUNT_PATH', dirname( __DIR__ ) . '/' );
}

$forwp_account_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! is_readable( $forwp_account_autoload ) ) {
	die( "Run: composer install (requires 4wp-dev-toolkit path repo).\n" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

require_once $forwp_account_autoload;
