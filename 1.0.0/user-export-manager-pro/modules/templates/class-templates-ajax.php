<?php
/**
 * AJAX controller for export templates.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Templates_Ajax' ) ) {

	/**
	 * Class UEMPro_Templates_Ajax
	 */
	class UEMPro_Templates_Ajax {

		use UEMPro_Ajax_Guard;

		/**
		 * Action => method map.
		 *
		 * @return array
		 */
		public static function actions() {
			return array(
				'uempro_template_get'       => 'get',
				'uempro_template_save'      => 'save',
				'uempro_template_delete'    => 'delete',
				'uempro_template_duplicate' => 'duplicate',
			);
		}

		/**
		 * Get one template.
		 */
		public function get() {
			$this->guard();
			$template = UEMPro_Templates::get( absint( $this->input( 'id', 0 ) ) );
			if ( ! $template ) {
				$this->fail( new WP_Error( 'uempro_template_missing', __( 'That template no longer exists.', 'user-export-manager-pro' ) ) );
			}
			wp_send_json_success( $template );
		}

		/**
		 * Create or update a template.
		 */
		public function save() {
			$this->guard();

			$data = UEMPro_Templates::prepare(
				array(
					'id'          => $this->input( 'id', 0 ),
					'name'        => $this->input( 'name' ),
					'description' => $this->input( 'description' ),
					'fields'      => $this->input( 'fields' ),
					'filters'     => $this->input( 'filters' ),
					'format'      => $this->input( 'format' ),
					'filename'    => $this->input( 'filename' ),
				)
			);
			if ( is_wp_error( $data ) ) {
				$this->fail( $data );
			}

			$id = UEMPro_Templates::save( $data );
			if ( is_wp_error( $id ) ) {
				$this->fail( $id );
			}
			wp_send_json_success( UEMPro_Templates::get( $id ) );
		}

		/**
		 * Delete a template.
		 */
		public function delete() {
			$this->guard();
			UEMPro_Templates::delete( absint( $this->input( 'id', 0 ) ) );
			wp_send_json_success( array( 'count' => UEMPro_Templates::count() ) );
		}

		/**
		 * Duplicate a template.
		 */
		public function duplicate() {
			$this->guard();
			$id = UEMPro_Templates::duplicate( absint( $this->input( 'id', 0 ) ) );
			if ( is_wp_error( $id ) ) {
				$this->fail( $id );
			}
			wp_send_json_success( UEMPro_Templates::get( $id ) );
		}
	}
}
