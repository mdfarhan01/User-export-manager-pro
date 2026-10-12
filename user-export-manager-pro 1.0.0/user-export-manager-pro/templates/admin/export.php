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
	1 => array( __( 'Users', 'user-export-manager-pro' ), __( 'Who to include', 'user-export-manager-pro' ), 'dashicons-groups', __( 'Who should be included?', 'user-export-manager-pro' ), __( 'Export everyone, a role, a registration period or hand-picked people. The counter below updates as you choose.', 'user-export-manager-pro' ) ),
	2 => array( __( 'Fields', 'user-export-manager-pro' ), __( 'Choose columns', 'user-export-manager-pro' ), 'dashicons-columns', __( 'Which fields become columns?', 'user-export-manager-pro' ), __( 'Tick the profile and custom fields you need. Each ticked field becomes a column, in the order shown at the bottom.', 'user-export-manager-pro' ) ),
	3 => array( __( 'Format', 'user-export-manager-pro' ), __( 'File type & name', 'user-export-manager-pro' ), 'dashicons-media-spreadsheet', __( 'Choose a file format', 'user-export-manager-pro' ), __( 'Pick the format that suits where the file is going, and name it.', 'user-export-manager-pro' ) ),
	4 => array( __( 'Review & export', 'user-export-manager-pro' ), __( 'Check and download', 'user-export-manager-pro' ), 'dashicons-yes-alt', __( 'Review and export', 'user-export-manager-pro' ), __( 'Check everything, optionally save it as a template, then run the export.', 'user-export-manager-pro' ) ),
);

/**
 * Print the header of a wizard step.
 *
 * @param int   $n     Step number.
 * @param array $steps Step definitions.
 */
$uempro_step_head = static function ( $n, $steps ) {
	?>
	<div class="uempro-step__head">
		<span class="uempro-step__icon" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $steps[ $n ][2] ); ?>"></span></span>
		<div>
			<p class="uempro-step__kicker">
				<?php
				/* translators: 1: current step number, 2: number of steps */
				echo esc_html( sprintf( __( 'Step %1$s of %2$s', 'user-export-manager-pro' ), $n, count( $steps ) ) );
				?>
			</p>
			<h2><?php echo esc_html( $steps[ $n ][3] ); ?></h2>
			<p class="uempro-step__desc"><?php echo esc_html( $steps[ $n ][4] ); ?></p>
		</div>
	</div>
	<?php
};
?>

<div class="uempro-banner" id="uempro-template-banner" hidden>
	<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
	<p data-banner-text></p>
	<button type="button" class="uempro-btn uempro-btn--primary uempro-btn--sm" data-banner-go><?php esc_html_e( 'Go to export', 'user-export-manager-pro' ); ?></button>
	<button type="button" class="uempro-link" data-banner-clear><?php esc_html_e( 'Start blank instead', 'user-export-manager-pro' ); ?></button>
</div>

<div class="uempro-wizard" id="uempro-wizard">
	<ol class="uempro-stepper uempro-stepper--rail">
		<?php foreach ( $uempro_steps as $uempro_n => $uempro_step ) : ?>
			<li>
				<button type="button" class="uempro-stepper__btn <?php echo 1 === $uempro_n ? 'is-current' : ''; ?>" data-goto="<?php echo esc_attr( $uempro_n ); ?>" <?php echo 1 === $uempro_n ? 'aria-current="step"' : 'disabled'; ?>>
					<span class="uempro-stepper__num"><span><?php echo esc_html( $uempro_n ); ?></span></span>
					<span class="uempro-stepper__text">
						<span class="uempro-stepper__label"><?php echo esc_html( $uempro_step[0] ); ?></span>
						<span class="uempro-stepper__meta" data-step-meta="<?php echo esc_attr( $uempro_n ); ?>"><?php echo esc_html( $uempro_step[1] ); ?></span>
					</span>
				</button>
			</li>
		<?php endforeach; ?>
	</ol>

	<section class="uempro-panel uempro-step" data-step="1">
		<?php $uempro_step_head( 1, $uempro_steps ); ?>
		<?php
		$uempro_scope_id    = 'uempro-wiz';
		$uempro_users_table = true;
		require UEMPRO_PATH . 'templates/admin/partials/scope.php';
		?>
		<div class="uempro-step__foot">
			<span class="uempro-matchcount" data-match-count aria-live="polite"></span>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Continue to fields', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="2" hidden>
		<?php $uempro_step_head( 2, $uempro_steps ); ?>
		<?php
		$uempro_picker_id = 'uempro-wiz-fields';
		$uempro_selected  = UEMPro_Fields::default_selection();
		require UEMPRO_PATH . 'templates/admin/partials/field-picker.php';
		?>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Continue to format', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="3" hidden>
		<?php $uempro_step_head( 3, $uempro_steps ); ?>
		<?php
		$uempro_format_id = 'uempro-wiz-format';
		require UEMPRO_PATH . 'templates/admin/partials/format.php';
		?>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Review export', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="4" hidden>
		<?php $uempro_step_head( 4, $uempro_steps ); ?>

		<div class="uempro-reviewwrap" data-review></div>

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
