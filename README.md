# yith-wapo-csv
YITH WooCommerce Product Add-on CSV Importer / Exporter

🔎 Understanding of the Plugin
The plugin is designed to export and import YITH WooCommerce Product Add-ons (WAPO) into CSV files. It handles the tricky part of flattening complex settings and options data (which can be serialized arrays or nested JSON) into columns for CSV, and then reassembling them back when importing.

Key Features
1.	Main Plugin File (yith-addons-csv-sync-wapo.php)
o	Registers the plugin.
o	Loads helpers, exporter, importer, admin UI, and backup manager.
o	Provides access from Tools → YITH CSV Sync in the WP Admin.
2.	Admin UI (class-yacs-admin-ui.php)
o	Adds a Tools page with simple Export CSV and Import CSV forms.
o	Uses admin-post.php with nonces for security.
3.	Exporter (class-yacs-exporter.php)
o	Queries WooCommerce yith_wapo_blocks, yith_wapo_addons, and related tables.
o	Joins data with associated products/rules.
o	Flattens nested arrays/JSON (via helper functions) into CSV rows.
4.	Importer (class-yacs-importer.php)
o	Reads CSV rows back.
o	Rebuilds structured settings and options from flattened CSV.
o	Inserts/updates rows in yith_wapo_addons.
o	Keeps track of skipped/unmatched columns.
5.	Backup Manager (class-yacs-backup-manager.php)
o	Logs skipped columns (during export/import) into a file inside /backups.
o	Helps with debugging and schema drift (when DB changes but CSV schema doesn’t).
6.	Helpers (helpers.php)
o	Provides flatten/unflatten functions that also decode embedded JSON.
o	Defines excluded columns (filterable).
o	Core utility for recursive processing.
________________________________________
🚀 Strengths
•	Handles complex data structures (serialized + JSON) gracefully.
•	Schema-safe: checks actual DB columns before inserting.
•	Backup logs make it easier to debug issues.
•	Extensible with filters for excluded columns.
•	Lightweight — no unnecessary dependencies.
________________________________________
💡 Suggestions for Improvements
1.	User Interface
o	Add progress indicators or feedback messages when importing large CSV files.
o	Provide a preview of the first few rows before importing (to catch mapping errors).
o	Add download links for backup logs from the admin page.
2.	Data Safety
o	Add an automatic backup option (export existing add-ons before importing new ones).
o	Provide a rollback feature to restore from the last successful import.
3.	Error Handling
o	Better error messages (instead of silent skips).
o	Highlight mismatched or extra columns in the admin UI after import.
4.	Export/Import Options
o	Allow selective export (by product, category, or block ID).
o	Support incremental export/import (only changed add-ons).
o	Add compatibility with WP-CLI for large imports/exports.
5.	Performance
o	Batch insert during import instead of row-by-row to reduce DB load.
o	Add background processing for large files (via Action Scheduler).
6.	Advanced Features
o	Add CSV schema versioning (so future DB changes won’t break imports).
o	Optional JSON export in addition to CSV for safer round-trips.
o	Hook into WooCommerce logs for better monitoring.
________________________________________
👉 In short:
The plugin is already a solid CSV bridge for YITH WAPO, but can be improved with better usability (UI/feedback), data safety (rollback/backups), and performance (batch processing, WP-CLI).

 
📂 Tables Involved
1.	{wp_prefix}yith_wapo_blocks
o	Stores "blocks" of add-ons (groups of options).
o	Exporter queries this table as the main entry point.
2.	{wp_prefix}yith_wapo_blocks_assoc
o	Associates blocks with WooCommerce objects (products, categories, etc.).
o	Columns seen in exporter:
	object → usually product/category ID.
	type → type of association (e.g., product, category).
3.	wp_posts (native WP table)
o	Joins with yith_wapo_blocks_assoc.object to fetch post_title for readability.
4.	{wp_prefix}yith_wapo_addons
o	Stores the actual add-ons (checkboxes, radio buttons, etc.).
o	This is the core table for import/export.
o	Exporter: joins it with block_id.
o	Importer: rebuilds structured data and writes back here.
o	Columns referenced:
	block_id → foreign key to yith_wapo_blocks.
	settings → serialized/JSON settings (flattened into CSV).
	options or columns → add-on options (flattened into CSV).
________________________________________
📑 Columns Touched
From yith_wapo_blocks
•	id (primary key, block ID).
•	All columns (b.* is selected) — structure varies by version of YITH plugin.
From yith_wapo_blocks_assoc
•	rule_id (links to yith_wapo_blocks.id).
•	object (product/category ID).
•	type (association type).
From wp_posts
•	ID (used to match assoc object).
•	post_title (included in export for readability).
From yith_wapo_addons
•	block_id (links add-ons to blocks).
•	settings (flattened/unflattened).
•	options or columns (depending on schema version).
•	All other add-on fields (d.* is selected in exporter).
________________________________________
🛠 How Plugin Uses Them
•	Export
o	Fetches all blocks + associations + addons.
o	Flattens nested settings and options/columns into separate CSV columns (settings__key__subkey).
•	Import
o	Reads CSV.
o	Rebuilds nested structures into settings and options.
o	Writes back to yith_wapo_addons with REPLACE INTO (safe upsert).
o	Logs skipped/missing columns.
________________________________________
⚡ Suggested Improvements for DB Handling
1.	Schema Version Awareness
o	Currently assumes either options or columns. Adding explicit schema versioning would prevent mis-imports if YITH changes DB structure.
2.	Selective Export
o	Allow exporting add-ons for specific product IDs or categories only, instead of all blocks.
3.	Validation Before Import
o	Check if block_id exists before inserting add-ons. If not, warn user.
4.	Transaction Safety
o	Wrap import operations in MySQL transactions — so a failed row won’t leave half-imported data.
________________________________________
👉 This plugin mainly bridges yith_wapo_blocks ↔ yith_wapo_addons via block_id, while keeping associations (yith_wapo_blocks_assoc) in sync and linking to WooCommerce products (wp_posts).

 
