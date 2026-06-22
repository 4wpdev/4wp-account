<?php
/**
 * Gutenberg blocks: account page, header link, auth buttons.
 *
 * @package ForWP\Account\Blocks
 */

namespace ForWP\Account\Blocks;

use ForWP\Account\Account\AccountMenuRenderer;
use ForWP\Account\Account\AccountPage;
use ForWP\Account\Account\AccountRenderer;
use ForWP\Account\Auth\ProviderSettings;
use ForWP\Account\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers forwp/account* dynamic blocks (block.json + PHP render).
 */
class AccountBlocks {

	/**
	 * Block names managed by this class.
	 *
	 * @var string[]
	 */
	private const BLOCK_NAMES = array(
		'forwp/account',
		'forwp/account-link',
		'forwp/account-menu',
		'forwp/auth-buttons',
	);

	/**
	 * Bootstrap hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_blocks' ) );
		add_filter( 'allowed_block_types_all', array( self::class, 'ensure_blocks_allowed' ), 99, 2 );
		add_filter( 'block_type_metadata', array( self::class, 'ensure_inserter_visible' ) );
		add_filter( 'block_core_navigation_listable_blocks', array( self::class, 'allow_in_navigation' ) );
	}

	/**
	 * Keep blocks available when themes/plugins filter the allowed list.
	 *
	 * @param bool|string[] $allowed_block_types Allowed block types.
	 * @param object        $block_editor_context Editor context.
	 * @return bool|string[]
	 */
	public static function ensure_blocks_allowed( $allowed_block_types, $block_editor_context ) {
		unset( $block_editor_context );

		if ( true === $allowed_block_types ) {
			return $allowed_block_types;
		}

		if ( ! is_array( $allowed_block_types ) ) {
			$allowed_block_types = array();
		}

		foreach ( self::BLOCK_NAMES as $block_name ) {
			if ( ! in_array( $block_name, $allowed_block_types, true ) ) {
				$allowed_block_types[] = $block_name;
			}
		}

		return $allowed_block_types;
	}

	/**
	 * Ensure custom blocks stay visible in the block inserter.
	 *
	 * @param array<string, mixed> $metadata Block metadata.
	 * @return array<string, mixed>
	 */
	public static function ensure_inserter_visible( array $metadata ): array {
		if ( empty( $metadata['name'] ) || ! in_array( $metadata['name'], self::BLOCK_NAMES, true ) ) {
			return $metadata;
		}

		if ( ! isset( $metadata['supports'] ) || ! is_array( $metadata['supports'] ) ) {
			$metadata['supports'] = array();
		}

		$metadata['supports']['inserter'] = true;

		return $metadata;
	}

	/**
	 * Allow account link inside core/navigation (header menus).
	 *
	 * @param string[] $blocks Blocks that need a list-item wrapper in navigation.
	 * @return string[]
	 */
	public static function allow_in_navigation( array $blocks ): array {
		foreach ( array( 'forwp/account-link', 'forwp/account-menu' ) as $block_name ) {
			if ( ! in_array( $block_name, $blocks, true ) ) {
				$blocks[] = $block_name;
			}
		}

		return $blocks;
	}

	/**
	 * Register dynamic blocks from block.json.
	 */
	public static function register_blocks(): void {
		$blocks_dir = FORWP_ACCOUNT_PLUGIN_DIR . 'assets/blocks/';

		register_block_type(
			$blocks_dir . 'account',
			array(
				'render_callback' => array( self::class, 'render_account' ),
			)
		);

		register_block_type(
			$blocks_dir . 'account-link',
			array(
				'render_callback' => array( self::class, 'render_account_link' ),
			)
		);

		register_block_type(
			$blocks_dir . 'account-menu',
			array(
				'render_callback' => array( self::class, 'render_account_menu' ),
			)
		);

		register_block_type(
			$blocks_dir . 'auth-buttons',
			array(
				'render_callback' => array( self::class, 'render_auth_buttons' ),
			)
		);
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner content.
	 * @return string
	 */
	public static function render_account( array $attributes, string $content ): string {
		unset( $attributes, $content );

		AccountPage::ensure_auth_assets();
		AccountPage::enqueue_assets();

		return AccountRenderer::render();
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner content.
	 * @return string
	 */
	public static function render_account_link( array $attributes, string $content ): string {
		unset( $content );

		$label       = isset( $attributes['label'] ) ? trim( (string) $attributes['label'] ) : '';
		$label_guest = isset( $attributes['labelGuest'] ) ? trim( (string) $attributes['labelGuest'] ) : '';
		$label_user  = isset( $attributes['labelUser'] ) ? trim( (string) $attributes['labelUser'] ) : '';

		// Icon-only header block → cabinet dropdown (same as forwp/account-menu).
		if ( $label === '' && $label_guest === '' && $label_user === '' ) {
			return AccountMenuRenderer::render();
		}

		AccountPage::enqueue_assets();

		$atts = array(
			'label'       => $label,
			'label_guest' => $label_guest,
			'label_user'  => $label_user,
			'class'       => isset( $attributes['className'] ) ? (string) $attributes['className'] : '',
		);

		return AccountRenderer::render_nav_link( $atts );
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner content.
	 * @return string
	 */
	public static function render_account_menu( array $attributes, string $content ): string {
		unset( $content );

		return AccountMenuRenderer::render(
			array(
				'account_url' => isset( $attributes['accountUrl'] ) ? (string) $attributes['accountUrl'] : '',
			)
		);
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner content.
	 * @return string
	 */
	public static function render_auth_buttons( array $attributes, string $content ): string {
		unset( $content );

		if ( is_user_logged_in() ) {
			return '';
		}

		AccountPage::ensure_auth_assets();

		$providers_attr = isset( $attributes['providers'] ) ? (string) $attributes['providers'] : 'auto';
		$provider_ids   = ProviderSettings::parse_provider_list( $providers_attr );

		if ( empty( $provider_ids ) ) {
			return '';
		}

		return Shortcodes::render_buttons_markup( $provider_ids );
	}
}
