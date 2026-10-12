<?php
/**
 * AJAX controller for the export module.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Export_Ajax' ) ) {

	/**
	 * Class UEMPro_Export_Ajax
	 */
	class UEMPro_Export_Ajax {

		use UEMPro_Ajax_Guard;

		/**
		 * Action => method map, registered by UEMPro_Plugin.
		 *
		 * @return array
		 */
		public static function actions() {
			return array(
				'uempro_count_users'   => 'count_users',
				'uempro_list_users'    => 'list_users',
				'uempro_scan_fields'   => 'scan_fields',
				'uempro_export_start'  => 'export_start',
				'uempro_export_step'   => 'export_step',
				'uempro_export_finish' => 'export_finish',
			);
		}

		/**
		 * Count users for a filter set.
		 */
		public function count_users() {
			$this->guard();
			$filters = UEMPro_Export_Config::sanitize_filters( UEMPro_Export_Config::decode( $this->input( 'filters' ) ) );
			wp_send_json_success(
				array(
					'count'   => UEMPro_Export_Query::count( $filters ),
					'summary' => UEMPro_Export_Config::describe_filters( $filters ),
				)
			);
		}

		/**
		 * Paged user list for "Specific users".
		 */
		public function list_users() {
			$this->guard();

			$paged    = max( 1, absint( $this->input( 'paged', 1 ) ) );
			$per_page = min( 100, max( 5, absint( $this->input( 'per_page', 10 ) ) ) );
			$search   = sanitize_text_field( (string) $this->input( 'search' ) );
			$role     = sanitize_key( (string) $this->input( 'role' ) );

			$args = array(
				'number'      => $per_page,
				'paged'       => $paged,
				'count_total' => true,
				'orderby'     => 'display_name',
				'order'       => 'ASC',
			);
			if ( '' !== $search ) {
				$args['search']         = '*' . $search . '*';
				$args['search_columns'] = array( 'user_login', 'user_email', 'user_nicename', 'display_name' );
			}
			if ( '' !== $role ) {
				$args['role'] = $role;
			}

			$query = new WP_User_Query( $args );
			$names = wp_roles()->get_names();
			$users = array();

			foreach ( $query->get_results() as $user ) {
				$roles = array();
				foreach ( (array) $user->roles as $r ) {
					$roles[] = isset( $names[ $r ] ) ? translate_user_role( $names[ $r ] ) : $r;
				}
				$users[] = array(
					'id'     => (int) $user->ID,
					'name'   => $user->display_name,
					'login'  => $user->user_login,
					'email'  => $user->user_email,
					'role'   => implode( ', ', $roles ),
					'avatar' => get_avatar_url( $user->ID, array( 'size' => 64 ) ),
				);
			}

			wp_send_json_success(
				array(
					'users'    => $users,
					'total'    => (int) $query->get_total(),
					'paged'    => $paged,
					'per_page' => $per_page,
				)
			);
		}

		/**
		 * Scan the usermeta table for custom fields (bypasses the cache).
		 */
		public function scan_fields() {
			$this->guard();
			$scan = UEMPro_Fields::scan( true );
			$scan['scanned_at'] = UEMPro_Fields::scanned_at_label();
			wp_send_json_success( $scan );
		}

		/**
		 * Build an export configuration from the request.
		 *
		 * @return array
		 */
		private function config_from_request() {
			$template_id   = absint( $this->input( 'template_id', 0 ) );
			$template_name = '';
			if ( $template_id ) {
				$template      = UEMPro_Templates::get( $template_id );
				$template_name = $template ? $template['name'] : '';
				$template_id   = $template ? $template_id : 0;
			}

			return array(
				'filters'       => UEMPro_Export_Config::sanitize_filters( UEMPro_Export_Config::decode( $this->input( 'filters' ) ) ),
				'fields'        => UEMPro_Export_Config::sanitize_fields( UEMPro_Export_Config::decode( $this->input( 'fields' ) ) ),
				'format'        => UEMPro_Export_Config::sanitize_format( $this->input( 'format', 'csv' ) ),
				'filename'      => UEMPro_Export_Config::sanitize_filename( $this->input( 'filename', '' ) ),
				'template_id'   => $template_id,
				'template_name' => $template_name,
			);
		}

		/**
		 * Start an export job (from the wizard, or straight from a template).
		 */
		public function export_start() {
			$this->guard();

			$template_id = absint( $this->input( 'run_template', 0 ) );
			if ( $template_id ) {
				$template = UEMPro_Templates::get( $template_id );
				if ( ! $template ) {
					$this->fail( new WP_Error( 'uempro_template_missing', __( 'That template no longer exists.', 'user-export-manager-pro' ) ) );
				}
				$config = UEMPro_Templates::to_config( $template );
			} else {
				$config = $this->config_from_request();
			}

			$this->respond( UEMPro_Export_Job::start( $config ) );
		}

		/**
		 * Process one batch.
		 */
		public function export_step() {
			$this->guard();
			$this->respond( UEMPro_Export_Job::step( sanitize_key( (string) $this->input( 'job' ) ) ) );
		}

		/**
		 * Write the final file.
		 */
		public function export_finish() {
			$this->guard();
			$this->respond( UEMPro_Export_Job::finish( sanitize_key( (string) $this->input( 'job' ) ) ) );
		}
	}
}
