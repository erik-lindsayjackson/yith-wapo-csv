<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class YACS_Exporter {
    private $sep = '__';

    public function get_rows(){
        global $wpdb;
        $tbl_blocks = $wpdb->prefix . 'yith_wapo_blocks';
        $tbl_assoc  = $wpdb->prefix . 'yith_wapo_blocks_assoc';
        $tbl_posts  = $wpdb->posts;
        $tbl_addons = $wpdb->prefix . 'yith_wapo_addons';

        $sql = "
            SELECT b.*, a.object AS assoc_object, a.type AS assoc_type,
                   p.post_title AS assoc_post_title, d.*
            FROM {$tbl_blocks} b
            LEFT JOIN {$tbl_assoc} a ON b.id = a.rule_id
            LEFT JOIN {$tbl_posts} p ON a.object = p.ID
            LEFT JOIN {$tbl_addons} d ON d.block_id = b.id
        ";
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if ( ! is_array($rows) ) $rows = [];

        $excluded = wpdf_yith_wapo_get_excluded_columns();
        $out = [];
        foreach( $rows as $row ){
            // Settings
            $settings_data = maybe_unserialize( $row['settings'] ?? null );
            unset($row['settings']);
            if ( is_array($settings_data) ){
                $flat = yacs_flatten_array($settings_data, 'settings', $this->sep);
                $row = array_merge($row, $flat);
            } elseif ( ! empty($settings_data) ) {
                $row['settings'] = $settings_data;
            }
            // Options/Columns
            $options_json = null;
            if ( ! empty($row['options']) ){
                $options_json = $row['options'];
                unset($row['options']);
            } elseif ( ! empty($row['columns']) ){
                $options_json = $row['columns'];
                unset($row['columns']);
            }
            $options_data = maybe_unserialize( $options_json );
            if ( is_array($options_data) ){
                $flat = yacs_flatten_array($options_data, 'options', $this->sep);
                $row = array_merge($row, $flat);
            } elseif ( ! empty($options_data) ) {
                $row['options'] = $options_data;
            }
            foreach($excluded as $ex){ if (isset($row[$ex])) unset($row[$ex]); }
            $out[] = $row;
        }
        return $out;
    }

    public function stream_csv(){
        $rows = $this->get_rows();
        $headers = [];
        foreach ($rows as $r){ foreach(array_keys($r) as $k){ if(!in_array($k,$headers,true)) $headers[]=$k; } }
        usort($headers, function($a,$b){
            $pa = (strpos($a,'settings__')===0)?2:((strpos($a,'options__')===0)?3:1);
            $pb = (strpos($b,'settings__')===0)?2:((strpos($b,'options__')===0)?3:1);
            return ($pa===$pb)?strcmp($a,$b):($pa<=>$pb);
        });
        $suffix = gmdate('Ymd-Hi');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=yith-addons-export-'.$suffix.'.csv');
        $out=fopen('php://output','w');
        fputcsv($out,$headers);
        foreach($rows as $r){
            $line=[]; foreach($headers as $h){ $line[]= $r[$h]??''; } fputcsv($out,$line);
        }
        fclose($out);
    }
}
