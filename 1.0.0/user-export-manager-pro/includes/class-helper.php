<?php
/**
 * Small static helper utilities shared across admin screens and modules.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Helper' ) ) {

	/**
	 * Class UEMPro_Helper
	 */
	class UEMPro_Helper {

		/**
		 * Version compare helper.
		 *
		 * @param string $version    Version to check.
		 * @param string $compare_to Minimum required version.
		 * @return bool
		 */
		public static function version_ok( $version, $compare_to ) {
			return version_compare( $version, $compare_to, '>=' );
		}

		/**
		 * Current plugin admin page slug, or ''.
		 *
		 * @return string
		 */
		public static function current_page() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing.
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
			return array_key_exists( $page, uempro_admin_pages() ) ? $page : '';
		}

		/**
		 * Echo one field checkbox for the field picker.
		 *
		 * @param string $key     Field key.
		 * @param string $label   Label.
		 * @param bool   $checked Checked.
		 */
		public static function field_item( $key, $label, $checked = false ) {
			?>
			<label class="uempro-fp__item" data-key="<?php echo esc_attr( $key ); ?>" data-search="<?php echo esc_attr( strtolower( $label . ' ' . $key ) ); ?>">
				<input type="checkbox" value="<?php echo esc_attr( $key ); ?>" data-label="<?php echo esc_attr( $label ); ?>" <?php checked( $checked ); ?>>
				<span class="uempro-fp__text">
					<span class="uempro-fp__label"><?php echo esc_html( $label ); ?></span>
					<code class="uempro-fp__key"><?php echo esc_html( $key ); ?></code>
				</span>
			</label>
			<?php
		}

		/**
		 * Echo a file-format badge.
		 *
		 * @param string $format Format key.
		 */
		public static function format_badge( $format ) {
			$format = sanitize_key( $format );
			printf( '<span class="uempro-badge uempro-badge--%1$s">%2$s</span>', esc_attr( $format ), esc_html( strtoupper( $format ) ) );
		}

		/**
		 * Human date from a MySQL datetime in site time.
		 *
		 * @param string $mysql Datetime.
		 * @return string
		 */
		public static function date( $mysql ) {
			return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $mysql ) );
		}
	}
}
