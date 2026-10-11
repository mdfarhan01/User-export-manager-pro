<?php
/**
 * Import users screen (upload → map columns → options → run).
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_settings = uempro_get_settings();
$uempro_roles    = get_editable_roles();
$uempro_default  = get_option( 'default_role', 'subscriber' );
$uempro_steps    = array(
	1 => __( 'Upload file', 'user-export-manager-pro' ),
	2 => __( 'Map columns', 'user-export-manager-pro' ),
	3 => __( 'Options', 'user-export-manager-pro' ),
	4 => __( 'Import', 'user-export-manager-pro' ),
);
?>
<div class="uempro-wizard uempro-import" id="uempro-import">
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
			<h2><?php esc_html_e( 'Upload a file', 'user-export-manager-pro' ); ?></h2>
		</div>
		<div class="uempro-step__body">
			<label class="uempro-drop" data-drop>
				<input type="file" class="screen-reader-text" data-file accept=".csv,.tsv,.txt,.xlsx,.json,.xml,text/csv,application/json,application/xml,text/xml,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
				<span class="uempro-drop__icon" aria-hidden="true"><span class="dashicons dashicons-media-spreadsheet"></span></span>
				<strong data-drop-title><?php esc_html_e( 'Drop your file here, or click to browse', 'user-export-manager-pro' ); ?></strong>
				<span class="uempro-drop__hint">
					<?php
					/* translators: %s: maximum upload size, e.g. "64 MB" */
					echo esc_html( sprintf( __( 'CSV, Excel (.xlsx), JSON or XML · up to %s', 'user-export-manager-pro' ), size_format( wp_max_upload_size() ) ) );
					?>
				</span>
				<span class="uempro-drop__formats" aria-hidden="true">
					<?php
					foreach ( array( 'csv', 'xlsx', 'json', 'xml' ) as $uempro_f ) {
						UEMPro_Helper::format_badge( $uempro_f );
					}
					?>
				</span>
			</label>

			<div class="uempro-callout">
				<p><strong><?php esc_html_e( 'Files exported by this plugin import as-is.', 'user-export-manager-pro' ); ?></strong> <?php esc_html_e( 'Their columns are matched to the right fields automatically. Files from other tools work too — the first row must contain column names.', 'user-export-manager-pro' ); ?></p>
			</div>
		</div>
		<div class="uempro-step__foot">
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<span class="uempro-upload-status" data-upload-status aria-live="polite"></span>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="2" hidden>
		<div class="uempro-step__head uempro-step__head--split">
			<h2><?php esc_html_e( 'Match columns to fields', 'user-export-manager-pro' ); ?></h2>
			<span class="uempro-filechip" data-file-chip></span>
		</div>
		<div class="uempro-step__body">
			<p class="uempro-help uempro-help--lead"><?php esc_html_e( 'Each column in your file is imported into the field on the right. Columns set to “Don’t import” are ignored.', 'user-export-manager-pro' ); ?></p>
			<div class="uempro-tablewrap">
				<table class="uempro-table uempro-table--map">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Column in your file', 'user-export-manager-pro' ); ?></th>
							<th><?php esc_html_e( 'Example values', 'user-export-manager-pro' ); ?></th>
							<th><?php esc_html_e( 'Import into', 'user-export-manager-pro' ); ?></th>
						</tr>
					</thead>
					<tbody data-map-body></tbody>
				</table>
			</div>
		</div>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-restart><?php esc_html_e( 'Choose a different file', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-matchcount" data-mapped-count></span>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Continue to options', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="3" hidden>
		<div class="uempro-step__head">
			<h2><?php esc_html_e( 'Import options', 'user-export-manager-pro' ); ?></h2>
		</div>
		<div class="uempro-step__body uempro-settings">
			<div class="uempro-setting">
				<div class="uempro-setting__text">
					<span class="uempro-setting__label"><?php esc_html_e( 'Find existing users by', 'user-export-manager-pro' ); ?></span>
					<p><?php esc_html_e( 'If no match is found by this field, email and username are tried next.', 'user-export-manager-pro' ); ?></p>
				</div>
				<div class="uempro-segmented" role="radiogroup">
					<?php
					foreach ( array(
						'email' => __( 'Email', 'user-export-manager-pro' ),
						'login' => __( 'Username', 'user-export-manager-pro' ),
						'id'    => __( 'User ID', 'user-export-manager-pro' ),
					) as $uempro_value => $uempro_label ) :
						?>
						<label><input type="radio" name="uempro-imp-match" value="<?php echo esc_attr( $uempro_value ); ?>" <?php checked( $uempro_settings['import_match_by'], $uempro_value ); ?>><span><?php echo esc_html( $uempro_label ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="uempro-setting">
				<div class="uempro-setting__text">
					<span class="uempro-setting__label"><?php esc_html_e( 'When a user already exists', 'user-export-manager-pro' ); ?></span>
					<p><?php esc_html_e( 'Update overwrites their profile with values from the file. Usernames and registration dates never change.', 'user-export-manager-pro' ); ?></p>
				</div>
				<div class="uempro-segmented" role="radiogroup">
					<label><input type="radio" name="uempro-imp-existing" value="update" <?php checked( $uempro_settings['import_existing'], 'update' ); ?>><span><?php esc_html_e( 'Update them', 'user-export-manager-pro' ); ?></span></label>
					<label><input type="radio" name="uempro-imp-existing" value="skip" <?php checked( $uempro_settings['import_existing'], 'skip' ); ?>><span><?php esc_html_e( 'Skip them', 'user-export-manager-pro' ); ?></span></label>
				</div>
			</div>
			<div class="uempro-setting">
				<div class="uempro-setting__text">
					<label for="uempro-imp-create"><?php esc_html_e( 'Create new users', 'user-export-manager-pro' ); ?></label>
					<p><?php esc_html_e( 'Rows that don’t match anyone become new accounts. Without a password column a strong random password is set.', 'user-export-manager-pro' ); ?></p>
				</div>
				<span class="uempro-switch"><input type="checkbox" id="uempro-imp-create" data-opt-create checked><span aria-hidden="true"></span></span>
			</div>
			<div class="uempro-setting">
				<div class="uempro-setting__text">
					<label for="uempro-imp-role"><?php esc_html_e( 'Role for new users', 'user-export-manager-pro' ); ?></label>
					<p><?php esc_html_e( 'Used when the file has no Role column or the cell is empty.', 'user-export-manager-pro' ); ?></p>
				</div>
				<select id="uempro-imp-role" class="uempro-select" data-opt-role>
					<?php foreach ( $uempro_roles as $uempro_key => $uempro_role ) : ?>
						<option value="<?php echo esc_attr( $uempro_key ); ?>" <?php selected( $uempro_default, $uempro_key ); ?>><?php echo esc_html( translate_user_role( $uempro_role['name'] ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="uempro-setting">
				<div class="uempro-setting__text">
					<label for="uempro-imp-empty"><?php esc_html_e( 'Ignore empty cells', 'user-export-manager-pro' ); ?></label>
					<p><?php esc_html_e( 'Recommended. When off, an empty cell clears that field for existing users.', 'user-export-manager-pro' ); ?></p>
				</div>
				<span class="uempro-switch"><input type="checkbox" id="uempro-imp-empty" data-opt-empty checked><span aria-hidden="true"></span></span>
			</div>
			<div class="uempro-setting">
				<div class="uempro-setting__text">
					<label for="uempro-imp-email"><?php esc_html_e( 'Email new users their login details', 'user-export-manager-pro' ); ?></label>
					<p><?php esc_html_e( 'Sends the standard WordPress “set your password” email to each new account.', 'user-export-manager-pro' ); ?></p>
				</div>
				<span class="uempro-switch"><input type="checkbox" id="uempro-imp-email" data-opt-email <?php checked( 1, (int) $uempro_settings['import_send_email'] ); ?>><span aria-hidden="true"></span></span>
			</div>
		</div>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary" data-next><?php esc_html_e( 'Review import', 'user-export-manager-pro' ); ?></button>
		</div>
	</section>

	<section class="uempro-panel uempro-step" data-step="4" hidden>
		<div class="uempro-step__head">
			<h2><?php esc_html_e( 'Review and import', 'user-export-manager-pro' ); ?></h2>
		</div>
		<div class="uempro-step__body">
			<dl class="uempro-review" data-review></dl>
			<div class="uempro-callout uempro-callout--warn">
				<p><?php esc_html_e( 'Importing changes user accounts and cannot be undone. Export your users first if you might need to roll back.', 'user-export-manager-pro' ); ?></p>
			</div>
			<div class="uempro-progress" data-progress hidden></div>
			<div class="uempro-importresult" data-result hidden></div>
		</div>
		<div class="uempro-step__foot">
			<button type="button" class="uempro-btn uempro-btn--ghost" data-prev><?php esc_html_e( 'Back', 'user-export-manager-pro' ); ?></button>
			<span class="uempro-step__error" data-step-error role="alert"></span>
			<button type="button" class="uempro-btn uempro-btn--primary uempro-btn--lg" data-run><span class="dashicons dashicons-download" aria-hidden="true"></span><span data-run-label><?php esc_html_e( 'Start import', 'user-export-manager-pro' ); ?></span></button>
		</div>
	</section>
</div>
