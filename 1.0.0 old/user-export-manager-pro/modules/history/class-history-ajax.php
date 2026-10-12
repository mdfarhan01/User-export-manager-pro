<?php
/**
 * AJAX controller for the history module.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_History_Ajax' ) ) {

	/**
	 * Class UEMPro_History_Ajax
	 */
	class UEMPro_History_Ajax {

		use UEMPro_Ajax_Guard;

		/**
		 * Action => method map.
		 *
		 * @return array
		 */
		public static function actions() {
			return array(
				'uempro_history_delete' => 'delete',
				'uempro_history_clear'  => 'clear',
			);
		}

		/**
		 * Delete one history entry and its file.
		 */
		public function delete() {
			$this->guard( 'manage_options' );
			UEMPro_History::delete( absint( $this->input( 'id', 0 ) ) );
			wp_send_json_success();
		}

		/**
		 * Delete all history entries and files.
		 */
		public function clear() {
			$this->guard( 'manage_options' );
			UEMPro_History::clear();
			wp_send_json_success();
		}
	}
}
