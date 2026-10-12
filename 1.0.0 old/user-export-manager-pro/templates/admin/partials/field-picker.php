<?php
/**
 * Field picker (used by the export wizard and the template editor).
 *
 * @package User_Export_Manager_Pro
 *
 * @var string $uempro_picker_id Unique id prefix.
 * @var array  $uempro_selected  Pre-selected keys.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_core = UEMPro_Fields::core_fields();
$uempro_scan = UEMPro_Fields::scan();
$uempro_sel  = isset( $uempro_selected ) ? (array) $uempro_selected : array();
?>
<div class="uempro-fp" data-uempro-picker id="<?php echo esc_attr( $uempro_picker_id ); ?>">
	<div class="uempro-fp__bar">
		<label class="screen-reader-text" for="<?php echo esc_attr( $uempro_picker_id ); ?>-search"><?php esc_html_e( 'Filter fields', 'user-export-manager-pro' ); ?></label>
		<span class="uempro-fp__searchwrap">
			<span class="dashicons dashicons-search" aria-hidden="true"></span>
			<input type="search" id="<?php echo esc_attr( $uempro_picker_id ); ?>-search" class="uempro-input uempro-fp__search" placeholder="<?php esc_attr_e( 'Filter by field name or meta key', 'user-export-manager-pro' ); ?>" autocomplete="off">
		</span>
		<span class="uempro-fp__count" data-fp-count aria-live="polite"></span>
		<span class="uempro-fp__scanned" data-fp-scanned>
			<?php
			/* translators: %s: time of the last scan, e.g. "5 mins ago" */
			echo esc_html( sprintf( __( 'Fields detected automatically · last scanned %s', 'user-export-manager-pro' ), UEMPro_Fields::scanned_at_label() ) );
			?>
		</span>
		<button type="button" class="uempro-btn uempro-btn--primary-soft uempro-btn--sm" data-fp-scan>
			<span class="dashicons dashicons-update" aria-hidden="true"></span>
			<span data-fp-scan-label><?php esc_html_e( 'Scan custom fields', 'user-export-manager-pro' ); ?></span>
		</button>
	</div>

	<section class="uempro-fp__group" data-group="core">
		<div class="uempro-fp__ghead">
			<div>
				<h3><?php esc_html_e( 'WordPress profile', 'user-export-manager-pro' ); ?> <span class="uempro-fp__gcount" data-fp-gcount></span></h3>
			</div>
			<div class="uempro-fp__gactions">
				<button type="button" class="uempro-link" data-fp-all><?php esc_html_e( 'Select all', 'user-export-manager-pro' ); ?></button>
				<button type="button" class="uempro-link" data-fp-none><?php esc_html_e( 'Clear', 'user-export-manager-pro' ); ?></button>
			</div>
		</div>
		<div class="uempro-fp__grid">
			<?php
			foreach ( $uempro_core as $uempro_key => $uempro_label ) {
				UEMPro_Helper::field_item( $uempro_key, $uempro_label, in_array( $uempro_key, $uempro_sel, true ) );
			}
			?>
		</div>
	</section>

	<section class="uempro-fp__group" data-group="profile">
		<div class="uempro-fp__ghead">
			<div>
				<h3><?php esc_html_e( 'Custom profile fields', 'user-export-manager-pro' ); ?> <span class="uempro-fp__gcount" data-fp-gcount></span></h3>
				<p><?php esc_html_e( 'Found in your user meta — ACF, Ultimate Member, WooCommerce, BuddyPress, registration forms and other plugins.', 'user-export-manager-pro' ); ?></p>
			</div>
			<div class="uempro-fp__gactions">
				<button type="button" class="uempro-link" data-fp-all><?php esc_html_e( 'Select all', 'user-export-manager-pro' ); ?></button>
				<button type="button" class="uempro-link" data-fp-none><?php esc_html_e( 'Clear', 'user-export-manager-pro' ); ?></button>
			</div>
		</div>
		<div class="uempro-fp__grid" data-fp-grid="profile">
			<?php
			foreach ( $uempro_scan['profile'] as $uempro_key => $uempro_label ) {
				UEMPro_Helper::field_item( $uempro_key, $uempro_label, in_array( $uempro_key, $uempro_sel, true ) );
			}
			?>
		</div>
		<p class="uempro-fp__empty" data-fp-empty <?php echo empty( $uempro_scan['profile'] ) ? '' : 'hidden'; ?>><?php esc_html_e( 'No custom profile fields were found. If you just added a plugin that stores user data, click “Scan custom fields”.', 'user-export-manager-pro' ); ?></p>
	</section>

	<section class="uempro-fp__group is-collapsed" data-group="system">
		<div class="uempro-fp__ghead">
			<div>
				<h3><?php esc_html_e( 'Hidden & plugin data', 'user-export-manager-pro' ); ?> <span class="uempro-fp__gcount" data-fp-gcount></span></h3>
				<p><?php esc_html_e( 'Internal keys, admin screen preferences and plugin housekeeping. Rarely needed in an export.', 'user-export-manager-pro' ); ?></p>
			</div>
			<div class="uempro-fp__gactions">
				<button type="button" class="uempro-link" data-fp-none><?php esc_html_e( 'Clear', 'user-export-manager-pro' ); ?></button>
				<button type="button" class="uempro-btn uempro-btn--ghost uempro-btn--sm" data-fp-toggle aria-expanded="false"><?php esc_html_e( 'Show', 'user-export-manager-pro' ); ?></button>
			</div>
		</div>
		<div class="uempro-fp__grid" data-fp-grid="system">
			<?php
			foreach ( $uempro_scan['system'] as $uempro_key => $uempro_label ) {
				UEMPro_Helper::field_item( $uempro_key, $uempro_label, in_array( $uempro_key, $uempro_sel, true ) );
			}
			?>
		</div>
	</section>

	<p class="uempro-fp__nomatch" data-fp-nomatch hidden><?php esc_html_e( 'No fields match that filter.', 'user-export-manager-pro' ); ?></p>

	<div class="uempro-cols">
		<div class="uempro-cols__head">
			<h3><?php esc_html_e( 'Column order', 'user-export-manager-pro' ); ?></h3>
			<p><?php esc_html_e( 'Drag to reorder, or focus a column and use the arrow keys. Delete removes it.', 'user-export-manager-pro' ); ?></p>
		</div>
		<ol class="uempro-cols__list" data-fp-columns></ol>
	</div>
</div>
