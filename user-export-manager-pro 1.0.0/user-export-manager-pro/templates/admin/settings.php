<?php
/**
 * Settings screen.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_s = UEMPro_Settings::get();

/**
 * Print a switch row.
 *
 * @param string $key   Setting key.
 * @param string $label Label.
 * @param string $help  Help text.
 * @param array  $s     Settings.
 */
$uempro_switch = static function ( $key, $label, $help, $s ) {
	?>
	<div class="uempro-setting">
		<div class="uempro-setting__text">
			<label for="uempro-set-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<p><?php echo esc_html( $help ); ?></p>
		</div>
		<span class="uempro-switch">
			<input type="checkbox" id="uempro-set-<?php echo esc_attr( $key ); ?>" name="uempro[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( 1, (int) $s[ $key ] ); ?>>
			<span aria-hidden="true"></span>
		</span>
	</div>
	<?php
};
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="uempro-settings">
	<input type="hidden" name="action" value="uempro_save_settings">
	<?php wp_nonce_field( 'uempro_save_settings', 'uempro_settings_nonce' ); ?>

	<section class="uempro-panel">
		<div class="uempro-panel__head"><h2><?php esc_html_e( 'Export defaults', 'user-export-manager-pro' ); ?></h2></div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-format"><?php esc_html_e( 'Default format', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Pre-selected in the wizard and for new templates.', 'user-export-manager-pro' ); ?></p>
			</div>
			<select id="uempro-set-format" name="uempro[default_format]" class="uempro-select">
				<?php foreach ( UEMPro_Export_Writer::formats() as $uempro_key => $uempro_f ) : ?>
					<option value="<?php echo esc_attr( $uempro_key ); ?>" <?php selected( $uempro_s['default_format'], $uempro_key ); ?>><?php echo esc_html( $uempro_f['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-filename"><?php esc_html_e( 'Default file name', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Letters, numbers, dots, dashes and underscores.', 'user-export-manager-pro' ); ?></p>
			</div>
			<input type="text" id="uempro-set-filename" name="uempro[default_filename]" class="uempro-input" value="<?php echo esc_attr( $uempro_s['default_filename'] ); ?>" maxlength="80">
		</div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-batch"><?php esc_html_e( 'Users per batch', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Exports run in batches so large sites never time out. Lower this on slow hosting; raise it on fast servers. 25–2000.', 'user-export-manager-pro' ); ?></p>
			</div>
			<input type="number" id="uempro-set-batch" name="uempro[batch_size]" class="uempro-input uempro-input--short" value="<?php echo esc_attr( $uempro_s['batch_size'] ); ?>" min="25" max="2000" step="25">
		</div>
	</section>

	<section class="uempro-panel">
		<div class="uempro-panel__head"><h2><?php esc_html_e( 'File output', 'user-export-manager-pro' ); ?></h2></div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-delimiter"><?php esc_html_e( 'CSV separator', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Many European versions of Excel expect a semicolon.', 'user-export-manager-pro' ); ?></p>
			</div>
			<select id="uempro-set-delimiter" name="uempro[csv_delimiter]" class="uempro-select">
				<option value="comma" <?php selected( $uempro_s['csv_delimiter'], 'comma' ); ?>><?php esc_html_e( 'Comma ( , )', 'user-export-manager-pro' ); ?></option>
				<option value="semicolon" <?php selected( $uempro_s['csv_delimiter'], 'semicolon' ); ?>><?php esc_html_e( 'Semicolon ( ; )', 'user-export-manager-pro' ); ?></option>
				<option value="tab" <?php selected( $uempro_s['csv_delimiter'], 'tab' ); ?>><?php esc_html_e( 'Tab', 'user-export-manager-pro' ); ?></option>
			</select>
		</div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-date"><?php esc_html_e( 'Registered date format', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Raw keeps the database value (UTC, sortable). Site format uses your WordPress date and time settings and timezone.', 'user-export-manager-pro' ); ?></p>
			</div>
			<select id="uempro-set-date" name="uempro[date_format]" class="uempro-select">
				<option value="raw" <?php selected( $uempro_s['date_format'], 'raw' ); ?>><?php esc_html_e( 'Raw — 2026-03-14 09:30:00', 'user-export-manager-pro' ); ?></option>
				<option value="site" <?php selected( $uempro_s['date_format'], 'site' ); ?>><?php esc_html_e( 'Site date & time format', 'user-export-manager-pro' ); ?></option>
			</select>
		</div>
		<?php
		$uempro_switch( 'csv_bom', __( 'Add UTF-8 marker to CSV', 'user-export-manager-pro' ), __( 'Makes Excel display accented and non-Latin characters (e.g. বাংলা, é, ü) correctly.', 'user-export-manager-pro' ), $uempro_s );
		$uempro_switch( 'formula_protection', __( 'Protect against spreadsheet formulas', 'user-export-manager-pro' ), __( 'Values that start with =, +, - or @ are prefixed with an apostrophe so a malicious profile value cannot run as a formula in Excel.', 'user-export-manager-pro' ), $uempro_s );
		?>
	</section>

	<section class="uempro-panel">
		<div class="uempro-panel__head"><h2><?php esc_html_e( 'History', 'user-export-manager-pro' ); ?></h2></div>
		<?php $uempro_switch( 'enable_logs', __( 'Keep export history', 'user-export-manager-pro' ), __( 'Record each export on the History screen so the file can be downloaded again.', 'user-export-manager-pro' ), $uempro_s ); ?>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-retention"><?php esc_html_e( 'Delete exports after', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Old files and history entries are removed once a day. Use 0 to keep them forever.', 'user-export-manager-pro' ); ?></p>
			</div>
			<span class="uempro-inline">
				<input type="number" id="uempro-set-retention" name="uempro[retention_days]" class="uempro-input uempro-input--short" value="<?php echo esc_attr( $uempro_s['retention_days'] ); ?>" min="0" max="3650">
				<span><?php esc_html_e( 'days', 'user-export-manager-pro' ); ?></span>
			</span>
		</div>
	</section>

	<section class="uempro-panel">
		<div class="uempro-panel__head"><h2><?php esc_html_e( 'Import defaults', 'user-export-manager-pro' ); ?></h2></div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-match"><?php esc_html_e( 'Find existing users by', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Pre-selected on the Import screen.', 'user-export-manager-pro' ); ?></p>
			</div>
			<select id="uempro-set-match" name="uempro[import_match_by]" class="uempro-select">
				<option value="email" <?php selected( $uempro_s['import_match_by'], 'email' ); ?>><?php esc_html_e( 'Email', 'user-export-manager-pro' ); ?></option>
				<option value="login" <?php selected( $uempro_s['import_match_by'], 'login' ); ?>><?php esc_html_e( 'Username', 'user-export-manager-pro' ); ?></option>
				<option value="id" <?php selected( $uempro_s['import_match_by'], 'id' ); ?>><?php esc_html_e( 'User ID', 'user-export-manager-pro' ); ?></option>
			</select>
		</div>
		<div class="uempro-setting">
			<div class="uempro-setting__text">
				<label for="uempro-set-existing"><?php esc_html_e( 'When a user already exists', 'user-export-manager-pro' ); ?></label>
				<p><?php esc_html_e( 'Default choice for rows that match an existing account.', 'user-export-manager-pro' ); ?></p>
			</div>
			<select id="uempro-set-existing" name="uempro[import_existing]" class="uempro-select">
				<option value="update" <?php selected( $uempro_s['import_existing'], 'update' ); ?>><?php esc_html_e( 'Update them', 'user-export-manager-pro' ); ?></option>
				<option value="skip" <?php selected( $uempro_s['import_existing'], 'skip' ); ?>><?php esc_html_e( 'Skip them', 'user-export-manager-pro' ); ?></option>
			</select>
		</div>
		<?php $uempro_switch( 'import_send_email', __( 'Email new users their login details', 'user-export-manager-pro' ), __( 'Default for the matching option on the Import screen.', 'user-export-manager-pro' ), $uempro_s ); ?>
	</section>

	<section class="uempro-panel">
		<div class="uempro-panel__head"><h2><?php esc_html_e( 'Privacy & data', 'user-export-manager-pro' ); ?></h2></div>
		<?php
		$uempro_switch( 'allow_password_hash', __( 'Allow exporting password hashes', 'user-export-manager-pro' ), __( 'Adds a “Password hash” field to exports and imports for administrators. WordPress never stores readable passwords — the hash lets users keep their password when you move them to another WordPress site. Keep this off unless you need it.', 'user-export-manager-pro' ), $uempro_s );
		$uempro_switch( 'delete_data_on_uninstall', __( 'Delete all plugin data on uninstall', 'user-export-manager-pro' ), __( 'Removes templates, history, export files and settings when the plugin is deleted from the Plugins screen.', 'user-export-manager-pro' ), $uempro_s );
		?>
	</section>

	<?php
	$uempro_storage = UEMPro_Storage_Check::result();
	$uempro_states  = array(
		'protected' => array( 'yes-alt', 'ok', __( 'Protected', 'user-export-manager-pro' ), __( 'An anonymous request for a file in the storage folder was refused, so export files cannot be downloaded without logging in.', 'user-export-manager-pro' ) ),
		'outside'   => array( 'yes-alt', 'ok', __( 'Outside the web root', 'user-export-manager-pro' ), __( 'Files are stored in a folder the web server does not serve. This is the safest setup.', 'user-export-manager-pro' ) ),
		'exposed'   => array( 'warning', 'danger', __( 'Publicly accessible', 'user-export-manager-pro' ), __( 'Your web server ignores the folder’s .htaccess / web.config rules (this is normal on Nginx). The folder name and file names are long random codes, but you should block the folder with the rule below, or move storage outside the web root.', 'user-export-manager-pro' ) ),
		'unknown'   => array( 'info-outline', 'warn', __( 'Could not be verified', 'user-export-manager-pro' ), __( 'The check could not prove that the folder is blocked — the reason is shown below. Add the rule below if you use Nginx, or keep files outside the web root, then click “Check again”.', 'user-export-manager-pro' ) ),
	);
	$uempro_state   = isset( $uempro_states[ $uempro_storage['status'] ] ) ? $uempro_states[ $uempro_storage['status'] ] : $uempro_states['unknown'];
	?>
	<section class="uempro-panel" id="uempro-storage">
		<div class="uempro-panel__head"><h2><?php esc_html_e( 'File storage security', 'user-export-manager-pro' ); ?></h2></div>
		<div class="uempro-storage uempro-storage--<?php echo esc_attr( $uempro_state[1] ); ?>">
			<span class="uempro-storage__icon dashicons dashicons-<?php echo esc_attr( $uempro_state[0] ); ?>" aria-hidden="true"></span>
			<div class="uempro-storage__text">
				<strong><?php echo esc_html( $uempro_state[2] ); ?></strong>
				<p><?php echo esc_html( $uempro_state[3] ); ?></p>
				<?php if ( '' !== $uempro_storage['detail'] && 'protected' !== $uempro_storage['status'] ) : ?>
					<p class="uempro-muted"><?php echo esc_html( $uempro_storage['detail'] ); ?></p>
				<?php endif; ?>
			</div>
			<a class="uempro-btn uempro-btn--ghost uempro-btn--sm" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=uempro_storage_recheck' ), 'uempro_storage_recheck' ) ); ?>"><?php esc_html_e( 'Check again', 'user-export-manager-pro' ); ?></a>
		</div>
		<dl class="uempro-storage__facts">
			<dt><?php esc_html_e( 'Folder', 'user-export-manager-pro' ); ?></dt>
			<dd><code><?php echo esc_html( UEMPro_Install::export_dir() ); ?></code></dd>
			<dt><?php esc_html_e( 'Last checked', 'user-export-manager-pro' ); ?></dt>
			<dd>
				<?php
				/* translators: %s: human time difference, e.g. "5 mins" */
				echo esc_html( sprintf( __( '%s ago', 'user-export-manager-pro' ), human_time_diff( (int) $uempro_storage['checked'], time() ) ) );
				?>
			</dd>
		</dl>
		<?php if ( in_array( $uempro_storage['status'], array( 'exposed', 'unknown' ), true ) ) : ?>
			<p class="uempro-storage__label"><?php esc_html_e( 'Nginx: add this to your server block, then reload Nginx', 'user-export-manager-pro' ); ?></p>
			<pre class="uempro-code"><code><?php echo esc_html( UEMPro_Storage_Check::nginx_rule() ); ?></code></pre>
		<?php endif; ?>
		<?php if ( 'outside' !== $uempro_storage['status'] ) : ?>
			<p class="uempro-storage__label"><?php esc_html_e( 'Or keep files outside the web root — add this to wp-config.php (the folder must be writable by PHP):', 'user-export-manager-pro' ); ?></p>
			<pre class="uempro-code"><code>define( 'UEMPRO_EXPORT_DIR', '<?php echo esc_html( wp_normalize_path( dirname( untrailingslashit( ABSPATH ) ) ) ); ?>/uempro-private' );</code></pre>
		<?php endif; ?>
	</section>

	<div class="uempro-settings__foot">
		<button type="submit" class="uempro-btn uempro-btn--primary"><?php esc_html_e( 'Save settings', 'user-export-manager-pro' ); ?></button>
	</div>
</form>
