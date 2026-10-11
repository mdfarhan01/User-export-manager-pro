<?php
/**
 * Activation, upgrades and storage setup.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Install' ) ) {

	/**
	 * Creates tables and the protected exports folder.
	 */
	class UEMPro_Install {

		const DB_VERSION        = '5';
		const DB_VERSION_OPTION = 'uempro_db_version';
		const EXPORT_DIRNAME    = 'uempro-exports';

		/**
		 * Run the installer again whenever the stored schema version is outdated
		 * (covers updates that do not trigger the activation hook).
		 */
		public static function maybe_upgrade() {
			if ( self::DB_VERSION !== get_option( self::DB_VERSION_OPTION ) ) {
				self::install();
			}
		}

		/**
		 * Create/upgrade everything the plugin needs.
		 */
		public static function install() {
			self::create_tables();
			self::ensure_export_dir();

			if ( false === get_option( UEMPRO_OPTION_KEY ) ) {
				add_option( UEMPRO_OPTION_KEY, UEMPro_Settings::defaults(), '', false );
			}

			update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
			UEMPro_Cron::schedule();
		}

		/**
		 * Templates table name.
		 *
		 * @return string
		 */
		public static function templates_table() {
			global $wpdb;
			return $wpdb->prefix . 'uempro_templates';
		}

		/**
		 * History table name.
		 *
		 * @return string
		 */
		public static function history_table() {
			global $wpdb;
			return $wpdb->prefix . 'uempro_history';
		}

		/**
		 * Create or update the custom tables with dbDelta.
		 */
		public static function create_tables() {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$charset_collate = $wpdb->get_charset_collate();
			$templates       = self::templates_table();
			$history         = self::history_table();

			dbDelta(
				"CREATE TABLE {$templates} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(191) NOT NULL,
				description text NULL,
				fields longtext NOT NULL,
				filters longtext NOT NULL,
				format varchar(10) NOT NULL DEFAULT 'csv',
				filename varchar(191) NOT NULL DEFAULT 'users-export',
				created_by bigint(20) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id)
				) {$charset_collate};"
			);

			dbDelta(
				"CREATE TABLE {$history} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				entry_type varchar(10) NOT NULL DEFAULT 'export',
				template_id bigint(20) unsigned NOT NULL DEFAULT 0,
				template_name varchar(191) NOT NULL DEFAULT '',
				admin_id bigint(20) unsigned NOT NULL DEFAULT 0,
				user_count int(10) unsigned NOT NULL DEFAULT 0,
				field_count int(10) unsigned NOT NULL DEFAULT 0,
				file_name varchar(191) NOT NULL,
				file_size bigint(20) unsigned NOT NULL DEFAULT 0,
				format varchar(10) NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY created_at (created_at)
				) {$charset_collate};"
			);
		}

		/**
		 * Absolute path of the exports folder (no trailing slash).
		 *
		 * @return string
		 */
		public static function export_dir() {
			$upload = wp_upload_dir( null, false );
			return trailingslashit( $upload['basedir'] ) . self::EXPORT_DIRNAME;
		}

		/**
		 * Create the exports folder and block direct web access to it.
		 * Files are only ever served through the authenticated download handler.
		 *
		 * @return bool
		 */
		public static function ensure_export_dir() {
			$dir = self::export_dir();
			if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
				return false;
			}

			$guards = array(
				'.htaccess'  => "# Exports are served through WordPress only.\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder deny,allow\n\tDeny from all\n</IfModule>\nOptions -Indexes\n",
				'index.php'  => "<?php\n// Silence is golden.\n",
				'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n",
			);

			foreach ( $guards as $name => $contents ) {
				$path = $dir . '/' . $name;
				if ( ! file_exists( $path ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writing a static guard file inside our own uploads sub-folder.
					file_put_contents( $path, $contents );
				}
			}

			return true;
		}
	}
}
