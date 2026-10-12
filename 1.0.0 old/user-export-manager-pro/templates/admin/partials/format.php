<?php
/**
 * Format + filename picker.
 *
 * @package User_Export_Manager_Pro
 *
 * @var string $uempro_format_id Unique id prefix.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_settings = UEMPro_Settings::get();
$uempro_has_zip  = class_exists( 'ZipArchive' );
$uempro_descs    = array(
	'csv'  => __( 'Opens in Excel, Google Sheets and Numbers. Best for imports into other tools.', 'user-export-manager-pro' ),
	'xlsx' => __( 'A real Excel workbook with a styled header row, filters and frozen headings.', 'user-export-manager-pro' ),
	'json' => __( 'An array of user objects for developers, scripts and APIs.', 'user-export-manager-pro' ),
	'xml'  => __( 'Structured markup for reporting systems that expect XML.', 'user-export-manager-pro' ),
);
?>
<div class="uempro-formatpick" data-uempro-format data-format-id="<?php echo esc_attr( $uempro_format_id ); ?>">
	<fieldset class="uempro-formats">
		<legend class="screen-reader-text"><?php esc_html_e( 'File format', 'user-export-manager-pro' ); ?></legend>
		<?php foreach ( UEMPro_Export_Writer::formats() as $uempro_key => $uempro_format ) : ?>
			<?php $uempro_disabled = ( 'xlsx' === $uempro_key && ! $uempro_has_zip ); ?>
			<label class="uempro-format uempro-format--<?php echo esc_attr( $uempro_key ); ?> <?php echo $uempro_disabled ? 'is-disabled' : ''; ?>">
				<input type="radio" name="<?php echo esc_attr( $uempro_format_id ); ?>-format" value="<?php echo esc_attr( $uempro_key ); ?>" <?php checked( $uempro_settings['default_format'], $uempro_key ); ?> <?php disabled( $uempro_disabled ); ?>>
				<span class="uempro-format__ext">.<?php echo esc_html( $uempro_key ); ?></span>
				<strong><?php echo esc_html( $uempro_format['label'] ); ?></strong>
				<span class="uempro-format__desc">
					<?php
					echo esc_html( $uempro_disabled ? __( 'Unavailable: the PHP ZipArchive extension is not installed on this server.', 'user-export-manager-pro' ) : $uempro_descs[ $uempro_key ] );
					?>
				</span>
			</label>
		<?php endforeach; ?>
	</fieldset>

	<div class="uempro-field">
		<label for="<?php echo esc_attr( $uempro_format_id ); ?>-filename"><?php esc_html_e( 'File name', 'user-export-manager-pro' ); ?></label>
		<div class="uempro-filename">
			<input type="text" id="<?php echo esc_attr( $uempro_format_id ); ?>-filename" class="uempro-input" data-format-filename value="<?php echo esc_attr( $uempro_settings['default_filename'] ); ?>" maxlength="80" spellcheck="false">
			<span class="uempro-filename__suffix" data-format-suffix>-<?php echo esc_html( wp_date( 'Ymd' ) ); ?>-….<?php echo esc_html( $uempro_settings['default_format'] ); ?></span>
		</div>
		<p class="uempro-help"><?php esc_html_e( 'The date and a short random code are added automatically, so exports never overwrite each other.', 'user-export-manager-pro' ); ?></p>
	</div>
</div>
