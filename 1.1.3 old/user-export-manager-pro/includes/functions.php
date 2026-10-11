<?php
/**
 * Global procedural helpers.
 * Every function name is prefixed with uempro_ to avoid any collision with
 * other plugins' global function names.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'uempro_get_settings' ) ) {
	/**
	 * Merged (saved + defaults) settings array.
	 *
	 * @return array
	 */
	function uempro_get_settings() {
		return UEMPro_Settings::get();
	}
}

if ( ! function_exists( 'uempro_get_option' ) ) {
	/**
	 * Read a single setting.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Fallback value.
	 * @return mixed
	 */
	function uempro_get_option( $key, $fallback = '' ) {
		$value = UEMPro_Settings::get( $key );
		return null === $value ? $fallback : $value;
	}
}

if ( ! function_exists( 'uempro_capability' ) ) {
	/**
	 * Capability required to use the exporter.
	 *
	 * @return string
	 */
	function uempro_capability() {
		/**
		 * Filters the capability required to use User Export Manager Pro.
		 *
		 * @param string $capability Default 'list_users'.
		 */
		return (string) apply_filters( 'uempro_capability', 'list_users' );
	}
}

if ( ! function_exists( 'uempro_can_import' ) ) {
	/**
	 * Whether the current user may import (create and edit) users.
	 *
	 * @return bool
	 */
	function uempro_can_import() {
		return current_user_can( 'create_users' ) && current_user_can( 'edit_users' );
	}
}

if ( ! function_exists( 'uempro_admin_url' ) ) {
	/**
	 * Admin URL of one of the plugin screens.
	 *
	 * @param string $slug Page slug.
	 * @param array  $args Extra query args.
	 * @return string
	 */
	function uempro_admin_url( $slug, array $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => $slug ), $args ), admin_url( 'admin.php' ) );
	}
}

if ( ! function_exists( 'uempro_asset_url' ) ) {
	/**
	 * URL inside the plugin's /assets/ folder.
	 *
	 * @param string $path Relative path, e.g. 'css/admin.css'.
	 * @return string
	 */
	function uempro_asset_url( $path ) {
		return UEMPRO_ASSETS_URL . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'uempro_asset_version' ) ) {
	/**
	 * Cache-busting version for one asset: the file's modification time, so
	 * browsers pick up CSS/JS changes without a manual version bump.
	 *
	 * @param string $relative_path Path inside /assets/.
	 * @return string
	 */
	function uempro_asset_version( $relative_path ) {
		$file = UEMPRO_PATH . 'assets/' . ltrim( $relative_path, '/' );
		$time = file_exists( $file ) ? filemtime( $file ) : false;
		return $time ? (string) $time : UEMPRO_VERSION;
	}
}

if ( ! function_exists( 'uempro_get_template' ) ) {
	/**
	 * Load a template file from /templates/ in its own scope.
	 *
	 * @param string $template_name File name inside /templates/ (no extension).
	 * @param array  $args          Variables extracted into the template scope.
	 */
	function uempro_get_template( $template_name, $args = array() ) {
		$file = UEMPRO_PATH . 'templates/' . $template_name . '.php';
		if ( ! file_exists( $file ) ) {
			return;
		}
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}
		include $file;
	}
}

if ( ! function_exists( 'uempro_admin_pages' ) ) {
	/**
	 * Registry of every admin screen: slug => label, icon, capability, template.
	 * Adding a screen only means adding one row here plus its template.
	 *
	 * @return array
	 */
	function uempro_admin_pages() {
		$cap = uempro_capability();
		return array(
			'uempro'           => array(
				'label'    => __( 'Dashboard', 'user-export-manager-pro' ),
				'title'    => __( 'Dashboard', 'user-export-manager-pro' ),
				'subtitle' => __( 'Overview of your users, templates and recent activity.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-dashboard',
				'cap'      => $cap,
				'template' => 'admin/dashboard',
			),
			'uempro-export'    => array(
				'label'    => __( 'Export users', 'user-export-manager-pro' ),
				'title'    => __( 'Export users', 'user-export-manager-pro' ),
				'subtitle' => __( 'Choose who to include, which fields become columns, and the file format.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-upload',
				'cap'      => $cap,
				'template' => 'admin/export',
			),
			'uempro-import'    => array(
				'label'    => __( 'Import users', 'user-export-manager-pro' ),
				'title'    => __( 'Import users', 'user-export-manager-pro' ),
				'subtitle' => __( 'Create or update users from a CSV, Excel, JSON or XML file — including files exported by this plugin.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-download',
				'cap'      => 'create_users',
				'template' => 'admin/import',
			),
			'uempro-templates' => array(
				'label'    => __( 'Templates', 'user-export-manager-pro' ),
				'title'    => __( 'Export templates', 'user-export-manager-pro' ),
				'subtitle' => __( 'Saved users, fields and format — run them again with one click.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-portfolio',
				'cap'      => $cap,
				'template' => 'admin/templates',
			),
			'uempro-history'   => array(
				'label'    => __( 'History', 'user-export-manager-pro' ),
				'title'    => __( 'History', 'user-export-manager-pro' ),
				'subtitle' => __( 'Every export and import, with downloads for export files.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-backup',
				'cap'      => $cap,
				'template' => 'admin/history',
			),
			'uempro-settings'  => array(
				'label'    => __( 'Settings', 'user-export-manager-pro' ),
				'title'    => __( 'Settings', 'user-export-manager-pro' ),
				'subtitle' => __( 'Defaults for exports and imports, file output, history and privacy.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-admin-generic',
				'cap'      => 'manage_options',
				'template' => 'admin/settings',
			),
			'uempro-docs'      => array(
				'label'    => __( 'Documentation', 'user-export-manager-pro' ),
				'title'    => __( 'Documentation', 'user-export-manager-pro' ),
				'subtitle' => __( 'Everything you need to export, import and keep user data safe.', 'user-export-manager-pro' ),
				'icon'     => 'dashicons-book-alt',
				'cap'      => $cap,
				'template' => 'admin/docs',
			),
		);
	}
}
