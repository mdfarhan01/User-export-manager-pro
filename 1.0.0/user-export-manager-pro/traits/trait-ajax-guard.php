<?php
/**
 * Shared AJAX helpers for every module's AJAX controller: capability +
 * nonce checks, raw input reading and error responses.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'UEMPro_Ajax_Guard' ) ) {

	/**
	 * Trait UEMPro_Ajax_Guard
	 */
	trait UEMPro_Ajax_Guard {

		/**
		 * Verify capability (or capabilities) and the "uempro_nonce" nonce.
		 *
		 * @param string|string[] $capabilities Required capabilities; defaults to the plugin capability.
		 */
		protected function guard( $capabilities = '' ) {
			$capabilities = $capabilities ? (array) $capabilities : array( uempro_capability() );
			foreach ( $capabilities as $capability ) {
				if ( ! current_user_can( $capability ) ) {
					wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'user-export-manager-pro' ) ), 403 );
				}
			}
			if ( ! check_ajax_referer( 'uempro_nonce', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => __( 'Your session has expired. Reload the page and try again.', 'user-export-manager-pro' ) ), 403 );
			}
		}

		/**
		 * Read a raw POST value (unslashed). Callers always sanitize.
		 *
		 * @param string $key      Key.
		 * @param mixed  $fallback Default.
		 * @return mixed
		 */
		protected function input( $key, $fallback = '' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce checked in guard(); every caller sanitizes the value.
			return isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $fallback;
		}

		/**
		 * Send a WP_Error as a JSON error response.
		 *
		 * @param WP_Error $error Error.
		 */
		protected function fail( WP_Error $error ) {
			wp_send_json_error( array( 'message' => $error->get_error_message() ) );
		}

		/**
		 * Send success, or the error if $result is a WP_Error.
		 *
		 * @param mixed $result Result.
		 */
		protected function respond( $result ) {
			if ( is_wp_error( $result ) ) {
				$this->fail( $result );
			}
			wp_send_json_success( $result );
		}
	}
}
