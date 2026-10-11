<?php
/**
 * Export wizard.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_templates = UEMPro_Templates::all();
$uempro_steps     = array(
	1 => __( 'Users', 'user-export-manager-pro' ),
	2 => __( 'Fields', 'user-export-manager-pro' ),
	3 => __( 'Format', 'user-export-manager-pro' ),
	4 => __( 'Review & export', 'user-export-manager-pro' ),
);
?>

<div class="uempro-banner" id="uempro-template-banner" hidden>
	<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
	<p data-banner-text></p>
	<button type="button" class="uempro-btn uempro-btn--primary uempro-btn--sm" data-banner-go><?php esc_html_e( 'Go to export', 'user-export-manager-pro' ); ?></button>
	<button type="button" class="uempro-link" data-banner-clear><?php esc_html_e( 'Start blank instead', 'user-export-manager-pro' ); ?></button>
</div>

<div class="uempro-wizard" id="uempro-wizard">
	<ol class="uempro-stepper">
		<?php foreach ( $uempro_steps as $uempro_n => $uempro_label ) : ?>
			<li>
				<button type="button" class="uempro-stepper__btn <?php echo 1 === $uempro_n ? 'is-current' : ''; ?>" data-goto="<?php echo esc_attr( $uempro_n ); ?>" <?php echo 1 === $uempro_n ? 'aria-current="step"' : 'disabled'; ?>>
					<span class="uempro-stepper__num"><?php echo esc_html( $uempro_n ); ?></span>
					<span class="uempro-stepper__label"><?php echo esc_html( $uempro_label ); ?></span>
				</button>
			</li>
		<?php endforeach; ?>
	</ol>

	<section class="uempro-panel uempro-step" data-step="1">
		<div class="uempro-step__head">
			<h2><?php esc_html_e( 'Who should be included?', 'user-export-manager-pro' ); ?></h2>
		</div>
		<?php
		$uempro_scope_id    = 'uempro-wiz';
		$uempro_users_table = true;
		include UEMPRO_PATH . 'templates/admin/partials/scope.php';
		?>
		<div class="uempro-step__foot">
			<span class="uempro-matchcount" data-match-count aria-live="polite"></span>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Continue to fields', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="2" hidden>
		<div class="uempro-step__head">
			<h2><?php esc_html_e( 'Which fields become columns?', 'user-export-manager-pro' ); ?></h2>
		</div>
		<?php
		$uempro_picker_id = 'uempro-wiz-fields';
		$uempro_selected  = UEMPro_Fields::default_selection();
		include UEMPRO_PATH . 'templates/admin/partials/field-picker.php';
		?>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Continue to format', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="3" hidden>
		<div class="uempro-step__head">
			<h2><?php esc_html_e( 'Choose a file format', 'user-export-manager-pro' ); ?></h2>
		</div>
		<?php
		$uempro_format_id = 'uempro-wiz-format';
		include UEMPRO_PATH . 'templates/admin/partials/format.php';
		?>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Review export', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="4" hidden>
		<div class="uempro-step__head">
			<h2><?php esc_html_e( 'Review and export', 'user-export-manager-pro' ); ?></h2>
		</div>

		<dl class="uempro-review" data-review></dl>

		<div class="uempro-savebox" data-savebox>
			<div class="uempro-savebox__text">
				<h3><?php esc_html_e( 'Save these settings as a template', 'user-export-manager-pro' ); ?></h3>
				<p><?php esc_html_e( 'Next time, run the same users, fields and format with one click.', 'user-export-manager-pro' ); ?></p>
			</div>
			<div class="uempro-savebox__actions" data-savebox-actions>
				<button type="button" class="uempro-btn uempro-btn--ghost" data-tpl-update hidden></button>
				<button type="button" class="uempro-btn uempro-btn--ghost" data-tpl-new><?php esc_html_e( 'Save as new template', 'user-export-manager-pro' ); ?></button>
			</div>
			<div class="uempro-savebox__form" data-savebox-form hidden>
				<label class="screen-reader-text" for="uempro-wiz-tplname"><?php esc_html_e( 'Template name', 'user-export-manager-pro' ); ?></label>
				<input type="text" id="uempro-wiz-tplname" class="uempro-input" maxlength="191" placeholder="<?php esc_attr_e( 'Template name, e.g. Subscribers — login & education', 'user-export-manager-pro' ); ?>">
				<button type="button" class="uempro-btn uempro-btn--primary" data-tpl-save><?php esc_html_e( 'Save template', 'user-export-manager-pro' ); ?></button>
				<button type="button" class="uempro-link" data-tpl-cancel><?php esc_html_e( 'Cancel', 'user-export-manager-pro' ); ?></button>
			</div>
		</div>

		<div class="uempro-progress" data-progress hidden></div>

		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary uempro-btn--lg" data-run><span class="dashicons dashicons-download" aria-hidden="true"></span><span data-run-label><?php esc_html_e( 'Run export', 'user-export-manager-pro' ); ?></span></button>
		</div>
	</section>
</div>
