<?php
/**
 * Dashboard screen.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_counts    = count_users();
$uempro_templates = UEMPro_Templates::all();
$uempro_recent    = UEMPro_History::page( 1, 6 );
$uempro_labels    = UEMPro_Fields::all_labels();
$uempro_stats     = array(
	array( number_format_i18n( $uempro_counts['total_users'] ), __( 'Users on this site', 'user-export-manager-pro' ), 'dashicons-groups', 'indigo' ),
	array( number_format_i18n( UEMPro_Fields::count_custom() ), __( 'Custom fields detected', 'user-export-manager-pro' ), 'dashicons-database', 'teal' ),
	array( number_format_i18n( count( $uempro_templates ) ), __( 'Saved templates', 'user-export-manager-pro' ), 'dashicons-portfolio', 'amber' ),
	array( number_format_i18n( UEMPro_History::count() ), __( 'History entries', 'user-export-manager-pro' ), 'dashicons-backup', 'violet' ),
);
?>

<div class="uempro-stats">
	<?php foreach ( $uempro_stats as $uempro_stat ) : ?>
		<div class="uempro-stat uempro-stat--<?php echo esc_attr( $uempro_stat[3] ); ?>">
			<span class="uempro-stat__icon" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $uempro_stat[2] ); ?>"></span></span>
			<span class="uempro-stat__text">
				<span class="uempro-stat__value"><?php echo esc_html( $uempro_stat[0] ); ?></span>
				<span class="uempro-stat__label"><?php echo esc_html( $uempro_stat[1] ); ?></span>
			</span>
		</div>
	<?php endforeach; ?>
</div>

<div class="uempro-quick">
	<a class="uempro-quick__item" href="<?php echo esc_url( uempro_admin_url( 'uempro-export' ) ); ?>">
		<span class="uempro-quick__icon uempro-quick__icon--indigo" aria-hidden="true"><span class="dashicons dashicons-upload"></span></span>
		<span><strong><?php esc_html_e( 'Export users', 'user-export-manager-pro' ); ?></strong><span><?php esc_html_e( 'Pick users and fields, download CSV, Excel, JSON or XML.', 'user-export-manager-pro' ); ?></span></span>
	</a>
	<?php if ( uempro_can_import() ) : ?>
		<a class="uempro-quick__item" href="<?php echo esc_url( uempro_admin_url( 'uempro-import' ) ); ?>">
			<span class="uempro-quick__icon uempro-quick__icon--teal" aria-hidden="true"><span class="dashicons dashicons-download"></span></span>
			<span><strong><?php esc_html_e( 'Import users', 'user-export-manager-pro' ); ?></strong><span><?php esc_html_e( 'Create or update users from an exported file.', 'user-export-manager-pro' ); ?></span></span>
		</a>
	<?php endif; ?>
	<a class="uempro-quick__item" href="<?php echo esc_url( uempro_admin_url( 'uempro-templates', array( 'new' => 1 ) ) ); ?>">
		<span class="uempro-quick__icon uempro-quick__icon--amber" aria-hidden="true"><span class="dashicons dashicons-plus-alt2"></span></span>
		<span><strong><?php esc_html_e( 'New template', 'user-export-manager-pro' ); ?></strong><span><?php esc_html_e( 'Save a set of users and fields for one-click exports.', 'user-export-manager-pro' ); ?></span></span>
	</a>
</div>

<div class="uempro-split">
	<section class="uempro-panel">
		<div class="uempro-panel__head">
			<h2><?php esc_html_e( 'Run a template', 'user-export-manager-pro' ); ?></h2>
			<a href="<?php echo esc_url( uempro_admin_url( 'uempro-templates' ) ); ?>"><?php esc_html_e( 'Manage templates', 'user-export-manager-pro' ); ?></a>
		</div>
		<?php if ( empty( $uempro_templates ) ) : ?>
			<div class="uempro-empty">
				<p><?php esc_html_e( 'A template remembers who to include, which fields to export and the file format, so a repeat export is a single click.', 'user-export-manager-pro' ); ?></p>
				<a class="uempro-btn uempro-btn--ghost" href="<?php echo esc_url( uempro_admin_url( 'uempro-templates', array( 'new' => 1 ) ) ); ?>"><?php esc_html_e( 'Create your first template', 'user-export-manager-pro' ); ?></a>
			</div>
		<?php else : ?>
			<ul class="uempro-list">
				<?php foreach ( array_slice( $uempro_templates, 0, 6 ) as $uempro_t ) : ?>
					<li class="uempro-list__row">
						<div class="uempro-list__main">
							<strong><?php echo esc_html( $uempro_t['name'] ); ?></strong>
							<span>
								<?php
								/* translators: %s: number of fields */
								echo esc_html( sprintf( _n( '%s field', '%s fields', count( $uempro_t['fields'] ), 'user-export-manager-pro' ), number_format_i18n( count( $uempro_t['fields'] ) ) ) . ' · ' . $uempro_t['summary'] );
								?>
							</span>
						</div>
						<?php UEMPro_Helper::format_badge( $uempro_t['format'] ); ?>
						<div class="uempro-list__actions">
							<a class="uempro-btn uempro-btn--ghost uempro-btn--sm" href="<?php echo esc_url( uempro_admin_url( 'uempro-export', array( 'template' => $uempro_t['id'] ) ) ); ?>"><?php esc_html_e( 'Open', 'user-export-manager-pro' ); ?></a>
							<button type="button" class="uempro-btn uempro-btn--primary uempro-btn--sm" data-uempro-quick="<?php echo esc_attr( $uempro_t['id'] ); ?>"><?php esc_html_e( 'Export now', 'user-export-manager-pro' ); ?></button>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>

	<section class="uempro-panel">
		<div class="uempro-panel__head">
			<h2><?php esc_html_e( 'Recent activity', 'user-export-manager-pro' ); ?></h2>
			<a href="<?php echo esc_url( uempro_admin_url( 'uempro-history' ) ); ?>"><?php esc_html_e( 'View history', 'user-export-manager-pro' ); ?></a>
		</div>
		<?php if ( empty( $uempro_recent ) ) : ?>
			<div class="uempro-empty">
				<p><?php esc_html_e( 'Finished exports and imports appear here.', 'user-export-manager-pro' ); ?></p>
			</div>
		<?php else : ?>
			<ul class="uempro-list">
				<?php foreach ( $uempro_recent as $uempro_row ) : ?>
					<?php
					$uempro_is_import = isset( $uempro_row->entry_type ) && 'import' === $uempro_row->entry_type;
					$uempro_exists    = ! $uempro_is_import && file_exists( UEMPro_History::file_path( $uempro_row ) );
					?>
					<li class="uempro-list__row">
						<div class="uempro-list__main">
							<strong class="uempro-filename-text"><?php echo esc_html( $uempro_row->file_name ); ?></strong>
							<span>
								<?php
								echo esc_html(
									$uempro_is_import
										/* translators: 1: number of users, 2: date */
										? sprintf( __( 'Imported %1$s users · %2$s', 'user-export-manager-pro' ), number_format_i18n( $uempro_row->user_count ), UEMPro_Helper::date( $uempro_row->created_at ) )
										/* translators: 1: number of users, 2: date */
										: sprintf( __( 'Exported %1$s users · %2$s', 'user-export-manager-pro' ), number_format_i18n( $uempro_row->user_count ), UEMPro_Helper::date( $uempro_row->created_at ) )
								);
								?>
							</span>
						</div>
						<?php UEMPro_Helper::format_badge( $uempro_row->format ); ?>
						<div class="uempro-list__actions">
							<?php if ( $uempro_exists ) : ?>
								<a class="uempro-btn uempro-btn--ghost uempro-btn--sm" href="<?php echo esc_url( UEMPro_Export_Download::url( $uempro_row->file_name, $uempro_row->id ) ); ?>"><?php esc_html_e( 'Download', 'user-export-manager-pro' ); ?></a>
							<?php elseif ( $uempro_is_import ) : ?>
								<span class="uempro-tag uempro-tag--import"><?php esc_html_e( 'Import', 'user-export-manager-pro' ); ?></span>
							<?php else : ?>
								<span class="uempro-muted"><?php esc_html_e( 'File removed', 'user-export-manager-pro' ); ?></span>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
</div>
