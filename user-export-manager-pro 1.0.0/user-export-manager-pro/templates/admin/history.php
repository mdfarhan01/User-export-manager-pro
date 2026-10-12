<?php
/**
 * Export history screen.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Pagination only.
$uempro_paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$uempro_per_page = 20;
$uempro_total    = UEMPro_History::count();
$uempro_rows     = UEMPro_History::page( $uempro_paged, $uempro_per_page );
$uempro_pages    = max( 1, (int) ceil( $uempro_total / $uempro_per_page ) );
$uempro_can_del  = current_user_can( 'manage_options' );
$uempro_keep     = (int) UEMPro_Settings::get( 'retention_days' );
?>

<p class="uempro-infobar">
	<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
	<?php
	if ( $uempro_keep > 0 ) {
		/* translators: %s: number of days */
		echo esc_html( sprintf( _n( 'Export files are deleted automatically after %s day. Change this in Settings.', 'Export files are deleted automatically after %s days. Change this in Settings.', $uempro_keep, 'user-export-manager-pro' ), number_format_i18n( $uempro_keep ) ) );
	} else {
		esc_html_e( 'Automatic cleanup is turned off — export files are kept until you delete them.', 'user-export-manager-pro' );
	}
	?>
</p>

<div class="uempro-panel uempro-panel--flush">
	<?php if ( empty( $uempro_rows ) ) : ?>
		<div class="uempro-empty uempro-empty--large">
			<h2><?php esc_html_e( 'Nothing here yet', 'user-export-manager-pro' ); ?></h2>
			<p><?php esc_html_e( 'Exports are kept here so you can download them again; imports are listed with their results.', 'user-export-manager-pro' ); ?></p>
			<a class="uempro-btn uempro-btn--primary" href="<?php echo esc_url( uempro_admin_url( 'uempro-export' ) ); ?>"><?php esc_html_e( 'Run an export', 'user-export-manager-pro' ); ?></a>
		</div>
	<?php else : ?>
		<div class="uempro-tablewrap">
			<table class="uempro-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'File', 'user-export-manager-pro' ); ?></th>
						<th><?php esc_html_e( 'Type', 'user-export-manager-pro' ); ?></th>
						<th><?php esc_html_e( 'Format', 'user-export-manager-pro' ); ?></th>
						<th class="is-num"><?php esc_html_e( 'Users', 'user-export-manager-pro' ); ?></th>
						<th class="is-num"><?php esc_html_e( 'Columns', 'user-export-manager-pro' ); ?></th>
						<th><?php esc_html_e( 'Template', 'user-export-manager-pro' ); ?></th>
						<th><?php esc_html_e( 'Run by', 'user-export-manager-pro' ); ?></th>
						<th><?php esc_html_e( 'Date', 'user-export-manager-pro' ); ?></th>
						<th><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'user-export-manager-pro' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $uempro_rows as $uempro_row ) : ?>
						<?php
						$uempro_admin     = get_userdata( $uempro_row->admin_id );
						$uempro_is_import = isset( $uempro_row->entry_type ) && 'import' === $uempro_row->entry_type;
						$uempro_exists    = ! $uempro_is_import && file_exists( UEMPro_History::file_path( $uempro_row ) );
						?>
						<tr data-history-id="<?php echo esc_attr( $uempro_row->id ); ?>">
							<td>
								<span class="uempro-filename-text"><?php echo esc_html( $uempro_row->file_name ); ?></span>
								<?php if ( ! $uempro_is_import ) : ?>
									<span class="uempro-muted"><?php echo esc_html( $uempro_exists ? size_format( (int) $uempro_row->file_size ) : __( 'File removed', 'user-export-manager-pro' ) ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<span class="uempro-tag uempro-tag--<?php echo $uempro_is_import ? 'import' : 'export'; ?>"><?php echo $uempro_is_import ? esc_html__( 'Import', 'user-export-manager-pro' ) : esc_html__( 'Export', 'user-export-manager-pro' ); ?></span>
							</td>
							<td><?php UEMPro_Helper::format_badge( $uempro_row->format ); ?></td>
							<td class="is-num"><?php echo esc_html( number_format_i18n( $uempro_row->user_count ) ); ?></td>
							<td class="is-num"><?php echo esc_html( number_format_i18n( $uempro_row->field_count ) ); ?></td>
							<td><?php echo esc_html( $uempro_row->template_name ? $uempro_row->template_name : '—' ); ?></td>
							<td><?php echo esc_html( $uempro_admin ? $uempro_admin->display_name : '—' ); ?></td>
							<td><?php echo esc_html( UEMPro_Helper::date( $uempro_row->created_at ) ); ?></td>
							<td class="uempro-table__actions">
								<?php if ( $uempro_exists ) : ?>
									<a class="uempro-btn uempro-btn--ghost uempro-btn--sm" href="<?php echo esc_url( UEMPro_Export_Download::url( $uempro_row->file_name, $uempro_row->id ) ); ?>"><?php esc_html_e( 'Download', 'user-export-manager-pro' ); ?></a>
								<?php endif; ?>
								<?php if ( $uempro_can_del ) : ?>
									<button type="button" class="uempro-iconbtn uempro-iconbtn--danger" data-history-delete="<?php echo esc_attr( $uempro_row->id ); ?>" title="<?php esc_attr_e( 'Delete', 'user-export-manager-pro' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Delete', 'user-export-manager-pro' ); ?></span></button>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php if ( $uempro_pages > 1 ) : ?>
			<nav class="uempro-pagination" aria-label="<?php esc_attr_e( 'History pages', 'user-export-manager-pro' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%', uempro_admin_url( 'uempro-history' ) ),
							'format'    => '',
							'current'   => $uempro_paged,
							'total'     => $uempro_pages,
							'prev_text' => '‹',
							'next_text' => '›',
						)
					)
				);
				?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</div>
