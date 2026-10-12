<?php
/**
 * Core orchestrator. Singleton on purpose — exactly one instance ever runs.
 * Every action and filter is declared here and registered via the Loader.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UEMPro_Plugin' ) ) {

	/**
	 * Class UEMPro_Plugin
	 */
	final class UEMPro_Plugin {

		/**
		 * Singleton instance.
		 *
		 * @var UEMPro_Plugin|null
		 */
		private static $instance = null;

		/**
		 * Hook loader.
		 *
		 * @var UEMPro_Loader
		 */
		public $loader;

		/**
		 * Assets manager.
		 *
		 * @var UEMPro_Assets
		 */
		public $assets;

		/**
		 * Admin screens manager.
		 *
		 * @var UEMPro_Admin
		 */
		public $admin;

		/**
		 * Get (and lazily create) the singleton.
		 *
		 * @return UEMPro_Plugin
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor — private, use ::instance().
		 */
		private function __construct() {
			if ( ! $this->environment_ok() ) {
				return;
			}

			UEMPro_Install::maybe_upgrade();

			$this->loader = new UEMPro_Loader();
			$this->assets = new UEMPro_Assets();
			$this->admin  = new UEMPro_Admin();

			$this->define_common_hooks();
			$this->define_admin_hooks();
			$this->define_module_hooks();

			$this->loader->run();
		}

		/**
		 * Bail with an admin notice if PHP is too old, instead of a fatal error.
		 *
		 * @return bool
		 */
		private function environment_ok() {
			if ( ! UEMPro_Helper::version_ok( PHP_VERSION, UEMPRO_MIN_PHP_VERSION ) ) {
				add_action( 'admin_notices', array( $this, 'notice_min_php' ) );
				return false;
			}
			return true;
		}

		/**
		 * Hooks that run on every request (cron runs outside wp-admin).
		 */
		private function define_common_hooks() {
			// Translations are loaded automatically by WordPress.org (WP 4.6+).
			$this->loader->add_action( UEMPro_Cron::HOOK, 'UEMPro_Cron', 'run' );
		}

		/**
		 * Admin screens, assets, settings form and plugin row links.
		 */
		private function define_admin_hooks() {
			if ( ! is_admin() ) {
				return;
			}
			$this->loader->add_action( 'admin_menu', $this->admin, 'register_menu' );
			$this->loader->add_action( 'admin_enqueue_scripts', $this->assets, 'admin_assets' );
			$this->loader->add_action( 'admin_init', $this->admin, 'maybe_activation_redirect' );
			$this->loader->add_action( 'admin_post_uempro_save_settings', 'UEMPro_Settings', 'handle_save' );
			$this->loader->add_filter( 'admin_body_class', $this->admin, 'body_class' );
			$this->loader->add_filter( 'admin_footer_text', $this->admin, 'footer_text' );
			$this->loader->add_filter( 'plugin_action_links_' . UEMPRO_BASENAME, $this->admin, 'plugin_action_links' );
			$this->loader->add_filter( 'plugin_row_meta', $this->admin, 'plugin_row_meta', 10, 2 );
		}

		/**
		 * AJAX endpoints and handlers of every feature module.
		 */
		private function define_module_hooks() {
			if ( ! is_admin() ) {
				return;
			}

			$download = new UEMPro_Export_Download();
			$this->loader->add_action( 'admin_post_uempro_download', $download, 'handle' );

			$controllers = array( 'UEMPro_Export_Ajax', 'UEMPro_Import_Ajax', 'UEMPro_Templates_Ajax', 'UEMPro_History_Ajax' );
			foreach ( $controllers as $class ) {
				$controller = new $class();
				foreach ( $class::actions() as $action => $method ) {
					$this->loader->add_action( 'wp_ajax_' . $action, $controller, $method );
				}
			}
		}

		/**
		 * Notice: PHP version too old.
		 */
		public function notice_min_php() {
			$message = sprintf(
				/* translators: 1: Required PHP version 2: Current PHP version */
				esc_html__( 'User Export Manager Pro requires PHP %1$s or higher. Your server is running %2$s.', 'user-export-manager-pro' ),
				UEMPRO_MIN_PHP_VERSION,
				PHP_VERSION
			);
			printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		}

		/**
		 * Prevent cloning of the singleton.
		 */
		private function __clone() {}

		/**
		 * Prevent unserializing of the singleton.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cannot unserialize singleton.', 'user-export-manager-pro' ), '1.0.0' );
		}
	}
}
