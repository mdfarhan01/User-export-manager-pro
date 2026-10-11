<?php
/**
 * Plugin Name:       User Export Manager Pro
 * Description:       Export and import WordPress users with their custom profile fields — CSV, Excel (XLSX), JSON or XML. Reusable export templates and a downloadable history.
 * Version:           1.2.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Md. Farhan Sadik
 * Text Domain:       user-export-manager-pro
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package User_Export_Manager_Pro
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All constants are prefixed with UEMPRO_ so they can never collide with
 * any other plugin or theme.
 */
define( 'UEMPRO_VERSION', '1.2.0' );
define( 'UEMPRO_FILE', __FILE__ );
define( 'UEMPRO_PATH', plugin_dir_path( __FILE__ ) );
define( 'UEMPRO_URL', plugin_dir_url( __FILE__ ) );
define( 'UEMPRO_BASENAME', plugin_basename( __FILE__ ) );
define( 'UEMPRO_ASSETS_URL', UEMPRO_URL . 'assets/' );
define( 'UEMPRO_MODULES_PATH', UEMPRO_PATH . 'modules/' );
define( 'UEMPRO_MIN_PHP_VERSION', '7.4' );
define( 'UEMPRO_TEXT_DOMAIN', 'user-export-manager-pro' );
define( 'UEMPRO_OPTION_KEY', 'uempro_settings' );

/**
 * Self-contained includes — explicit, no autoloader and no namespaces, so the
 * plugin has zero dependency collisions with other plugins.
 */
require_once UEMPRO_PATH . 'includes/functions.php';
require_once UEMPRO_PATH . 'includes/class-helper.php';
require_once UEMPRO_PATH . 'includes/class-loader.php';
require_once UEMPRO_PATH . 'includes/class-settings.php';
require_once UEMPRO_PATH . 'includes/class-install.php';
require_once UEMPRO_PATH . 'includes/class-cron.php';
require_once UEMPRO_PATH . 'includes/class-fields.php';
require_once UEMPRO_PATH . 'includes/class-assets.php';
require_once UEMPRO_PATH . 'includes/class-admin.php';
require_once UEMPRO_PATH . 'traits/trait-ajax-guard.php';

// Feature modules.
require_once UEMPRO_MODULES_PATH . 'export/class-export-config.php';
require_once UEMPRO_MODULES_PATH . 'export/class-export-query.php';
require_once UEMPRO_MODULES_PATH . 'export/class-export-writer.php';
require_once UEMPRO_MODULES_PATH . 'export/class-export-job.php';
require_once UEMPRO_MODULES_PATH . 'export/class-export-download.php';
require_once UEMPRO_MODULES_PATH . 'export/class-export-ajax.php';
require_once UEMPRO_MODULES_PATH . 'import/class-import-reader.php';
require_once UEMPRO_MODULES_PATH . 'import/class-import-job.php';
require_once UEMPRO_MODULES_PATH . 'import/class-import-ajax.php';
require_once UEMPRO_MODULES_PATH . 'templates/class-templates.php';
require_once UEMPRO_MODULES_PATH . 'templates/class-templates-ajax.php';
require_once UEMPRO_MODULES_PATH . 'history/class-history.php';
require_once UEMPRO_MODULES_PATH . 'history/class-history-ajax.php';

require_once UEMPRO_PATH . 'includes/class-plugin.php';

/**
 * Boot the plugin on `plugins_loaded`.
 *
 * @return UEMPro_Plugin
 */
function uempro() {
	return UEMPro_Plugin::instance();
}

add_action( 'plugins_loaded', 'uempro' );

/**
 * Activation hook — create tables, the protected exports folder and defaults.
 */
function uempro_activate() {
	UEMPro_Install::install();
	update_option( 'uempro_activation_redirect', true, false );
}
register_activation_hook( __FILE__, 'uempro_activate' );

/**
 * Deactivation hook — keep data, just stop the scheduled cleanup.
 */
function uempro_deactivate() {
	UEMPro_Cron::unschedule();
}
register_deactivation_hook( __FILE__, 'uempro_deactivate' );
