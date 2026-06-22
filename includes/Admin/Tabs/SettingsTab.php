<?php
/**
 * Admin tab: Settings (Pages, Blocks, Account Menu).
 *
 * @package ForWP\Account\Admin\Tabs
 */

namespace ForWP\Account\Admin\Tabs;

use ForWP\Account\Account\AccountMenu;
use ForWP\Account\Admin\Tabs\AccountMenuTab;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account plugin settings.
 */
class SettingsTab {

	public const SUB_PAGES       = 'pages';
	public const SUB_BLOCKS      = 'blocks';
	public const SUB_ACCOUNT_MENU = 'account-menu';

	/**
	 * @return array<string, string>
	 */
	public static function get_subtabs(): array {
		return array(
			self::SUB_PAGES        => __( 'Pages', '4wp-account' ),
			self::SUB_ACCOUNT_MENU => __( 'Account Menu', '4wp-account' ),
			self::SUB_BLOCKS       => __( 'Blocks', '4wp-account' ),
		);
	}

	/**
	 * @param string $sub Current sub-tab.
	 */
	public static function render( string $sub ): void {
		$sub = array_key_exists( $sub, self::get_subtabs() ) ? $sub : self::SUB_PAGES;

		?>
		<div class="forwp-account-panel">
			<h2><?php esc_html_e( 'Settings', '4wp-account' ); ?></h2>

			<nav class="nav-tab-wrapper forwp-account-subtabs" aria-label="<?php esc_attr_e( 'Settings sections', '4wp-account' ); ?>">
				<?php foreach ( self::get_subtabs() as $sub_key => $label ) : ?>
					<a href="<?php echo esc_url( self::subtab_url( $sub_key ) ); ?>" class="nav-tab<?php echo $sub === $sub_key ? ' nav-tab-active' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<input type="hidden" name="forwp_account_settings_sub" value="<?php echo esc_attr( $sub ); ?>">

			<?php
			if ( self::SUB_BLOCKS === $sub ) {
				self::render_blocks_section();
			} elseif ( self::SUB_ACCOUNT_MENU === $sub ) {
				AccountMenuTab::render();
			} else {
				self::render_pages_section();
			}
			?>
		</div>
		<?php
	}

	/**
	 * @param string $sub Sub-tab key.
	 */
	private static function subtab_url( string $sub ): string {
		return add_query_arg(
			array(
				'page'    => 'forwp-account',
				'tab'     => 'settings',
				'section' => $sub,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Pages: account page selector.
	 */
	private static function render_pages_section(): void {
		$page_id  = AccountMenu::get_account_page_id();
		$pages    = get_pages(
			array(
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC',
			)
		);
		$page_url = AccountMenu::get_account_page_url();
		?>
		<h3><?php esc_html_e( 'Account page', '4wp-account' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Primary page that hosts the Account block and navigation. Falls back to WooCommerce My Account when not set.', '4wp-account' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="forwp_account_page_id"><?php esc_html_e( 'Page', '4wp-account' ); ?></label>
				</th>
				<td>
					<select name="forwp_account_page_id" id="forwp_account_page_id">
						<option value="0"><?php esc_html_e( '— Auto (WooCommerce My Account) —', '4wp-account' ); ?></option>
						<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $page_id, (int) $page->ID ); ?>>
								<?php echo esc_html( $page->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php if ( $page_url ) : ?>
						<p class="description">
							<?php esc_html_e( 'Preview:', '4wp-account' ); ?>
							<a href="<?php echo esc_url( $page_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $page_url ); ?></a>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Blocks: Auth + Account block documentation.
	 */
	private static function render_blocks_section(): void {
		?>
		<h3><?php esc_html_e( 'Auth block', '4wp-account' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Sign-in buttons and OAuth flows.', '4wp-account' ); ?>
		</p>
		<div class="forwp-account-notice forwp-account-notice--info">
			<p><code>forwp/auth-buttons</code> — <?php esc_html_e( 'Gutenberg block or', '4wp-account' ); ?> <code>[forwp_account_signin_buttons providers="gmail,github"]</code></p>
		</div>

		<h3><?php esc_html_e( 'Account block', '4wp-account' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'One page for sign-in and the logged-in cabinet (left menu + content, like WooCommerce My Account).', '4wp-account' ); ?>
		</p>
		<div class="forwp-account-notice forwp-account-notice--info">
			<p><?php esc_html_e( 'In Site Editor or block inserter, search “4WP Account” (category: Widgets).', '4wp-account' ); ?></p>
			<p><code>forwp/account</code> — <?php esc_html_e( 'full page: Google/GitHub sign-in or account sections', '4wp-account' ); ?></p>
			<p><code>forwp/account-link</code> — <?php esc_html_e( 'header/menu link with icon (guest vs signed-in)', '4wp-account' ); ?></p>
			<p><code>forwp/account-menu</code> — <?php esc_html_e( 'user icon + classic cabinet dropdown (recommended for header)', '4wp-account' ); ?></p>
			<p><code>[forwp_account]</code> / <code>[forwp_account_link]</code> / <code>[forwp_account_menu]</code> — <?php esc_html_e( 'shortcode equivalents', '4wp-account' ); ?></p>
			<p><?php esc_html_e( 'On the selected account page, [woocommerce_my_account] is replaced automatically. Menu items linking to the account URL also get icons.', '4wp-account' ); ?></p>
			<p>
				<a href="<?php echo esc_url( self::subtab_url( self::SUB_ACCOUNT_MENU ) ); ?>">
					<?php esc_html_e( 'Configure account menu sections →', '4wp-account' ); ?>
				</a>
			</p>
		</div>

		<?php self::render_notifications_integration_section(); ?>
		<?php
	}

	/**
	 * Document 4WP Notifications blocks and cabinet sections when the plugin is active.
	 */
	private static function render_notifications_integration_section(): void {
		if ( ! AccountMenu::is_notifications_active() ) {
			return;
		}

		$notif_page_id    = (int) get_option( 'forwp_notifications_page_id', 0 );
		$favorites_page_id = (int) get_option( 'forwp_favorites_page_id', 0 );
		$settings_url     = admin_url( 'admin.php?page=4wp-notifications&tab=display' );
		?>
		<h3><?php esc_html_e( '4WP Notifications integration', '4wp-account' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'When 4WP Notifications is active, Favorites and Notifications sections in the account menu are powered by that plugin. You can show them inside the cabinet (?section=…) or link menu items to dedicated pages.', '4wp-account' ); ?>
		</p>
		<div class="forwp-account-notice forwp-account-notice--info">
			<p><strong><?php esc_html_e( 'Account menu sections', '4wp-account' ); ?></strong></p>
			<ul>
				<li><code>?section=favorites</code> — <?php esc_html_e( 'grouped favorites list', '4wp-account' ); ?></li>
				<li><code>?section=notifications</code> — <?php esc_html_e( 'notification inbox', '4wp-account' ); ?></li>
			</ul>
			<p><strong><?php esc_html_e( 'Header / Site Editor blocks', '4wp-account' ); ?></strong></p>
			<ul>
				<li><code>forwp/notifications-bell</code> — <?php esc_html_e( 'bell dropdown', '4wp-account' ); ?></li>
				<li><code>forwp/favorites-menu</code> — <?php esc_html_e( 'favorites dropdown', '4wp-account' ); ?></li>
				<li><code>forwp/account-menu</code> — <?php esc_html_e( 'account cabinet dropdown', '4wp-account' ); ?></li>
				<li><code>forwp/favorite-button</code> — <?php esc_html_e( 'heart toggle in Query Loop', '4wp-account' ); ?></li>
				<li><code>forwp/favorites-list</code> / <code>forwp/notifications</code> — <?php esc_html_e( 'full page lists', '4wp-account' ); ?></li>
			</ul>
			<p>
				<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( '4WP Notifications display settings', '4wp-account' ); ?></a>
				<?php if ( $notif_page_id > 0 && get_edit_post_link( $notif_page_id, 'raw' ) ) : ?>
					· <a href="<?php echo esc_url( get_edit_post_link( $notif_page_id, 'raw' ) ); ?>"><?php esc_html_e( 'Edit notifications page', '4wp-account' ); ?></a>
				<?php endif; ?>
				<?php if ( $favorites_page_id > 0 && get_edit_post_link( $favorites_page_id, 'raw' ) ) : ?>
					· <a href="<?php echo esc_url( get_edit_post_link( $favorites_page_id, 'raw' ) ); ?>"><?php esc_html_e( 'Edit favorites page', '4wp-account' ); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $post Raw POST.
	 */
	public static function save( array $post ): void {
		if ( isset( $post['forwp_account_page_id'] ) ) {
			AccountMenu::set_account_page_id( absint( $post['forwp_account_page_id'] ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sub = isset( $post['forwp_account_settings_sub'] ) ? sanitize_key( (string) $post['forwp_account_settings_sub'] ) : self::SUB_PAGES;

		if ( self::SUB_ACCOUNT_MENU === $sub ) {
			AccountMenuTab::save( $post );
		}
	}
}
