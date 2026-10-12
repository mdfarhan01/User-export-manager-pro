<?php
/**
 * Templates screen with create / edit / delete.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_templates = UEMPro_Templates::all();
$uempro_labels    = UEMPro_Fields::all_labels();
?>

<div class="uempro-panel uempro-empty uempro-empty--large" data-tpl-empty <?php echo empty( $uempro_templates ) ? '' : 'hidden'; ?>>
	<h2><?php esc_html_e( 'No templates yet', 'user-export-manager-pro' ); ?></h2>
	<p><?php esc_html_e( 'For example: “Subscribers — username, email, last name and education”. Create it once, then export it whenever you need a fresh copy.', 'user-export-manager-pro' ); ?></p>
	<button type="button" class="uempro-btn uempro-btn--primary" data-tpl-create><?php esc_html_e( 'Create a template', 'user-export-manager-pro' ); ?></button>
</div>

<div class="uempro-tplgrid" data-tpl-grid>
	<?php foreach ( $uempro_templates as $uempro_t ) : ?>
		<?php
		$uempro_field_count = count( $uempro_t['fields'] );
		$uempro_preview     = array_slice( $uempro_t['fields'], 0, 6 );
		?>
		<article class="uempro-tpl" data-tpl-id="<?php echo esc_attr( $uempro_t['id'] ); ?>">
			<header class="uempro-tpl__head">
				<h2><?php echo esc_html( $uempro_t['name'] ); ?></h2>
				<?php UEMPro_Helper::format_badge( $uempro_t['format'] ); ?>
			</header>
			<?php if ( '' !== $uempro_t['description'] ) : ?>
				<p class="uempro-tpl__desc"><?php echo esc_html( $uempro_t['description'] ); ?></p>
			<?php endif; ?>
			<p class="uempro-tpl__scope"><span class="dashicons dashicons-groups" aria-hidden="true"></span><?php echo esc_html( $uempro_t['summary'] ); ?></p>
			<ul class="uempro-tpl__fields" aria-label="<?php esc_attr_e( 'Columns', 'user-export-manager-pro' ); ?>">
				<?php foreach ( $uempro_preview as $uempro_key ) : ?>
					<li><?php echo esc_html( isset( $uempro_labels[ $uempro_key ] ) ? $uempro_labels[ $uempro_key ] : $uempro_key ); ?></li>
				<?php endforeach; ?>
				<?php if ( $uempro_field_count > count( $uempro_preview ) ) : ?>
					<li class="is-more">
						<?php
						/* translators: %s: number of extra fields */
						echo esc_html( sprintf( __( '+%s more', 'user-export-manager-pro' ), number_format_i18n( $uempro_field_count - count( $uempro_preview ) ) ) );
						?>
					</li>
				<?php endif; ?>
			</ul>
			<footer class="uempro-tpl__foot">
				<button type="button" class="uempro-btn uempro-btn--primary uempro-btn--sm" data-uempro-quick="<?php echo esc_attr( $uempro_t['id'] ); ?>"><?php esc_html_e( 'Export now', 'user-export-manager-pro' ); ?></button>
				<a class="uempro-btn uempro-btn--ghost uempro-btn--sm" href="<?php echo esc_url( uempro_admin_url( 'uempro-export', array( 'template' => $uempro_t['id'] ) ) ); ?>"><?php esc_html_e( 'Open in wizard', 'user-export-manager-pro' ); ?></a>
				<span class="uempro-tpl__tools">
					<button type="button" class="uempro-iconbtn" data-tpl-edit="<?php echo esc_attr( $uempro_t['id'] ); ?>" title="<?php esc_attr_e( 'Edit', 'user-export-manager-pro' ); ?>"><span class="dashicons dashicons-edit" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Edit', 'user-export-manager-pro' ); ?></span></button>
					<button type="button" class="uempro-iconbtn" data-tpl-duplicate="<?php echo esc_attr( $uempro_t['id'] ); ?>" title="<?php esc_attr_e( 'Duplicate', 'user-export-manager-pro' ); ?>"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Duplicate', 'user-export-manager-pro' ); ?></span></button>
					<button type="button" class="uempro-iconbtn uempro-iconbtn--danger" data-tpl-delete="<?php echo esc_attr( $uempro_t['id'] ); ?>" title="<?php esc_attr_e( 'Delete', 'user-export-manager-pro' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Delete', 'user-export-manager-pro' ); ?></span></button>
				</span>
			</footer>
		</article>
	<?php endforeach; ?>
</div>

<div class="uempro-modal" id="uempro-tpl-editor" hidden>
	<div class="uempro-modal__backdrop" data-modal-close></div>
	<div class="uempro-modal__dialog uempro-modal__dialog--wide" role="dialog" aria-modal="true" aria-labelledby="uempro-tpl-editor-title">
		<header class="uempro-modal__head">
			<h2 id="uempro-tpl-editor-title" data-editor-title><?php esc_html_e( 'New template', 'user-export-manager-pro' ); ?></h2>
			<button type="button" class="uempro-iconbtn" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'user-export-manager-pro' ); ?>"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>
		</header>
		<div class="uempro-modal__body">
			<section class="uempro-editor__section">
				<h3><?php esc_html_e( 'Name', 'user-export-manager-pro' ); ?></h3>
				<div class="uempro-editor__row">
					<div class="uempro-field">
						<label for="uempro-tpl-name"><?php esc_html_e( 'Template name', 'user-export-manager-pro' ); ?></label>
						<input type="text" id="uempro-tpl-name" class="uempro-input" maxlength="191" data-editor-name placeholder="<?php esc_attr_e( 'e.g. Subscribers — login & education', 'user-export-manager-pro' ); ?>">
					</div>
					<div class="uempro-field">
						<label for="uempro-tpl-desc"><?php esc_html_e( 'Note (optional)', 'user-export-manager-pro' ); ?></label>
						<input type="text" id="uempro-tpl-desc" class="uempro-input" data-editor-desc placeholder="<?php esc_attr_e( 'Who uses this export and why', 'user-export-manager-pro' ); ?>">
					</div>
				</div>
			</section>
			<section class="uempro-editor__section">
				<h3><?php esc_html_e( 'Users', 'user-export-manager-pro' ); ?></h3>
				<?php
				$uempro_scope_id    = 'uempro-tpl';
				$uempro_users_table = false;
				require UEMPRO_PATH . 'templates/admin/partials/scope.php';
				?>
			</section>
			<section class="uempro-editor__section">
				<h3><?php esc_html_e( 'Fields', 'user-export-manager-pro' ); ?></h3>
				<?php
				$uempro_picker_id = 'uempro-tpl-fields';
				$uempro_selected  = array();
				require UEMPRO_PATH . 'templates/admin/partials/field-picker.php';
				?>
			</section>
			<section class="uempro-editor__section">
				<h3><?php esc_html_e( 'File', 'user-export-manager-pro' ); ?></h3>
				<?php
				$uempro_format_id = 'uempro-tpl-format';
				require UEMPRO_PATH . 'templates/admin/partials/format.php';
				?>
			</section>
		</div>
		<footer class="uempro-modal__foot">
			<span class="uempro-step__error" data-editor-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--ghost" data-modal-close><?php esc_html_e( 'Cancel', 'user-export-manager-pro' ); ?></button>
			<button type="button" class="uempro-btn uempro-btn--primary" data-editor-save><?php esc_html_e( 'Save template', 'user-export-manager-pro' ); ?></button>
		</footer>
	</div>
</div>
