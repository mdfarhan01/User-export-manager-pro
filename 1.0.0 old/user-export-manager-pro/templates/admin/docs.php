<?php
/**
 * Documentation screen.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uempro_kses = array(
	'strong' => array(),
	'em'     => array(),
	'code'   => array(),
	'a'      => array( 'href' => array() ),
);

/**
 * Echo a translated paragraph that may contain basic markup.
 *
 * @param string $text Translated text.
 */
$uempro_p = static function ( $text ) use ( $uempro_kses ) {
	echo '<p>' . wp_kses( $text, $uempro_kses ) . '</p>';
};

$uempro_icons = array(
	'start' => 'flag',
	'export' => 'upload',
	'fields' => 'columns',
	'templates' => 'portfolio',
	'import' => 'download',
	'formats' => 'media-spreadsheet',
	'history' => 'backup',
	'settings' => 'admin-generic',
	'security' => 'shield',
	'faq' => 'editor-help',
	'developers' => 'editor-code',
	'about' => 'info-outline',
);

$uempro_toc = array(
	'start'      => __( 'Getting started', 'user-export-manager-pro' ),
	'export'     => __( 'Running an export', 'user-export-manager-pro' ),
	'fields'     => __( 'Choosing fields', 'user-export-manager-pro' ),
	'templates'  => __( 'Templates', 'user-export-manager-pro' ),
	'import'     => __( 'Importing users', 'user-export-manager-pro' ),
	'formats'    => __( 'File formats', 'user-export-manager-pro' ),
	'history'    => __( 'History & downloads', 'user-export-manager-pro' ),
	'settings'   => __( 'Settings reference', 'user-export-manager-pro' ),
	'security'   => __( 'Security & privacy', 'user-export-manager-pro' ),
	'faq'        => __( 'FAQ', 'user-export-manager-pro' ),
	'developers' => __( 'Developer hooks', 'user-export-manager-pro' ),
	'about'      => __( 'About', 'user-export-manager-pro' ),
);
?>

<div class="uempro-docshero">
	<div class="uempro-docshero__text">
		<h2><?php esc_html_e( 'How can we help?', 'user-export-manager-pro' ); ?></h2>
		<p><?php esc_html_e( 'Start with the guide that matches what you want to do, or browse every topic in the contents list.', 'user-export-manager-pro' ); ?></p>
	</div>
	<div class="uempro-docshero__links">
		<?php
		$uempro_quick = array(
			'export'    => __( 'Export users to a file', 'user-export-manager-pro' ),
			'import'    => __( 'Import users from a file', 'user-export-manager-pro' ),
			'templates' => __( 'Save and reuse templates', 'user-export-manager-pro' ),
			'faq'       => __( 'Common questions', 'user-export-manager-pro' ),
		);
		foreach ( $uempro_quick as $uempro_id => $uempro_text ) :
			?>
			<a class="uempro-docshero__link" href="#uempro-doc-<?php echo esc_attr( $uempro_id ); ?>">
				<span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-<?php echo esc_attr( $uempro_icons[ $uempro_id ] ); ?>"></span></span>
				<span><strong><?php echo esc_html( $uempro_toc[ $uempro_id ] ); ?></strong><span><?php echo esc_html( $uempro_text ); ?></span></span>
			</a>
		<?php endforeach; ?>
	</div>
</div>

<div class="uempro-docs">
	<nav class="uempro-docs__toc" aria-label="<?php esc_attr_e( 'Contents', 'user-export-manager-pro' ); ?>">
		<p class="uempro-docs__toctitle"><?php esc_html_e( 'Contents', 'user-export-manager-pro' ); ?></p>
		<ol>
			<?php foreach ( $uempro_toc as $uempro_id => $uempro_title ) : ?>
				<li><a href="#uempro-doc-<?php echo esc_attr( $uempro_id ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $uempro_icons[ $uempro_id ] ); ?>" aria-hidden="true"></span><?php echo esc_html( $uempro_title ); ?></a></li>
			<?php endforeach; ?>
		</ol>
	</nav>

	<div class="uempro-docs__body">

		<article class="uempro-doc" id="uempro-doc-start">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-flag"></span></span><?php echo esc_html( $uempro_toc['start'] ); ?></h2>
			<?php
			$uempro_p( __( 'User Export Manager Pro exports the people registered on your site — including the custom profile fields that other plugins add — to CSV, Excel, JSON or XML. You decide exactly which users are included and which fields become columns.', 'user-export-manager-pro' ) );
			$uempro_p( __( 'After activation you will find <strong>User Export</strong> in the WordPress admin menu. It has seven screens, listed in the sidebar on the left of every plugin page:', 'user-export-manager-pro' ) );
			?>
			<dl class="uempro-doc__dl">
				<dt><?php esc_html_e( 'Dashboard', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Site totals, one-click template exports and your most recent files.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Export users', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'A four-step wizard: users, fields, format, review.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Import users', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Create or update users from a CSV, Excel, JSON or XML file.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Templates', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Create, edit, duplicate, delete and run saved export setups.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'History', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Every export with a download link, who ran it and when.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Settings', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Defaults, CSV options, history cleanup and privacy options (administrators only).', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Documentation', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'This page.', 'user-export-manager-pro' ); ?></dd>
			</dl>
			<?php $uempro_p( __( '<strong>Requirements:</strong> WordPress 6.2 or newer and PHP 7.4 or newer. Excel export also needs the PHP <code>ZipArchive</code> extension, which almost every host provides.', 'user-export-manager-pro' ) ); ?>
		</article>

		<article class="uempro-doc" id="uempro-doc-export">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-upload"></span></span><?php echo esc_html( $uempro_toc['export'] ); ?></h2>
			<?php $uempro_p( __( 'Open <strong>User Export → Export users</strong>. The wizard walks you through four steps; you can go back to any finished step from the step bar.', 'user-export-manager-pro' ) ); ?>
			<ol class="uempro-doc__steps">
				<li>
					<h3><?php esc_html_e( 'Users', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Choose <strong>All users</strong>, <strong>By role</strong>, <strong>By registration date</strong> or <strong>Specific users</strong>. With specific users you search and tick people from a paged list; your ticks are kept while you search and change pages.', 'user-export-manager-pro' ) ); ?>
					<?php $uempro_p( __( 'Optionally narrow the result with a search term (it matches username, email, display name and profile slug) and choose how rows are sorted. The live counter under the options shows how many users will be exported.', 'user-export-manager-pro' ) ); ?>
				</li>
				<li>
					<h3><?php esc_html_e( 'Fields', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Tick the fields you want. Each ticked field becomes a column, shown in the <strong>Column order</strong> strip at the bottom where you can reorder or remove columns. See “Choosing fields” below.', 'user-export-manager-pro' ) ); ?>
				</li>
				<li>
					<h3><?php esc_html_e( 'Format', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Pick CSV, Excel (XLSX), JSON or XML and set the file name. The date and a short random code are appended automatically.', 'user-export-manager-pro' ) ); ?>
				</li>
				<li>
					<h3><?php esc_html_e( 'Review & export', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Check the summary, optionally save everything as a template, then click <strong>Run export</strong>. A progress bar shows each batch; when the file is ready the download starts by itself and a <strong>Download file</strong> button stays on screen.', 'user-export-manager-pro' ) ); ?>
				</li>
			</ol>
			<div class="uempro-callout">
				<?php $uempro_p( __( '<strong>Large sites:</strong> exports run in batches (200 users per request by default), so even sites with hundreds of thousands of users finish without timeouts. Keep the tab open until the file is ready.', 'user-export-manager-pro' ) ); ?>
			</div>
		</article>

		<article class="uempro-doc" id="uempro-doc-fields">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-columns"></span></span><?php echo esc_html( $uempro_toc['fields'] ); ?></h2>
			<?php $uempro_p( __( 'Fields are listed in three groups. Each field shows a readable name and, underneath, its real database key so two similar names are never confused.', 'user-export-manager-pro' ) ); ?>
			<dl class="uempro-doc__dl">
				<dt><?php esc_html_e( 'WordPress profile', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'User ID, username, email, first and last name, display name, nickname, role, website, registered date, biographical info, profile slug and status.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Custom profile fields', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Every other meta key found in the user meta table — data saved by ACF, Ultimate Member, WooCommerce (billing and shipping), BuddyPress, Meta Box, JetEngine, registration form plugins and your own code.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Hidden & plugin data', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Keys that begin with an underscore, admin screen preferences, dismissed notices and plugin housekeeping. This group is collapsed by default; click “Show” if you need something from it.', 'user-export-manager-pro' ); ?></dd>
			</dl>
			<h3><?php esc_html_e( 'Why each field appears only once', 'user-export-manager-pro' ); ?></h3>
			<?php
			$uempro_p( __( 'Plugins such as ACF save two meta keys for every field: the value (<code>district</code>) and a hidden reference (<code>_district</code>) that only stores the field’s internal ID. Listing both would show every field twice, so the scanner drops the reference key whenever the real value exists, and skips <code>first_name</code>, <code>last_name</code>, <code>nickname</code> and <code>description</code> because they are already in the WordPress profile group.', 'user-export-manager-pro' ) );
			$uempro_p( __( 'The field list is cached for 12 hours. After installing a plugin that adds user fields — or after the first user fills in a new form field — click <strong>Scan custom fields</strong>.', 'user-export-manager-pro' ) );
			?>
			<h3><?php esc_html_e( 'Column order', 'user-export-manager-pro' ); ?></h3>
			<?php $uempro_p( __( 'Columns are added in the order you tick them. Drag a column in the strip to move it, or focus it with the Tab key and use the left and right arrow keys. Press Delete or Backspace to remove it.', 'user-export-manager-pro' ) ); ?>
			<h3><?php esc_html_e( 'How values are written', 'user-export-manager-pro' ); ?></h3>
			<?php $uempro_p( __( 'Simple lists (for example checkbox values) are joined with commas. Complex values are written as JSON so no data is lost. Roles use their readable names.', 'user-export-manager-pro' ) ); ?>
		</article>

		<article class="uempro-doc" id="uempro-doc-templates">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-portfolio"></span></span><?php echo esc_html( $uempro_toc['templates'] ); ?></h2>
			<?php $uempro_p( __( 'A template stores who to include, the fields in their column order, the format and the file name. Example: a template named “Subscribers — login & education” with role <em>Subscriber</em> and the fields Username, Email, Last name and Education.', 'user-export-manager-pro' ) ); ?>
			<h3><?php esc_html_e( 'Create a template', 'user-export-manager-pro' ); ?></h3>
			<?php $uempro_p( __( 'Either click <strong>New template</strong> on the Templates screen and fill in the editor, or build an export in the wizard and use <strong>Save as new template</strong> on the review step.', 'user-export-manager-pro' ) ); ?>
			<h3><?php esc_html_e( 'Use a template', 'user-export-manager-pro' ); ?></h3>
			<?php $uempro_p( __( '<strong>Export now</strong> runs the template immediately and downloads the file. <strong>Open in wizard</strong> (or “Start from a template” at the top of the wizard) loads it with every field already ticked, so you can adjust anything before exporting.', 'user-export-manager-pro' ) ); ?>
			<h3><?php esc_html_e( 'Edit, duplicate and delete', 'user-export-manager-pro' ); ?></h3>
			<?php $uempro_p( __( 'Use the pencil, copy and bin buttons on each template card. When you load a template into the wizard and change it, the review step also offers <strong>Update</strong> to overwrite the original. Deleting a template does not delete past exports.', 'user-export-manager-pro' ) ); ?>
		</article>

		<article class="uempro-doc" id="uempro-doc-import">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-download"></span></span><?php echo esc_html( $uempro_toc['import'] ); ?></h2>
			<?php $uempro_p( __( 'Open <strong>Import users</strong> to create or update accounts from a file. Any file exported by this plugin — CSV, Excel, JSON or XML — can be imported again unchanged, so you can move users between sites, or export, edit in Excel and import the changes back.', 'user-export-manager-pro' ) ); ?>
			<ol class="uempro-doc__steps">
				<li>
					<h3><?php esc_html_e( 'Upload file', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Drop a .csv, .xlsx, .json or .xml file onto the upload area. The first row (or the keys of each JSON/XML record) must contain the column names. Comma, semicolon and tab separated CSV files are detected automatically.', 'user-export-manager-pro' ) ); ?>
				</li>
				<li>
					<h3><?php esc_html_e( 'Map columns', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Every column is matched to a field automatically — by its name, its meta key or common words such as “Email” or “Surname”. Change any row, choose “Don’t import” to ignore a column, or pick “New custom field…” to save it under a meta key of your choice. At least one column must be Email, Username or User ID.', 'user-export-manager-pro' ) ); ?>
				</li>
				<li>
					<h3><?php esc_html_e( 'Options', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Decide how existing users are found (email, username or ID), whether they are updated or skipped, whether new users are created, the role for new users without a Role column, whether empty cells are ignored, and whether new users receive the WordPress welcome email.', 'user-export-manager-pro' ) ); ?>
				</li>
				<li>
					<h3><?php esc_html_e( 'Import', 'user-export-manager-pro' ); ?></h3>
					<?php $uempro_p( __( 'Rows are processed in small batches with a progress bar. The summary shows how many users were created, updated, skipped or failed, with the row number and reason for every problem.', 'user-export-manager-pro' ) ); ?>
				</li>
			</ol>
			<h3><?php esc_html_e( 'Good to know', 'user-export-manager-pro' ); ?></h3>
			<ul class="uempro-doc__list">
				<li><?php esc_html_e( 'Usernames and registration dates of existing users never change. The registration date is only used for new users.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'Roles may be written as names (Subscriber, Editor) or keys (subscriber). Several roles can be separated by commas. You can only assign roles you are allowed to assign, and your own role is never changed.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'Passwords: a “Password (plain text)” column sets the password. A “Password hash” column — available when “Allow exporting password hashes” is on — keeps users’ existing passwords when moving them between WordPress sites. Without either, new users get a strong random password.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'Values written as JSON by the export (complex custom fields) are restored as structured data. Simple lists exported as “a, b” are stored as text.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'Users are not emailed about profile or password changes made by an import.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'The uploaded file is never kept: its rows are stored in the protected plugin folder only while the import runs.', 'user-export-manager-pro' ); ?></li>
			</ul>
			<div class="uempro-callout uempro-callout--warn">
				<?php $uempro_p( __( '<strong>Tip:</strong> run an export of your users before a large import, so you can restore them if a column was mapped incorrectly.', 'user-export-manager-pro' ) ); ?>
			</div>
		</article>

		<article class="uempro-doc" id="uempro-doc-formats">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-media-spreadsheet"></span></span><?php echo esc_html( $uempro_toc['formats'] ); ?></h2>
			<div class="uempro-tablewrap">
				<table class="uempro-table uempro-table--doc">
					<thead><tr><th><?php esc_html_e( 'Format', 'user-export-manager-pro' ); ?></th><th><?php esc_html_e( 'Best for', 'user-export-manager-pro' ); ?></th><th><?php esc_html_e( 'Notes', 'user-export-manager-pro' ); ?></th></tr></thead>
					<tbody>
						<tr><td><?php UEMPro_Helper::format_badge( 'csv' ); ?></td><td><?php esc_html_e( 'Excel, Google Sheets, mailing tools, imports', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Separator and UTF-8 marker are configurable in Settings.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><?php UEMPro_Helper::format_badge( 'xlsx' ); ?></td><td><?php esc_html_e( 'Sharing a ready-to-read spreadsheet', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Styled header row, frozen headings, filter buttons, numeric IDs stored as numbers.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><?php UEMPro_Helper::format_badge( 'json' ); ?></td><td><?php esc_html_e( 'Developers, scripts, APIs', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'An array of objects keyed by field key.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><?php UEMPro_Helper::format_badge( 'xml' ); ?></td><td><?php esc_html_e( 'Reporting systems that require XML', 'user-export-manager-pro' ); ?></td><td><?php echo wp_kses( __( 'A <code>&lt;users&gt;</code> root with one <code>&lt;user&gt;</code> element per person.', 'user-export-manager-pro' ), $uempro_kses ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</article>

		<article class="uempro-doc" id="uempro-doc-history">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-backup"></span></span><?php echo esc_html( $uempro_toc['history'] ); ?></h2>
			<?php
			$uempro_p( __( 'Each export is listed with its file name, size, format, number of users and columns, the template used and who ran it. <strong>Download</strong> fetches the file again. Administrators can delete single entries or everything at once.', 'user-export-manager-pro' ) );
			$uempro_p( __( 'Files are removed automatically after the number of days set in Settings (30 by default). Set it to 0 to keep them forever.', 'user-export-manager-pro' ) );
			?>
		</article>

		<article class="uempro-doc" id="uempro-doc-settings">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-admin-generic"></span></span><?php echo esc_html( $uempro_toc['settings'] ); ?></h2>
			<dl class="uempro-doc__dl">
				<dt><?php esc_html_e( 'Default format / file name', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Pre-filled in the wizard and the template editor.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Users per batch', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'How many users each request processes. Lower it if exports stall on slow hosting.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'CSV separator', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Comma, semicolon or tab.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Registered date format', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Raw database value in UTC, or your site’s date format and timezone.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'UTF-8 marker', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Helps Excel show non-English characters correctly.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Formula protection', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Stops profile values from running as spreadsheet formulas.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Keep export history / Delete exports after', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Whether exports are listed in History, and for how long files are kept.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Allow exporting password hashes', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Adds the Password hash field for administrators. Off by default.', 'user-export-manager-pro' ); ?></dd>
				<dt><?php esc_html_e( 'Delete all plugin data on uninstall', 'user-export-manager-pro' ); ?></dt>
				<dd><?php esc_html_e( 'Off by default, so reinstalling keeps your templates.', 'user-export-manager-pro' ); ?></dd>
			</dl>
		</article>

		<article class="uempro-doc" id="uempro-doc-security">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-shield"></span></span><?php echo esc_html( $uempro_toc['security'] ); ?></h2>
			<?php
			$uempro_p( __( 'Exports and imports touch personal data, so the plugin is strict about who can use them.', 'user-export-manager-pro' ) );
			?>
			<ul class="uempro-doc__list">
				<li><?php echo wp_kses( __( 'Only users with the <code>list_users</code> capability (administrators by default) can export. Importing needs <code>create_users</code> and <code>edit_users</code>; Settings and deleting history need <code>manage_options</code>.', 'user-export-manager-pro' ), $uempro_kses ); ?></li>
				<li><?php echo wp_kses( __( 'Files are saved in <code>wp-content/uploads/uempro-exports/</code> with a random code in the name. The folder blocks direct web access (Apache and IIS rules included); downloads always go through WordPress with a security check.', 'user-export-manager-pro' ), $uempro_kses ); ?></li>
				<li><?php esc_html_e( 'Session tokens and capability data are never exportable. Password hashes are only available when an administrator turns them on.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'Every request is protected by a WordPress nonce, and all input is sanitized.', 'user-export-manager-pro' ); ?></li>
				<li><?php esc_html_e( 'The plugin makes no external requests and sends no data anywhere.', 'user-export-manager-pro' ); ?></li>
			</ul>
			<div class="uempro-callout uempro-callout--warn">
				<?php $uempro_p( __( '<strong>Nginx users:</strong> Nginx ignores .htaccess files. File names are random so they cannot be guessed, but for full protection add a rule that denies access to <code>/wp-content/uploads/uempro-exports/</code>.', 'user-export-manager-pro' ) ); ?>
			</div>
		</article>

		<article class="uempro-doc" id="uempro-doc-faq">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-editor-help"></span></span><?php echo esc_html( $uempro_toc['faq'] ); ?></h2>
			<details class="uempro-faq">
				<summary><?php esc_html_e( 'Can I export users’ passwords?', 'user-export-manager-pro' ); ?></summary>
				<?php $uempro_p( __( 'No plugin can export readable passwords: WordPress only stores a one-way hash. If you are moving users to another WordPress site, an administrator can enable “Allow exporting password hashes” in Settings; the hash keeps working on the new site. For anything else, ask users to reset their password.', 'user-export-manager-pro' ) ); ?>
			</details>
			<details class="uempro-faq">
				<summary><?php esc_html_e( 'A custom field I need is missing.', 'user-export-manager-pro' ); ?></summary>
				<?php $uempro_p( __( 'A meta key only exists once at least one user has a value for it. Make sure someone has filled it in, then click “Scan custom fields”. Also open the “Hidden & plugin data” group — keys beginning with an underscore are listed there.', 'user-export-manager-pro' ) ); ?>
			</details>
			<details class="uempro-faq">
				<summary><?php esc_html_e( 'Accents or Bangla characters look broken in Excel.', 'user-export-manager-pro' ); ?></summary>
				<?php $uempro_p( __( 'Turn on “Add UTF-8 marker to CSV” in Settings, or export as Excel (XLSX), which always stores text as UTF-8.', 'user-export-manager-pro' ) ); ?>
			</details>
			<details class="uempro-faq">
				<summary><?php esc_html_e( 'The export stops partway through.', 'user-export-manager-pro' ); ?></summary>
				<?php $uempro_p( __( 'Lower “Users per batch” in Settings (try 50) so each request does less work, and keep the browser tab open until the download starts.', 'user-export-manager-pro' ) ); ?>
			</details>
			<details class="uempro-faq">
				<summary><?php esc_html_e( 'Does it work on multisite?', 'user-export-manager-pro' ); ?></summary>
				<?php $uempro_p( __( 'Yes, per site. Each site exports the users that belong to it.', 'user-export-manager-pro' ) ); ?>
			</details>
		</article>

		<article class="uempro-doc" id="uempro-doc-developers">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-editor-code"></span></span><?php echo esc_html( $uempro_toc['developers'] ); ?></h2>
			<?php $uempro_p( __( 'All hooks are prefixed with <code>uempro_</code>. All PHP classes are prefixed with <code>UEMPro_</code>.', 'user-export-manager-pro' ) ); ?>
			<div class="uempro-tablewrap">
				<table class="uempro-table uempro-table--doc">
					<thead><tr><th><?php esc_html_e( 'Hook', 'user-export-manager-pro' ); ?></th><th><?php esc_html_e( 'Type', 'user-export-manager-pro' ); ?></th><th><?php esc_html_e( 'Purpose', 'user-export-manager-pro' ); ?></th></tr></thead>
					<tbody>
						<tr><td><code>uempro_capability</code></td><td><?php esc_html_e( 'Filter', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Capability required to export. Default list_users.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_core_fields</code></td><td><?php esc_html_e( 'Filter', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Add, rename or remove WordPress profile fields.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_scanned_fields</code></td><td><?php esc_html_e( 'Filter', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Adjust the scanned custom fields or their labels.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_is_system_meta_key</code></td><td><?php esc_html_e( 'Filter', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Move a meta key in or out of the Hidden & plugin data group.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_user_query_args</code></td><td><?php esc_html_e( 'Filter', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Modify the WP_User_Query arguments of an export.', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_export_value</code></td><td><?php esc_html_e( 'Filter', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Change any exported value (receives value, field key and WP_User).', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_import_completed</code></td><td><?php esc_html_e( 'Action', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Runs after an import finishes (receives the created/updated/skipped/failed counts and job data).', 'user-export-manager-pro' ); ?></td></tr>
						<tr><td><code>uempro_export_completed</code></td><td><?php esc_html_e( 'Action', 'user-export-manager-pro' ); ?></td><td><?php esc_html_e( 'Runs after a file is written (receives path, config and user count).', 'user-export-manager-pro' ); ?></td></tr>
					</tbody>
				</table>
			</div>
			<pre class="uempro-code"><code>// <?php esc_html_e( 'Example: export a field with a friendly value.', 'user-export-manager-pro' ); ?>

add_filter( 'uempro_export_value', function ( $value, $key, $user ) {
	if ( 'account_status' === $key ) {
		return 'approved' === $value ? 'Active' : 'Pending';
	}
	return $value;
}, 10, 3 );</code></pre>
		</article>

		<article class="uempro-doc" id="uempro-doc-about">
			<h2 class="uempro-doc__title"><span class="uempro-doc__icon" aria-hidden="true"><span class="dashicons dashicons-info-outline"></span></span><?php echo esc_html( $uempro_toc['about'] ); ?></h2>
			<?php
			/* translators: 1: plugin version, 2: developer name */
			$uempro_p( sprintf( __( 'User Export Manager Pro %1$s is developed and maintained by <strong>%2$s</strong>. It is free software released under the GNU General Public License v2 or later.', 'user-export-manager-pro' ), esc_html( UEMPRO_VERSION ), 'Md. Farhan Sadik' ) );
			?>
		</article>
	</div>
</div>
