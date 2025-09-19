<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class YACS_Importer {
    public $skipped_import_columns = [];
    private $sep = '__';

    public function process_row( $csv_row ){
        global $wpdb;
        if ( ! is_array($csv_row) ) return false;
        $addons_table = $wpdb->prefix . 'yith_wapo_addons';
        $cols = $wpdb->get_results( "SHOW COLUMNS FROM {$addons_table}", ARRAY_A );
        $db_cols = $cols ? wp_list_pluck($cols,'Field'):[];
        if ( empty($db_cols) ) return false;
        $options_col = in_array('options',$db_cols)?'options':(in_array('columns',$db_cols)?'columns':null);

        $settings_obj = yacs_unflatten_array($csv_row,'settings',$this->sep);
        $options_obj  = yacs_unflatten_array($csv_row,'options',$this->sep);
        foreach(array_keys($csv_row) as $k){
            if(strpos($k,'settings__')===0||strpos($k,'options__')===0) unset($csv_row[$k]);
        }
        unset($csv_row['settings'],$csv_row['options'],$csv_row['columns']);
        if ( ! empty($settings_obj) ) $csv_row['settings'] = maybe_serialize($settings_obj);
        if ( ! empty($options_obj) && $options_col ) $csv_row[$options_col] = maybe_serialize($options_obj);
        $filtered = array_intersect_key($csv_row,array_fill_keys($db_cols,true));
        foreach(array_keys($csv_row) as $k){
            if(!in_array($k,$db_cols,true)){
                $this->skipped_import_columns[]=$k;
                error_log("[YITH Add-ons Import] Column {$k} not in {$addons_table}");
            }
        }
        if(empty($filtered)) return false;
        $formats=array_fill(0,count($filtered),'%s');
        $wpdb->replace($addons_table,$filtered,$formats);
        return true;
    }
}
