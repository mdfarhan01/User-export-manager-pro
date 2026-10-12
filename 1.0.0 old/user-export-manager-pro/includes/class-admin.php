<?php
/**
 * Builds the wp-admin menu and renders every screen inside the shared
 * app shell (left sidebar + top bar). Menu slugs, DOM ids and classes are
 * all prefixed with `uempro` so the screens never clash with other plugins.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Admin' ) ) {

	/**
	 * Class UEMPro_Admin
	 */
	class UEMPro_Admin {

		/**
		 * Top-level menu slug.
		 *
		 * @var string
		 */
		const MENU_SLUG = 'uempro';

		/**
		 * Register the top-level admin menu and its submenus.
		 */
		public function register_menu() {
			add_menu_page(
				__( 'User Export Manager Pro', 'user-export-manager-pro' ),
				__( 'User Export', 'user-export-manager-pro' ),
				uempro_capability(),
				self::MENU_SLUG,
				array( $this, 'render_page' ),
				'data:image/svg+xml;base64,' . base64_encode( $this->menu_icon_svg() ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Inline SVG menu icon.
				71
			);

			foreach ( uempro_admin_pages() as $slug => $page ) {
				add_submenu_page(
					self::MENU_SLUG,
					$page['title'] . ' ‹ ' . __( 'User Export Manager Pro', 'user-export-manager-pro' ),
					$page['label'],
					$page['cap'],
					$slug,
					array( $this, 'render_page' )
				);
			}
		}

		/**
		 * Inline SVG used as the menu icon.
		 *
		 * @return string
		 */
		protected function menu_icon_svg() {
			return '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill="#a7aaad" d="M4 2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm1 4v1.5h6V6H5zm0 3.25v1.5h4.5v-1.5H5zM5 12.5V14h6v-1.5H5zm8.2-4.3-1 1 1.1 1.05H11.5v1.5h1.8l-1.1 1.05 1 1 2.8-2.8-2.8-2.8z"/></svg>';
		}

		/**
		 * Render the active screen inside the shared shell.
		 */
		public function render_page() {
			$active = UEMPro_Helper::current_page();
			$pages  = uempro_admin_pages();

			if ( ! $active || ! current_user_can( $pages[ $active ]['cap'] ) ) {
				wp_die( esc_html__( 'You do not have permission to view this page.', 'user-export-manager-pro' ) );
			}

			$page = $pages[ $active ];

			uempro_get_template(
				'admin/header',
				array(
					'active'   => $active,
					'title'    => $page['title'],
					'subtitle' => $page['subtitle'],
				)
			);
			uempro_get_template( $page['template'], array( 'active' => $active ) );
			uempro_get_template( 'admin/footer' );
		}

		/**
		 * Add a body class on the plugin's screens (used to tune wp-admin chrome).
		 *
		 * @param string $classes Classes.
		 * @return string
		 */
		public function body_class( $classes ) {
			return UEMPro_Helper::current_page() ? $classes . ' uempro-screen' : $classes;
		}

		/**
		 * Footer credit on the plugin's screens.
		 *
		 * @param string $text Footer text.
		 * @return string
		 */
		public function footer_text( $text ) {
			if ( ! UEMPro_Helper::current_page() ) {
				return $text;
			}
			/* translators: %s: developer name */
			return esc_html( sprintf( __( 'User Export Manager Pro is developed by %s.', 'user-export-manager-pro' ), 'Md. Farhan Sadik' ) );
		}

		/**
		 * Redirect to the dashboard once, right after activation.
		 */
		public function maybe_activation_redirect() {
			if ( ! get_option( 'uempro_activation_redirect' ) ) {
				return;
			}
			delete_option( 'uempro_activation_redirect' );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of a core query flag.
			if ( isset( $_GET['activate-multi'] ) || wp_doing_ajax() || ! current_user_can( uempro_capability() ) ) {
				return;
			}
			wp_safe_redirect( uempro_admin_url( self::MENU_SLUG ) );
			exit;
		}

		/**
		 * Action links on the Plugins screen.
		 *
		 * @param array $links Links.
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			array_unshift(
				$links,
				sprintf( '<a href="%s">%s</a>', esc_url( uempro_admin_url( 'uempro-export' ) ), esc_html__( 'Export', 'user-export-manager-pro' ) ),
				sprintf( '<a href="%s">%s</a>', esc_url( uempro_admin_url( 'uempro-import' ) ), esc_html__( 'Import', 'user-export-manager-pro' ) )
			);
			return $links;
		}

		/**
		 * Row meta on the Plugins screen.
		 *
		 * @param array  $meta Meta links.
		 * @param string $file Plugin file.
		 * @return array
		 */
		public function plugin_row_meta( $meta, $file ) {
			if ( UEMPRO_BASENAME === $file ) {
				$meta[] = sprintf( '<a href="%s">%s</a>', esc_url( uempro_admin_url( 'uempro-docs' ) ), esc_html__( 'Documentation', 'user-export-manager-pro' ) );
			}
			return $meta;
		}
	}
}
