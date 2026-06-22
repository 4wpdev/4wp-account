<?php
/**
 * Editor script dependencies for the account block.
 *
 * @package ForWP\Account\Blocks
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-block-editor',
		'wp-i18n',
	),
	'version'      => FORWP_ACCOUNT_VERSION,
);
