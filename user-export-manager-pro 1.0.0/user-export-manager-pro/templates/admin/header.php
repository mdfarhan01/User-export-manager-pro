<?php
/**
 * Shared shell (left sidebar + top bar) for every plugin screen.
 *
 * @var string $active   Current page slug.
 * @var string $title    Page title shown in the top bar.
 * @var string $subtitle Short description under the title.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_nav_groups = array(
	array(
		'label' => __( 'Overview', 'user-export-manager-pro' ),
		'items' => array( 'uempro' ),
	),
	array(
		'label' => __( 'Users', 'user-export-manager-pro' ),
		'items' => array( 'uempro-export', 'uempro-import', 'uempro-templates', 'uempro-history' ),
	),
	array(
		'label' => __( 'Plugin', 'user-export-manager-pro' ),
		'items' => array( 'uempro-settings', 'uempro-docs' ),
	),
);
$uempro_pages      = uempro_admin_pages();

$uempro_notices = array(
	'settings-saved'  => __( 'Settings saved.', 'user-export-manager-pro' ),
	'template-saved'  => __( 'Template saved.', 'user-export-manager-pro' ),
	'storage-checked' => __( 'File storage check finished.', 'user-export-manager-pro' ),
);
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set by the plugin's own redirects.
$uempro_notice = isset( $_GET['uempro_notice'] ) ? sanitize_key( wp_unslash( $_GET['uempro_notice'] ) ) : '';
?>
<div class="uempro-app uempro-wrap">

	<aside class="uempro-sidebar" aria-label="<?php esc_attr_e( 'User Export Manager Pro', 'user-export-manager-pro' ); ?>">
		<a class="uempro-sidebar__brand" href="<?php echo esc_url( uempro_admin_url( 'uempro' ) ); ?>">
			<svg class="uempro-sidebar__logo" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
				<rect width="32" height="32" rx="8" fill="#3d3bc8"/>
				<rect x="7" y="9" width="12" height="2.4" rx="1.2" fill="#fff"/>
				<rect x="7" y="14.8" width="9" height="2.4" rx="1.2" fill="#fff" opacity=".75"/>
				<rect x="7" y="20.6" width="12" height="2.4" rx="1.2" fill="#fff" opacity=".5"/>
				<path d="M21 16h5m-2.2-2.6L26.4 16l-2.6 2.6" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="uempro-sidebar__brand-text">
				<strong><?php esc_html_e( 'User Export', 'user-export-manager-pro' ); ?></strong>
				<span><?php esc_html_e( 'Manager Pro', 'user-export-manager-pro' ); ?></span>
			</span>
		</a>

		<nav class="uempro-sidebar__nav">
			<?php foreach ( $uempro_nav_groups as $uempro_group ) : ?>
				<?php
				$uempro_links = array();
				foreach ( $uempro_group['items'] as $uempro_slug ) {
					if ( isset( $uempro_pages[ $uempro_slug ] ) && current_user_can( $uempro_pages[ $uempro_slug ]['cap'] ) ) {
						$uempro_links[ $uempro_slug ] = $uempro_pages[ $uempro_slug ];
					}
				}
				if ( empty( $uempro_links ) ) {
					continue;
				}
				?>
				<p class="uempro-sidebar__group"><?php echo esc_html( $uempro_group['label'] ); ?></p>
				<?php foreach ( $uempro_links as $uempro_slug => $uempro_item ) : ?>
					<a href="<?php echo esc_url( uempro_admin_url( $uempro_slug ) ); ?>" class="uempro-sidebar__link<?php echo $active === $uempro_slug ? ' is-active' : ''; ?>"<?php echo $active === $uempro_slug ? ' aria-current="page"' : ''; ?>>
						<span class="dashicons <?php echo esc_attr( $uempro_item['icon'] ); ?>" aria-hidden="true"></span>
						<span><?php echo esc_html( $uempro_item['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</nav>

		<div class="uempro-sidebar__footer">
			<div class="uempro-sidebar__card">
				<p><?php esc_html_e( 'Developed by', 'user-export-manager-pro' ); ?></p>
				<strong>Md. Farhan Sadik</strong>
			</div>
			<span class="uempro-sidebar__version">
				<?php
				/* translators: %s: plugin version */
				echo esc_html( sprintf( __( 'Version %s', 'user-export-manager-pro' ), UEMPRO_VERSION ) );
				?>
			</span>
		</div>
	</aside>

	<div class="uempro-main">
		<header class="uempro-topbar">
			<div class="uempro-topbar__title">
				<h1><?php echo esc_html( $title ); ?></h1>
				<p><?php echo esc_html( $subtitle ); ?></p>
			</div>
			<div class="uempro-topbar__actions">
				<?php uempro_get_template( 'admin/partials/actions', array( 'active' => $active ) ); ?>
			</div>
		</header>
		<hr class="wp-header-end">

		<main class="uempro-content">
			<?php
			$uempro_storage = get_transient( UEMPro_Storage_Check::TRANSIENT );
			if ( 'uempro-settings' !== $active && current_user_can( 'manage_options' ) && is_array( $uempro_storage ) && 'exposed' === $uempro_storage['status'] ) :
				?>
				<div class="notice notice-error">
					<p>
						<strong><?php esc_html_e( 'Export files can be downloaded without logging in.', 'user-export-manager-pro' ); ?></strong>
						<?php esc_html_e( 'Your web server ignores the folder’s protection rules (common on Nginx).', 'user-export-manager-pro' ); ?>
						<a href="<?php echo esc_url( uempro_admin_url( 'uempro-settings' ) . '#uempro-storage' ); ?>"><?php esc_html_e( 'See how to fix it', 'user-export-manager-pro' ); ?></a>
					</p>
				</div>
			<?php endif; ?>
			<?php if ( isset( $uempro_notices[ $uempro_notice ] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $uempro_notices[ $uempro_notice ] ); ?></p></div>
			<?php endif; ?>
