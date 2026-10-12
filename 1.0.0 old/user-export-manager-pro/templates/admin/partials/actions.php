<?php
/**
 * Contextual actions shown on the right of the top bar.
 *
 * @var string $active Current page slug.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

switch ( $active ) {
	case 'uempro':
		if ( uempro_can_import() ) :
			?>
			<a class="uempro-btn uempro-btn--ghost" href="<?php echo esc_url( uempro_admin_url( 'uempro-import' ) ); ?>"><span class="dashicons dashicons-download" aria-hidden="true"></span><?php esc_html_e( 'Import users', 'user-export-manager-pro' ); ?></a>
			<?php
		endif;
		?>
		<a class="uempro-btn uempro-btn--primary" href="<?php echo esc_url( uempro_admin_url( 'uempro-export' ) ); ?>"><span class="dashicons dashicons-upload" aria-hidden="true"></span><?php esc_html_e( 'New export', 'user-export-manager-pro' ); ?></a>
		<?php
		break;

	case 'uempro-export':
		$uempro_templates = UEMPro_Templates::all();
		if ( ! empty( $uempro_templates ) ) :
			?>
			<label class="uempro-tplselect">
				<span><?php esc_html_e( 'Start from a template', 'user-export-manager-pro' ); ?></span>
				<select class="uempro-select" id="uempro-template-select">
					<option value=""><?php esc_html_e( 'None — start blank', 'user-export-manager-pro' ); ?></option>
					<?php foreach ( $uempro_templates as $uempro_t ) : ?>
						<option value="<?php echo esc_attr( $uempro_t['id'] ); ?>"><?php echo esc_html( $uempro_t['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php
		endif;
		break;

	case 'uempro-templates':
		?>
		<button type="button" class="uempro-btn uempro-btn--primary" data-tpl-create><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php esc_html_e( 'New template', 'user-export-manager-pro' ); ?></button>
		<?php
		break;

	case 'uempro-history':
		if ( current_user_can( 'manage_options' ) && UEMPro_History::count() ) :
			?>
			<button type="button" class="uempro-btn uempro-btn--danger-ghost" data-history-clear><?php esc_html_e( 'Delete all history', 'user-export-manager-pro' ); ?></button>
			<?php
		endif;
		break;

	case 'uempro-import':
		?>
		<a class="uempro-btn uempro-btn--ghost" href="<?php echo esc_url( uempro_admin_url( 'uempro-docs' ) . '#uempro-doc-import' ); ?>"><span class="dashicons dashicons-editor-help" aria-hidden="true"></span><?php esc_html_e( 'How importing works', 'user-export-manager-pro' ); ?></a>
		<?php
		break;
}
