=== User Export Manager Pro ===
Contributors: farhansadik
Tags: export users, import users, user export, csv, user meta
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Export and import users with their custom profile fields — CSV, Excel, JSON or XML — with reusable export templates and a downloadable history.

== Description ==

User Export Manager Pro gives you full control over exporting — and importing — the people registered on your WordPress site, including the custom profile fields added by other plugins.

Everything lives under **User Export** in your wp-admin menu, in a full-width dashboard with a sidebar for Dashboard, Export users, Import users, Templates, History, Settings and Documentation.

= Choose who to export =

* **All users, by role, by registration date or hand-picked** — select individual users from a searchable, paged list.
* **Search and sort** — narrow any selection with a search term and choose the sort order.
* **Live counter** — see how many users match before you export.

= Choose exactly which fields become columns =

* **WordPress profile fields** — ID, username, email, first and last name, display name, nickname, role, website, registered date, biographical info and more.
* **Custom fields detected automatically** — ACF, Ultimate Member, WooCommerce billing and shipping, BuddyPress, Meta Box, JetEngine, registration form plugins and your own code. A **Scan custom fields** button refreshes the list at any time.
* **Every field listed once** — hidden reference keys (such as ACF's `_field` copies) are filtered out, and admin preferences are tucked into a collapsible "Hidden & plugin data" group.
* **Column order** — drag columns into the order you want.

= Four export formats =

* **CSV** — configurable separator and UTF-8 marker for Excel.
* **Excel (XLSX)** — a styled, frozen header row and filter buttons, with no external libraries needed.
* **JSON** — for developers and APIs.
* **XML** — for reporting systems.

= Import users =

* **Import the files you export** — any CSV, Excel, JSON or XML file exported by the plugin can be imported again unchanged, so you can move users between sites or edit them in Excel and import the changes back.
* **Files from other tools work too** — comma, semicolon and tab separated CSV files are detected automatically.
* **Automatic column matching** — columns are matched to fields by name, meta key or common words; change anything, skip columns or save a column as a new custom field.
* **Create or update** — find existing users by email, username or ID, then update or skip them; create new users with a default role.
* **Safe by design** — you can only assign roles you are allowed to assign, your own role is never changed, users are not emailed about bulk changes, and every failed row is reported with its row number and reason.

= Templates =

* **Save any export as a template** — for example "Subscribers — username, email, last name and education".
* **One click** — run it again with Export now, or open it in the wizard with every field pre-selected.
* **Manage** — create, edit, duplicate and delete templates from the Templates screen.

= Built for big sites =

* **Batched exports and imports** — work in small batches with a progress bar, so sites with hundreds of thousands of users finish without timeouts.

= History and safety =

* **History** — every export and import is listed with who ran it; export files can be downloaded again.
* **Automatic cleanup** — old files are deleted after a number of days you choose.
* **Protected files** — exports are stored in a protected folder and served only to authorised users through WordPress.
* **Formula protection** — spreadsheet formula injection protection is on by default.
* **Built-in documentation** — a complete guide inside wp-admin.

== Installation ==

1. Upload the `user-export-manager-pro` folder to `/wp-content/plugins/`, or install the plugin through the Plugins screen in WordPress.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **User Export > Export users** to export, or **User Export > Import users** to import.

== Frequently Asked Questions ==

= Can I export users' passwords? =

No plugin can export readable passwords, because WordPress only stores a one-way hash. If you are moving users to another WordPress site, an administrator can enable "Allow exporting password hashes" in Settings; the hash keeps working on the new site when you import the file there.

= Can I import a file I exported with this plugin? =

Yes. Files exported by the plugin in any format are recognised and their columns are matched automatically on the Import users screen.

= A custom field I need is not listed. =

Custom fields are detected automatically. A meta key exists only after at least one user has a value for it — make sure one user has filled it in, then click "Scan custom fields". Keys that start with an underscore are listed in the "Hidden & plugin data" group.

= Why did fields appear twice in version 1.0? =

ACF and similar plugins store a hidden reference key (for example `_district`) next to each value (`district`). Since version 1.1 these reference keys are removed, so every field is listed exactly once.

= Who can export and import users? =

Exporting needs the `list_users` capability (administrators by default). Importing needs `create_users` and `edit_users`. Settings and deleting history require `manage_options`. Developers can change the export capability with the `uempro_capability` filter.

= Where are export files stored? =

In `wp-content/uploads/uempro-exports/`. The folder blocks direct web access on Apache and IIS and file names contain a random code. On Nginx, add a rule denying access to that folder for full protection.

= Does the plugin send data anywhere? =

No. The plugin makes no external requests.

== Screenshots ==

1. Dashboard with site totals, quick actions, one-click templates and recent activity.
2. Choosing which users to export.
3. Field picker with automatically detected custom fields and the column order strip.
4. Importing users — automatic column matching.
5. Template cards with Export now, Edit, Duplicate and Delete.
6. History of exports and imports.
7. Built-in documentation.

== Changelog ==

= 1.2.0 =
* New: Import users from CSV, Excel (XLSX), JSON or XML — including any file exported by the plugin — with automatic column matching, create/update/skip options and a per-row problem report.
* New: full-width admin interface with a left sidebar menu.
* New: "Scan custom fields" button with the time of the last scan; custom fields are still detected automatically.
* New: History lists imports as well as exports.
* Fix: all Plugin Check "UnescapedDBParameter" warnings — table names are now passed through `$wpdb->prepare()` with the `%i` identifier placeholder.
* Fix: "Tested up to" updated to 7.1.
* Tweak: requires WordPress 6.2 or newer (needed for the `%i` placeholder).
* Tweak: code reorganised into includes/, modules/, traits/ and templates/ with prefixed classes and a central hook loader.

= 1.1.0 =
* Fixed: custom fields were listed twice after scanning (hidden ACF reference keys and core fields are now removed).
* Fixed: templates did not load their fields into the export wizard.
* New: create, edit, duplicate and delete templates from the Templates screen.
* New: "Export now" runs a template in one click.
* New: batched exports with a progress bar — no more 500-user cap.
* New: drag-and-drop column order.
* New: Excel files with styled header, frozen row and filters.
* New: CSV separator, UTF-8 marker, date format and formula-protection settings.
* New: automatic cleanup of old export files.
* New: built-in documentation page.
* Improved: completely redesigned admin interface.
* Improved: security hardening of the exports folder and downloads.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.2.0 =
Adds user import, a full-width interface with a sidebar menu, and fixes all Plugin Check warnings. Requires WordPress 6.2 or newer.
