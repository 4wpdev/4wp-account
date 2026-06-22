<?php
/**
 * Simple autoloader for plugin classes
 *
 * @package ForWP\Account
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register( function( $class ) {
	$prefix = 'ForWP\\Account\\';
	$base_dir = FORWP_ACCOUNT_PLUGIN_DIR . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );





