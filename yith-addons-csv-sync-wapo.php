<?php
/**
 * Plugin Name: YITH Add-ons CSV Sync
 * Description: Export/Import YITH WooCommerce Product Add-ons with recursive flattening of settings/options (handles serialized + JSON inside), schema-safe joins to yith_wapo_addons via block_id, and backup logs. Access via Tools -> YITH CSV Sync.
 * Plugin URI: https://lindsayjackson.com.au
 * Version: 1.5.0
 * Author: Lindsay Jackson
 * Author URI: https://lindsayjackson.com.au
 * Text Domain: yacs
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'YACS_VERSION' ) ) define( 'YACS_VERSION', '1.4.0' );

require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-exporter.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-importer.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-backup-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-yacs-admin-ui.php';

add_action('admin_menu', function() {
    add_management_page(
        __('YITH Add-ons CSV Sync','yacs'),
        __('YITH CSV Sync','yacs'),
        'manage_options',
        'yacs-tools',
        ['YACS_Admin_UI','render_tools_page']
    );
});

add_action('admin_post_yacs_export', function(){
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('yacs_export');
    $exporter = new YACS_Exporter();
    $exporter->stream_csv();
    exit;
});

add_action('admin_post_yacs_import', function(){
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('yacs_import');
    if ( empty($_FILES['import_file']['tmp_name']) ) wp_die(__('No file uploaded','yacs'));
    $importer = new YACS_Importer();
    $fh = fopen($_FILES['import_file']['tmp_name'],'r');
    $header = fgetcsv($fh);
    while( ($row = fgetcsv($fh)) !== false ){
        $assoc = array_combine($header, $row);
        $importer->process_row($assoc);
    }
    fclose($fh);
    $bm = new YACS_Backup_Manager();
    $bm->write_skipped_columns_log($importer->skipped_export_columns ?? [], $importer->skipped_import_columns ?? []);
    wp_safe_redirect( admin_url('tools.php?page=yacs-tools&yith_addons_import=1') );
    exit;
});
