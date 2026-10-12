<?php
/**
 * Fires when the plugin is deleted from wp-admin > Plugins.
 * Data is removed only if "Delete all plugin data on uninstall" is enabled,
 * and only data that belongs to User Export Manager Pro is touched.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove plugin data for the current site.
 */
function uempro_uninstall_site() {
	global $wpdb;

	wp_clear_scheduled_hook( 'uempro_daily_cleanup' );
	delete_option( 'uempro_activation_redirect' );

	$settings = get_option( 'uempro_settings', array() );
	if ( empty( $settings['delete_data_on_uninstall'] ) ) {
		return;
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Dropping this plugin's own tables on uninstall.
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'uempro_templates' ) );
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'uempro_history' ) );
	// phpcs:enable

	delete_option( 'uempro_settings' );
	delete_option( 'uempro_db_version' );
	delete_option( 'uempro_jobs' );
	delete_option( 'uempro_import_jobs' );
	delete_transient( 'uempro_meta_keys' );

	delete_transient( 'uempro_storage_check' );

	$upload = wp_upload_dir( null, false );
	$dirs   = (array) glob( trailingslashit( $upload['basedir'] ) . 'uempro-exports*', GLOB_ONLYDIR );
	if ( defined( 'UEMPRO_EXPORT_DIR' ) && is_string( UEMPRO_EXPORT_DIR ) && is_dir( UEMPRO_EXPORT_DIR ) ) {
		$dirs[] = UEMPRO_EXPORT_DIR;
	}
	foreach ( $dirs as $dir ) {
		$files = array_merge( (array) glob( $dir . '/*' ), (array) glob( $dir . '/.htaccess' ) );
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Removing the plugin's own, now empty, folder.
		rmdir( $dir );
	}
	delete_option( 'uempro_storage_key' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $uempro_site_id ) {
		switch_to_blog( $uempro_site_id );
		uempro_uninstall_site();
		restore_current_blog();
	}
} else {
	uempro_uninstall_site();
}
