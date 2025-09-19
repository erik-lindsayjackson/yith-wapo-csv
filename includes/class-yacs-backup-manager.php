<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class YACS_Backup_Manager {
    public function write_skipped_columns_log($skipped_export=[],$skipped_import=[]){
        $dir=plugin_dir_path(__FILE__).'../backups'; if(!file_exists($dir)) wp_mkdir_p($dir);
        $ver=defined('YACS_VERSION')?YACS_VERSION:'unknown'; $ts=gmdate('Y-m-d H:i:s');
        $lines=["YITH Add-ons Export/Import skipped columns report","Plugin version: {$ver}","Timestamp (UTC): {$ts}","===================================","","Export:",!empty($skipped_export)?implode(', ',$skipped_export):"No skipped columns","","Import:",!empty($skipped_import)?implode(', ',$skipped_import):"No skipped columns",""];
        file_put_contents(trailingslashit($dir).'skipped-columns.log',implode(PHP_EOL,$lines));
    }
}
