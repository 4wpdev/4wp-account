<?php
/**
 * Database migrations.
 *
 * @package ForWP\Account\Storage
 */

namespace ForWP\Account\Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin schema and one-time upgrades.
 */
class Migrations {

	private const OPTION_DB_VERSION = 'forwp_account_db_version';
	private const LEGACY_MIGRATED   = 'forwp_account_legacy_migrated_v1';

	/**
	 * Run migrations.
	 */
	public static function run(): void {
		self::maybe_migrate_legacy_identifiers();
		self::migrate_header_account_block();

		$version = get_option( self::OPTION_DB_VERSION, '0' );

		if ( version_compare( $version, FORWP_ACCOUNT_VERSION, '<' ) ) {
			self::create_tables();
			self::migrate_provider_flags( $version );
			update_option( self::OPTION_DB_VERSION, FORWP_ACCOUNT_VERSION, false );
		}
	}

	/**
	 * Copy pre-1.0 forwp_auth_* options and rename OAuth table once.
	 */
	private static function maybe_migrate_legacy_identifiers(): void {
		if ( get_option( self::LEGACY_MIGRATED ) ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$legacy_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				'forwp_auth_%'
			),
			ARRAY_A
		);

		if ( is_array( $legacy_options ) ) {
			foreach ( $legacy_options as $row ) {
				$new_name = str_replace( 'forwp_auth_', 'forwp_account_', (string) $row['option_name'] );
				if ( get_option( $new_name, null ) === null ) {
					update_option( $new_name, maybe_unserialize( $row['option_value'] ), false );
				}
				delete_option( (string) $row['option_name'] );
			}
		}

		$legacy_table = $wpdb->prefix . 'forwp_auth_users';
		$new_table    = $wpdb->prefix . 'forwp_account_oauth';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$legacy_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$new_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $new_table ) );

		if ( $legacy_exists === $legacy_table && $new_exists !== $new_table ) {
			$sql = $wpdb->prepare( 'RENAME TABLE %i TO %i', $legacy_table, $new_table );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql from prepare() above.
		}

		$legacy_meta_keys = array(
			'forwp_auth_provider'     => 'forwp_account_provider',
			'forwp_auth_provider_id'  => 'forwp_account_provider_id',
			'forwp_auth_avatar'       => 'forwp_account_avatar',
		);

		foreach ( $legacy_meta_keys as $old_key => $new_key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->usermeta} SET meta_key = %s WHERE meta_key = %s",
					$new_key,
					$old_key
				)
			);
		}

		update_option( self::LEGACY_MIGRATED, 1, false );
	}

	/**
	 * Replace legacy forwp/account-link with forwp/account-menu in FSE templates.
	 */
	private static function migrate_header_account_block(): void {
		if ( get_option( 'forwp_account_header_block_migrated_v1' ) ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$posts = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts}
			WHERE post_type IN ('wp_template_part', 'wp_template')
			AND post_status IN ('publish', 'draft')
			AND post_content LIKE '%forwp/account-link%'"
		);

		if ( is_array( $posts ) ) {
			foreach ( $posts as $post ) {
				$content = (string) $post->post_content;
				$updated = preg_replace(
					'/<!-- wp:forwp\/account-link\b[^\/]*\/-->/',
					'<!-- wp:forwp/account-menu /-->',
					$content
				);

				if ( is_string( $updated ) && $updated !== $content ) {
					wp_update_post(
						array(
							'ID'           => (int) $post->ID,
							'post_content' => $updated,
						)
					);
				}
			}
		}

		update_option( 'forwp_account_header_block_migrated_v1', 1, false );
	}

	/**
	 * Normalize provider enable flags after settings logic changes.
	 *
	 * @param string $from_version Previously stored plugin/db version.
	 */
	private static function migrate_provider_flags( string $from_version ): void {
		if ( version_compare( $from_version, '0.9.3', '>=' ) ) {
			return;
		}

		update_option( 'forwp_account_provider_enabled_facebook', '0', false );
		update_option( 'forwp_account_provider_enabled_tiktok', '0', false );
	}

	/**
	 * Create database tables.
	 */
	private static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'forwp_account_oauth';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			provider varchar(50) NOT NULL,
			provider_user_id varchar(255) NOT NULL,
			access_token text,
			refresh_token text,
			token_expires datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY provider_user (provider, provider_user_id),
			KEY user_id (user_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
