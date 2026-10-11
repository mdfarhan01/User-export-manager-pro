<?php
/**
 * AJAX controller for the import module.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Import_Ajax' ) ) {

	/**
	 * Class UEMPro_Import_Ajax
	 */
	class UEMPro_Import_Ajax {

		use UEMPro_Ajax_Guard;

		/**
		 * Action => method map.
		 *
		 * @return array
		 */
		public static function actions() {
			return array(
				'uempro_import_upload'    => 'upload',
				'uempro_import_configure' => 'configure',
				'uempro_import_step'      => 'step',
			);
		}

		/**
		 * Capabilities needed to import users.
		 *
		 * @return string[]
		 */
		private function caps() {
			return array( 'create_users', 'edit_users' );
		}

		/**
		 * Receive the file, parse it and suggest a column mapping.
		 * The uploaded file itself is never stored — only its parsed rows,
		 * in the plugin's protected folder, until the import finishes.
		 */
		public function upload() {
			$this->guard( $this->caps() );

			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce checked in guard(); each part is validated below.
			$file = isset( $_FILES['file'] ) && is_array( $_FILES['file'] ) ? $_FILES['file'] : null;

			if ( ! $file || ! isset( $file['tmp_name'], $file['name'], $file['error'], $file['size'] ) ) {
				$this->fail( new WP_Error( 'uempro_import_nofile', __( 'Choose a file to import.', 'user-export-manager-pro' ) ) );
			}
			if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
				$this->fail( new WP_Error( 'uempro_import_upload', __( 'The file did not upload correctly. It may be larger than the server allows.', 'user-export-manager-pro' ) ) );
			}
			if ( (int) $file['size'] > wp_max_upload_size() ) {
				/* translators: %s: maximum upload size */
				$this->fail( new WP_Error( 'uempro_import_size', sprintf( __( 'That file is larger than the %s upload limit.', 'user-export-manager-pro' ), size_format( wp_max_upload_size() ) ) ) );
			}

			$name = sanitize_file_name( wp_basename( (string) $file['name'] ) );
			$this->respond( UEMPro_Import_Job::start( $file['tmp_name'], $name ) );
		}

		/**
		 * Save mapping + options.
		 */
		public function configure() {
			$this->guard( $this->caps() );

			$mapping = UEMPro_Export_Config::decode( $this->input( 'mapping' ) );
			$mapping = is_array( $mapping ) ? array_map( 'sanitize_text_field', array_filter( $mapping, 'is_scalar' ) ) : array();
			$options = UEMPro_Export_Config::decode( $this->input( 'options' ) );
			$options = is_array( $options ) ? map_deep( $options, 'sanitize_text_field' ) : array();

			$this->respond( UEMPro_Import_Job::configure( sanitize_key( (string) $this->input( 'job' ) ), $mapping, $options ) );
		}

		/**
		 * Process one batch of rows.
		 */
		public function step() {
			$this->guard( $this->caps() );
			$this->respond( UEMPro_Import_Job::step( sanitize_key( (string) $this->input( 'job' ) ) ) );
		}
	}
}
