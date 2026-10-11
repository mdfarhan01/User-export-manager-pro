<?php
/**
 * "Which users" picker (wizard + template editor).
 *
 * @package User_Export_Manager_Pro
 *
 * @var string $uempro_scope_id    Unique id prefix.
 * @var bool   $uempro_users_table Whether to offer the "specific users" table.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_counts = count_users();
$uempro_roles  = wp_roles()->get_names();
$uempro_avail  = isset( $uempro_counts['avail_roles'] ) ? $uempro_counts['avail_roles'] : array();
$uempro_sid    = $uempro_scope_id;

$uempro_choices = array(
	'all'      => array( __( 'All users', 'user-export-manager-pro' ), __( 'Everyone registered on this site.', 'user-export-manager-pro' ), 'dashicons-groups' ),
	'role'     => array( __( 'By role', 'user-export-manager-pro' ), __( 'Subscribers, customers, authors…', 'user-export-manager-pro' ), 'dashicons-id-alt' ),
	'date'     => array( __( 'By registration date', 'user-export-manager-pro' ), __( 'Today, last week, last month or a custom range.', 'user-export-manager-pro' ), 'dashicons-calendar-alt' ),
	'selected' => array( __( 'Specific users', 'user-export-manager-pro' ), __( 'Pick people one by one from a searchable list.', 'user-export-manager-pro' ), 'dashicons-admin-users' ),
);
?>
<div class="uempro-scope" data-uempro-scope data-scope-id="<?php echo esc_attr( $uempro_sid ); ?>">
	<fieldset class="uempro-choices">
		<legend class="screen-reader-text"><?php esc_html_e( 'Which users', 'user-export-manager-pro' ); ?></legend>
		<?php foreach ( $uempro_choices as $uempro_value => $uempro_choice ) : ?>
			<label class="uempro-choice" <?php echo ( 'selected' === $uempro_value && ! $uempro_users_table ) ? 'hidden data-scope-selected-only' : ''; ?>>
				<input type="radio" name="<?php echo esc_attr( $uempro_sid ); ?>-scope" value="<?php echo esc_attr( $uempro_value ); ?>" <?php checked( 'all', $uempro_value ); ?>>
				<span class="uempro-choice__icon" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $uempro_choice[2] ); ?>"></span></span>
				<span class="uempro-choice__body">
					<strong><?php echo esc_html( $uempro_choice[0] ); ?></strong>
					<span data-choice-desc><?php echo esc_html( $uempro_choice[1] ); ?></span>
				</span>
			</label>
		<?php endforeach; ?>
	</fieldset>

	<div class="uempro-scope__panel" data-scope-panel="role" hidden>
		<p class="uempro-scope__label"><?php esc_html_e( 'Include users with any of these roles', 'user-export-manager-pro' ); ?></p>
		<div class="uempro-roles">
			<?php foreach ( $uempro_roles as $uempro_role => $uempro_name ) : ?>
				<label class="uempro-check">
					<input type="checkbox" value="<?php echo esc_attr( $uempro_role ); ?>" data-scope-role>
					<span><?php echo esc_html( translate_user_role( $uempro_name ) ); ?></span>
					<span class="uempro-check__meta"><?php echo esc_html( number_format_i18n( isset( $uempro_avail[ $uempro_role ] ) ? $uempro_avail[ $uempro_role ] : 0 ) ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="uempro-scope__panel" data-scope-panel="date" hidden>
		<p class="uempro-scope__label"><?php esc_html_e( 'Registered', 'user-export-manager-pro' ); ?></p>
		<div class="uempro-segmented" role="radiogroup">
			<?php
			$uempro_ranges = array(
				'today'  => __( 'Today', 'user-export-manager-pro' ),
				'7days'  => __( 'Last 7 days', 'user-export-manager-pro' ),
				'30days' => __( 'Last 30 days', 'user-export-manager-pro' ),
				'custom' => __( 'Custom range', 'user-export-manager-pro' ),
			);
			foreach ( $uempro_ranges as $uempro_value => $uempro_label ) :
				?>
				<label><input type="radio" name="<?php echo esc_attr( $uempro_sid ); ?>-range" value="<?php echo esc_attr( $uempro_value ); ?>" <?php checked( '30days', $uempro_value ); ?>><span><?php echo esc_html( $uempro_label ); ?></span></label>
			<?php endforeach; ?>
		</div>
		<div class="uempro-daterange" data-scope-custom hidden>
			<label><span><?php esc_html_e( 'From', 'user-export-manager-pro' ); ?></span><input type="date" class="uempro-input" data-scope-from></label>
			<label><span><?php esc_html_e( 'To', 'user-export-manager-pro' ); ?></span><input type="date" class="uempro-input" data-scope-to></label>
		</div>
	</div>

	<?php if ( $uempro_users_table ) : ?>
		<div class="uempro-scope__panel" data-scope-panel="selected" hidden>
			<div class="uempro-userpick">
				<div class="uempro-userpick__bar">
					<span class="uempro-fp__searchwrap">
						<span class="dashicons dashicons-search" aria-hidden="true"></span>
						<input type="search" class="uempro-input" data-users-search placeholder="<?php esc_attr_e( 'Search name, username or email', 'user-export-manager-pro' ); ?>" aria-label="<?php esc_attr_e( 'Search users', 'user-export-manager-pro' ); ?>">
					</span>
					<select class="uempro-select" data-users-role aria-label="<?php esc_attr_e( 'Filter by role', 'user-export-manager-pro' ); ?>">
						<option value=""><?php esc_html_e( 'All roles', 'user-export-manager-pro' ); ?></option>
						<?php foreach ( $uempro_roles as $uempro_role => $uempro_name ) : ?>
							<option value="<?php echo esc_attr( $uempro_role ); ?>"><?php echo esc_html( translate_user_role( $uempro_name ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="uempro-tablewrap">
					<table class="uempro-table uempro-table--users">
						<thead>
							<tr>
								<th class="uempro-table__check"><input type="checkbox" data-users-all aria-label="<?php esc_attr_e( 'Select every user on this page', 'user-export-manager-pro' ); ?>"></th>
								<th><?php esc_html_e( 'User', 'user-export-manager-pro' ); ?></th>
								<th><?php esc_html_e( 'Email', 'user-export-manager-pro' ); ?></th>
								<th><?php esc_html_e( 'Role', 'user-export-manager-pro' ); ?></th>
							</tr>
						</thead>
						<tbody data-users-body></tbody>
					</table>
				</div>
				<div class="uempro-userpick__foot">
					<span><strong data-users-selected>0</strong> <?php esc_html_e( 'selected', 'user-export-manager-pro' ); ?> <button type="button" class="uempro-link" data-users-clear><?php esc_html_e( 'Clear selection', 'user-export-manager-pro' ); ?></button></span>
					<span class="uempro-pager">
						<span data-users-pageinfo></span>
						<button type="button" class="uempro-btn uempro-btn--ghost uempro-btn--sm" data-users-prev aria-label="<?php esc_attr_e( 'Previous page', 'user-export-manager-pro' ); ?>">‹</button>
						<button type="button" class="uempro-btn uempro-btn--ghost uempro-btn--sm" data-users-next aria-label="<?php esc_attr_e( 'Next page', 'user-export-manager-pro' ); ?>">›</button>
					</span>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="uempro-scope__extra">
		<div class="uempro-field">
			<label for="<?php echo esc_attr( $uempro_sid ); ?>-search"><?php esc_html_e( 'Only users matching (optional)', 'user-export-manager-pro' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $uempro_sid ); ?>-search" class="uempro-input" data-scope-search placeholder="<?php esc_attr_e( 'e.g. @gmail.com or a username', 'user-export-manager-pro' ); ?>">
		</div>
		<div class="uempro-field">
			<label for="<?php echo esc_attr( $uempro_sid ); ?>-orderby"><?php esc_html_e( 'Sort rows by', 'user-export-manager-pro' ); ?></label>
			<div class="uempro-inline">
				<select id="<?php echo esc_attr( $uempro_sid ); ?>-orderby" class="uempro-select" data-scope-orderby>
					<?php foreach ( UEMPro_Export_Config::sort_options() as $uempro_value => $uempro_label ) : ?>
						<option value="<?php echo esc_attr( $uempro_value ); ?>"><?php echo esc_html( $uempro_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<select class="uempro-select" data-scope-order aria-label="<?php esc_attr_e( 'Sort direction', 'user-export-manager-pro' ); ?>">
					<option value="ASC"><?php esc_html_e( 'Ascending', 'user-export-manager-pro' ); ?></option>
					<option value="DESC"><?php esc_html_e( 'Descending', 'user-export-manager-pro' ); ?></option>
				</select>
			</div>
		</div>
	</div>
</div>
