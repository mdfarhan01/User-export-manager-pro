<?php
/**
 * Registers/enqueues all CSS + JS. Every handle is prefixed with `uempro-`
 * and assets load only on this plugin's own screens.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Assets' ) ) {

	/**
	 * Class UEMPro_Assets
	 */
	class UEMPro_Assets {

		/**
		 * Enqueue assets for the plugin's admin screens only.
		 */
		public function admin_assets() {
			$page = UEMPro_Helper::current_page();
			if ( ! $page ) {
				return;
			}

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'uempro-admin', uempro_asset_url( 'css/admin.css' ), array(), uempro_asset_version( 'css/admin.css' ) );
			wp_enqueue_script( 'uempro-admin', uempro_asset_url( 'js/admin.js' ), array( 'jquery' ), uempro_asset_version( 'js/admin.js' ), true );

			$settings  = uempro_get_settings();
			$templates = in_array( $page, array( 'uempro', 'uempro-export', 'uempro-templates' ), true ) ? UEMPro_Templates::all() : array();
			$indexed   = array();
			foreach ( $templates as $template ) {
				$indexed[ $template['id'] ] = $template;
			}

			$preload = 0;
			if ( 'uempro-export' === $page ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: which template to pre-fill.
				$preload = isset( $_GET['template'] ) ? absint( $_GET['template'] ) : 0;
			}

			wp_localize_script(
				'uempro-admin',
				'UEMPRO',
				array(
					'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
					'nonce'           => wp_create_nonce( 'uempro_nonce' ),
					'page'            => $page,
					'templates'       => (object) $indexed,
					'preloadTemplate' => isset( $indexed[ $preload ] ) ? $preload : 0,
					'defaultFields'   => UEMPro_Fields::default_selection(),
					'defaultFormat'   => $settings['default_format'],
					'defaultFilename' => $settings['default_filename'],
					'roles'           => (object) array_map( 'translate_user_role', wp_roles()->get_names() ),
					'importTargets'   => 'uempro-import' === $page ? UEMPro_Import_Job::targets() : array(),
					'maxUpload'       => wp_max_upload_size(),
					'maxUploadLabel'  => size_format( wp_max_upload_size() ),
					'urls'            => array(
						'export'    => uempro_admin_url( 'uempro-export' ),
						'import'    => uempro_admin_url( 'uempro-import' ),
						'templates' => uempro_admin_url( 'uempro-templates' ),
						'history'   => uempro_admin_url( 'uempro-history' ),
						'users'     => admin_url( 'users.php' ),
					),
					'i18n'            => self::js_strings(),
				)
			);
		}

		/**
		 * Translatable strings used by admin.js.
		 *
		 * @return array
		 */
		private static function js_strings() {
			return array(
				'requestFailed'     => __( 'The server did not respond. Check your connection and try again.', 'user-export-manager-pro' ),
				'genericError'      => __( 'Something went wrong. Reload the page and try again.', 'user-export-manager-pro' ),
				'scanning'          => __( 'Scanning…', 'user-export-manager-pro' ),
				'rescan'            => __( 'Scan custom fields', 'user-export-manager-pro' ),
				/* translators: %s: number of custom fields */
				'scanDone'          => __( 'Scan complete — %s custom fields found.', 'user-export-manager-pro' ),
				/* translators: %s: time of the last scan */
				'scannedAt'         => __( 'Fields detected automatically · last scanned %s', 'user-export-manager-pro' ),
				/* translators: 1: number selected, 2: total number */
				'fieldsSelected'    => __( '%1$s of %2$s fields selected', 'user-export-manager-pro' ),
				'noFieldMatch'      => __( 'No fields match that filter.', 'user-export-manager-pro' ),
				'noColumns'         => __( 'Tick fields above. Each one becomes a column here, in this order.', 'user-export-manager-pro' ),
				'removeColumn'      => __( 'Remove column', 'user-export-manager-pro' ),
				'notOnSite'         => __( 'not found on this site', 'user-export-manager-pro' ),
				'show'              => __( 'Show', 'user-export-manager-pro' ),
				'hide'              => __( 'Hide', 'user-export-manager-pro' ),
				'loadingUsers'      => __( 'Loading users…', 'user-export-manager-pro' ),
				'noUsers'           => __( 'No users match this search.', 'user-export-manager-pro' ),
				/* translators: 1: current page, 2: total pages, 3: total users */
				'pageOf'            => __( 'Page %1$s of %2$s · %3$s users', 'user-export-manager-pro' ),
				/* translators: %s: number of users */
				'usersMatch'        => __( '%s users match', 'user-export-manager-pro' ),
				'oneUserMatch'      => __( '1 user matches', 'user-export-manager-pro' ),
				'counting'          => __( 'Counting users…', 'user-export-manager-pro' ),
				'pickUsers'         => __( 'Select at least one user, or choose a different option.', 'user-export-manager-pro' ),
				'pickRoles'         => __( 'Select at least one role.', 'user-export-manager-pro' ),
				'pickDates'         => __( 'Enter a start or end date for the custom range.', 'user-export-manager-pro' ),
				'pickFields'        => __( 'Choose at least one field to export.', 'user-export-manager-pro' ),
				'nameRequired'      => __( 'Give the template a name.', 'user-export-manager-pro' ),
				'templateSaved'     => __( 'Template saved.', 'user-export-manager-pro' ),
				'templateUpdated'   => __( 'Template updated.', 'user-export-manager-pro' ),
				'templateDeleted'   => __( 'Template deleted.', 'user-export-manager-pro' ),
				'templateCopied'    => __( 'Template duplicated.', 'user-export-manager-pro' ),
				/* translators: %s: template name */
				'templateLoaded'    => __( 'Loaded template “%s”. Its fields are ticked below — adjust anything you like, then export.', 'user-export-manager-pro' ),
				/* translators: %s: template name */
				'updateTemplate'    => __( 'Update “%s”', 'user-export-manager-pro' ),
				'newTemplate'       => __( 'New template', 'user-export-manager-pro' ),
				'editTemplate'      => __( 'Edit template', 'user-export-manager-pro' ),
				'saveTemplate'      => __( 'Save template', 'user-export-manager-pro' ),
				'saveChanges'       => __( 'Save changes', 'user-export-manager-pro' ),
				'saving'            => __( 'Saving…', 'user-export-manager-pro' ),
				'deleteTemplateQ'   => __( 'Delete this template?', 'user-export-manager-pro' ),
				/* translators: %s: template name */
				'deleteTemplateMsg' => __( '“%s” will be removed. Past exports in History are not affected.', 'user-export-manager-pro' ),
				'delete'            => __( 'Delete', 'user-export-manager-pro' ),
				'cancel'            => __( 'Cancel', 'user-export-manager-pro' ),
				'close'             => __( 'Close', 'user-export-manager-pro' ),
				'deleteExportQ'     => __( 'Delete this export?', 'user-export-manager-pro' ),
				'deleteExportMsg'   => __( 'The history entry and its file will be permanently removed.', 'user-export-manager-pro' ),
				'clearHistoryQ'     => __( 'Delete all exports?', 'user-export-manager-pro' ),
				'clearHistoryMsg'   => __( 'Every history entry and every export file will be permanently removed. Templates are kept.', 'user-export-manager-pro' ),
				'deleteAll'         => __( 'Delete everything', 'user-export-manager-pro' ),
				'historyCleared'    => __( 'All exports deleted.', 'user-export-manager-pro' ),
				'exportDeleted'     => __( 'Export deleted.', 'user-export-manager-pro' ),
				'preparing'         => __( 'Preparing export…', 'user-export-manager-pro' ),
				/* translators: 1: processed users, 2: total users */
				'progress'          => __( 'Exported %1$s of %2$s users', 'user-export-manager-pro' ),
				'writing'           => __( 'Writing file…', 'user-export-manager-pro' ),
				'exportDone'        => __( 'Export ready', 'user-export-manager-pro' ),
				/* translators: 1: number of users, 2: file size */
				'exportDoneMsg'     => __( '%1$s users exported · %2$s. Your download should start automatically.', 'user-export-manager-pro' ),
				'download'          => __( 'Download file', 'user-export-manager-pro' ),
				'exportFailed'      => __( 'Export failed', 'user-export-manager-pro' ),
				'runExport'         => __( 'Run export', 'user-export-manager-pro' ),
				'exporting'         => __( 'Exporting…', 'user-export-manager-pro' ),
				/* translators: %s: template name */
				'exportingTemplate' => __( 'Exporting “%s”', 'user-export-manager-pro' ),
				'viewHistory'       => __( 'View history', 'user-export-manager-pro' ),
				'allUsers'          => __( 'All users', 'user-export-manager-pro' ),
				/* translators: %s: number of users */
				'selectedUsers'     => __( '%s selected users', 'user-export-manager-pro' ),
				/* translators: %s: role list */
				'roleSummary'       => __( 'Role: %s', 'user-export-manager-pro' ),
				'today'             => __( 'Registered today', 'user-export-manager-pro' ),
				'last7'             => __( 'Registered in the last 7 days', 'user-export-manager-pro' ),
				'last30'            => __( 'Registered in the last 30 days', 'user-export-manager-pro' ),
				/* translators: 1: start date, 2: end date */
				'customRange'       => __( 'Registered %1$s to %2$s', 'user-export-manager-pro' ),
				/* translators: %s: search term */
				'matching'          => __( 'matching “%s”', 'user-export-manager-pro' ),
				'ascending'         => __( 'ascending', 'user-export-manager-pro' ),
				'descending'        => __( 'descending', 'user-export-manager-pro' ),
				/* translators: %s: number of fields */
				'nFields'           => __( '%s fields', 'user-export-manager-pro' ),
				'scanCustom'        => __( 'Scan custom fields', 'user-export-manager-pro' ),
				/* translators: %s: time of the last scan */
				'lastScan'          => __( 'Last scanned %s', 'user-export-manager-pro' ),
				'uploadPick'        => __( 'Choose a file to import.', 'user-export-manager-pro' ),
				'uploading'         => __( 'Reading file…', 'user-export-manager-pro' ),
				'badType'           => __( 'Use a .csv, .xlsx, .json or .xml file.', 'user-export-manager-pro' ),
				/* translators: %s: maximum upload size */
				'tooBig'            => __( 'That file is larger than the %s upload limit.', 'user-export-manager-pro' ),
				/* translators: %s: number of rows */
				'rowsCount'         => __( '%s rows', 'user-export-manager-pro' ),
				/* translators: %s: sort column and direction, e.g. "User ID, ascending" */
				'sortedBy'          => __( 'Sorted by %s', 'user-export-manager-pro' ),
				'edit'              => __( 'Edit', 'user-export-manager-pro' ),
				'columnsInFile'     => __( 'Columns in the file, in this order', 'user-export-manager-pro' ),
				'matched'           => __( 'Auto-matched', 'user-export-manager-pro' ),
				'skipColumn'        => __( 'Don’t import', 'user-export-manager-pro' ),
				'newMeta'           => __( 'New custom field…', 'user-export-manager-pro' ),
				'metaKeyPh'         => __( 'meta_key', 'user-export-manager-pro' ),
				'groupIdentify'     => __( 'Identify users', 'user-export-manager-pro' ),
				'groupProfile'      => __( 'WordPress profile', 'user-export-manager-pro' ),
				'groupPassword'     => __( 'Password', 'user-export-manager-pro' ),
				'groupCustom'       => __( 'Custom fields', 'user-export-manager-pro' ),
				'groupHidden'       => __( 'Hidden & plugin data', 'user-export-manager-pro' ),
				'needIdentifier'    => __( 'Map at least one column to Email, Username or User ID so users can be matched or created.', 'user-export-manager-pro' ),
				'dupTarget'         => __( 'Two columns are mapped to the same field. Each field can be imported only once.', 'user-export-manager-pro' ),
				'badMetaKey'        => __( 'Enter a meta key (letters, numbers, underscores and dashes) for every “New custom field” column.', 'user-export-manager-pro' ),
				/* translators: 1: number of rows, 2: file format */
				'fileSummary'       => __( '%1$s rows · %2$s', 'user-export-manager-pro' ),
				/* translators: 1: number of mapped columns, 2: total columns */
				'mappedCount'       => __( '%1$s of %2$s columns will be imported', 'user-export-manager-pro' ),
				'runImport'         => __( 'Start import', 'user-export-manager-pro' ),
				'importing'         => __( 'Importing…', 'user-export-manager-pro' ),
				/* translators: 1: processed rows, 2: total rows */
				'importProgress'    => __( 'Processed %1$s of %2$s rows', 'user-export-manager-pro' ),
				'importDone'        => __( 'Import complete', 'user-export-manager-pro' ),
				'importFailed'      => __( 'Import stopped', 'user-export-manager-pro' ),
				'created'           => __( 'Created', 'user-export-manager-pro' ),
				'updated'           => __( 'Updated', 'user-export-manager-pro' ),
				'skipped'           => __( 'Skipped', 'user-export-manager-pro' ),
				'failed'            => __( 'Failed', 'user-export-manager-pro' ),
				'row'               => __( 'Row', 'user-export-manager-pro' ),
				'problem'           => __( 'Problem', 'user-export-manager-pro' ),
				/* translators: %s: number of further problems not listed */
				'moreProblems'      => __( '…and %s more rows with problems.', 'user-export-manager-pro' ),
				'importAnother'     => __( 'Import another file', 'user-export-manager-pro' ),
				'viewUsers'         => __( 'View users', 'user-export-manager-pro' ),
			);
		}
	}
}
