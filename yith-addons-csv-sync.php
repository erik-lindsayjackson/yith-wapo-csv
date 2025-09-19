<?php
/**
 * Plugin Name: YITH Addons CSV Sync (Custom)
 * Description: Export/Import YITH WooCommerce Product Add-Ons with fully expanded settings (recursive), associations, and backups. Prefixed to avoid conflicts.
 * Version:     7.0.2
 * Author:      ChatGPT
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-backup-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-exporter.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-importer.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-admin-ui.php';

add_action('plugins_loaded', function(){
    $backup_manager = new YACS_Backup_Manager();
    new YACS_Exporter($backup_manager);
    new YACS_Importer($backup_manager);
    $admin_ui = new YACS_Admin_UI($backup_manager);
    add_action('admin_menu', [$admin_ui, 'register_page']);
    add_action('admin_notices', [$admin_ui, 'render_notices']);
});
