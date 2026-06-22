<?php
/**
 * Admin tab: Account Menu — separate configs for header dropdown and account page.
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

use ForWP\Account\Account\AccountMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account menu configuration panel.
 */
class AccountMenuTab {

	/**
	 * Render account menu settings.
	 */
	public static function render(): void {
		self::render_context_editor(
			AccountMenu::CONTEXT_HEADER,
			__( 'Header dropdown menu', '4wp-account' ),
			__( 'Items for the account icon dropdown in the site header.', '4wp-account' ),
			array(
				'<code>forwp/account-menu</code>',
				'<code>[forwp_account_menu]</code>',
			)
		);

		self::render_context_editor(
			AccountMenu::CONTEXT_PAGE,
			__( 'Account page sidebar', '4wp-account' ),
			__( 'Left navigation on the logged-in account page.', '4wp-account' ),
			array(
				'<code>forwp/account</code>',
				'<code>[forwp_account]</code>',
			)
		);
	}

	/**
	 * @param string   $context     header|page.
	 * @param string   $title       Panel title.
	 * @param string   $description Panel description.
	 * @param string[] $shortcodes  Shortcode hints.
	 */
	private static function render_context_editor( string $context, string $title, string $description, array $shortcodes ): void {
		$sections = AccountMenu::get_sections( $context );
		$prefix   = self::field_prefix( $context );
		?>
		<div class="forwp-account-menu-context forwp-account-menu-context--<?php echo esc_attr( $context ); ?>">
			<h3><?php echo esc_html( $title ); ?></h3>
			<p class="description"><?php echo esc_html( $description ); ?></p>

			<div class="forwp-account-notice forwp-account-notice--info">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: block/shortcode list */
							__( 'Used by: %s', '4wp-account' ),
							implode( ' · ', $shortcodes )
						)
					);
					?>
				</p>
			</div>

			<h4><?php esc_html_e( 'Cabinet sections', '4wp-account' ); ?></h4>
			<table class="widefat striped forwp-account-menu-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Label', '4wp-account' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Section', '4wp-account' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Order', '4wp-account' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Show', '4wp-account' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $sections as $key => $section ) : ?>
						<?php
						$plugin_file   = isset( $section['plugin'] ) ? (string) $section['plugin'] : '';
						$available     = $plugin_file === '' || AccountMenu::is_plugin_active( $plugin_file );
						$default_label = (string) ( AccountMenu::get_default_sections()[ $key ]['label'] ?? $key );
						$name          = $prefix . '_sections[' . $key . ']';
						?>
						<tr>
							<td>
								<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( (string) ( $section['label'] ?? $key ) ); ?>" placeholder="<?php echo esc_attr( $default_label ); ?>">
							</td>
							<td>
								<strong><?php echo esc_html( $default_label ); ?></strong>
								<?php if ( $plugin_file && ! $available ) : ?>
									<br><span class="forwp-account-badge forwp-account-badge--muted"><?php esc_html_e( 'Plugin not active', '4wp-account' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<input type="number" class="small-text" name="<?php echo esc_attr( $name ); ?>[order]" value="<?php echo esc_attr( (string) ( $section['order'] ?? 0 ) ); ?>" min="0" step="10">
							</td>
							<td>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( ! empty( $section['enabled'] ) ); ?>>
									<?php esc_html_e( 'Show', '4wp-account' ); ?>
								</label>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php self::render_custom_links_section( $context ); ?>
		</div>
		<?php
	}

	/**
	 * @param string $context header|page.
	 */
	private static function field_prefix( string $context ): string {
		return AccountMenu::CONTEXT_PAGE === $context
			? 'forwp_account_page_menu'
			: 'forwp_account_header_menu';
	}

	/**
	 * @param string $context header|page.
	 */
	private static function links_field_name( string $context ): string {
		return AccountMenu::CONTEXT_PAGE === $context
			? 'forwp_account_page_custom_links'
			: 'forwp_account_header_custom_links';
	}

	/**
	 * @param string $context header|page.
	 */
	private static function render_custom_links_section( string $context ): void {
		$links     = AccountMenu::get_stored_custom_links( $context );
		$table_id  = 'forwp-account-custom-links-' . $context;
		$add_id    = 'forwp-account-add-custom-link-' . $context;
		$tpl_id    = 'forwp-account-custom-link-template-' . $context;
		$field     = self::links_field_name( $context );
		?>
		<h4><?php esc_html_e( 'Custom links', '4wp-account' ); ?></h4>
		<p class="description"><?php esc_html_e( 'Additional links merged with cabinet sections above.', '4wp-account' ); ?></p>

		<table class="widefat striped forwp-account-menu-table forwp-account-custom-links-table" id="<?php echo esc_attr( $table_id ); ?>">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Label', '4wp-account' ); ?></th>
					<th scope="col"><?php esc_html_e( 'URL', '4wp-account' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Order', '4wp-account' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Show', '4wp-account' ); ?></th>
					<th scope="col"><?php esc_html_e( 'New tab', '4wp-account' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Delete', '4wp-account' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $links === array() ) {
					self::render_custom_link_row( $field, 'new_0', array() );
				} else {
					foreach ( $links as $index => $link ) {
						if ( ! is_array( $link ) ) {
							continue;
						}
						self::render_custom_link_row( $field, (string) ( $link['id'] ?? 'row_' . $index ), $link );
					}
				}
				?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button forwp-account-add-custom-link" id="<?php echo esc_attr( $add_id ); ?>" data-table="<?php echo esc_attr( $table_id ); ?>" data-template="<?php echo esc_attr( $tpl_id ); ?>">
				<?php esc_html_e( 'Add link', '4wp-account' ); ?>
			</button>
		</p>

		<template id="<?php echo esc_attr( $tpl_id ); ?>">
			<?php self::render_custom_link_row( $field, '__INDEX__', array(), true ); ?>
		</template>
		<?php
	}

	/**
	 * @param string               $field_name POST array base name.
	 * @param string               $row_key    Row key.
	 * @param array<string, mixed> $link       Saved link row.
	 * @param bool                 $template   JS template fragment.
	 */
	private static function render_custom_link_row( string $field_name, string $row_key, array $link, bool $template = false ): void {
		$id      = $template ? '__INDEX__' : $row_key;
		$label   = (string) ( $link['label'] ?? '' );
		$url     = (string) ( $link['url'] ?? '' );
		$order   = isset( $link['order'] ) ? (int) $link['order'] : 50;
		$enabled = ! array_key_exists( 'enabled', $link ) || ! empty( $link['enabled'] );
		$target  = ( ! empty( $link['target'] ) && '_blank' === $link['target'] );
		$name    = $field_name . '[' . $id . ']';
		?>
		<tr>
			<td>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[id]" value="<?php echo esc_attr( $id ); ?>">
				<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Documentation', '4wp-account' ); ?>">
			</td>
			<td>
				<input type="text" class="large-text code" name="<?php echo esc_attr( $name ); ?>[url]" value="<?php echo esc_attr( $url ); ?>" placeholder="/docs/">
			</td>
			<td>
				<input type="number" class="small-text" name="<?php echo esc_attr( $name ); ?>[order]" value="<?php echo esc_attr( (string) $order ); ?>" min="0" step="1">
			</td>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( $enabled ); ?>>
					<?php esc_html_e( 'Show', '4wp-account' ); ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[target]" value="_blank" <?php checked( $target ); ?>>
					<?php esc_html_e( 'New tab', '4wp-account' ); ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[_delete]" value="1">
					<?php esc_html_e( 'Delete', '4wp-account' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param array<string, mixed> $post Raw POST.
	 */
	public static function save( array $post ): void {
		self::save_context( $post, AccountMenu::CONTEXT_HEADER );
		self::save_context( $post, AccountMenu::CONTEXT_PAGE );
	}

	/**
	 * @param array<string, mixed> $post    Raw POST.
	 * @param string               $context header|page.
	 */
	private static function save_context( array $post, string $context ): void {
		$sections_key = self::field_prefix( $context ) . '_sections';
		$links_key    = self::links_field_name( $context );

		if ( isset( $post[ $sections_key ] ) && is_array( $post[ $sections_key ] ) ) {
			AccountMenu::save_sections( $post[ $sections_key ], $context );
		}

		if ( isset( $post[ $links_key ] ) && is_array( $post[ $links_key ] ) ) {
			AccountMenu::save_custom_links( $post[ $links_key ], $context );
		} elseif ( array_key_exists( $links_key, $post ) ) {
			AccountMenu::save_custom_links( array(), $context );
		}
	}
}
