<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class YACS_Admin_UI {
    public static function render_tools_page(){
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('YITH Add-ons CSV Sync','yacs'); ?></h1>
            <p><?php esc_html_e('Export or import YITH WooCommerce Product Add-ons. Serialized + nested JSON settings/options are expanded into readable CSV columns.','yacs'); ?></p>
            <h2><?php esc_html_e('Export','yacs'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('yacs_export'); ?>
                <input type="hidden" name="action" value="yacs_export">
                <?php submit_button(__('Export CSV','yacs')); ?>
            </form>
            <h2><?php esc_html_e('Import','yacs'); ?></h2>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('yacs_import'); ?>
                <input type="hidden" name="action" value="yacs_import">
                <input type="file" name="import_file" accept=".csv" required />
                <?php submit_button(__('Import CSV','yacs')); ?>
            </form>
        </div>
        <?php
    }
}
add_action('admin_notices', function(){
    if(!empty($_GET['yith_addons_import'])){
        echo '<div class="notice notice-success is-dismissible"><p><strong>YITH Add-ons Import complete.</strong></p><p>See backups/skipped-columns.log for report.</p></div>';
    }
});
