<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wpdf_yith_wapo_get_excluded_columns(){
    $excluded = [ 'settings_name', 'settings_rules_show_in' ];
    return apply_filters('wpdf_yith_wapo_excluded_columns', $excluded);
}

/** Recursive flatten that also decodes JSON strings */
function yacs_flatten_array( $array, $prefix = '', $sep = '__' ){
    $result = [];
    foreach ( (array) $array as $key => $val ){
        $new_key = $prefix === '' ? $key : $prefix . $sep . $key;
        if ( is_string($val) ){
            $maybe_json = json_decode($val, true);
            if ( json_last_error() === JSON_ERROR_NONE && is_array($maybe_json) ){
                $val = $maybe_json; // expand JSON string
            }
        }
        if ( is_array($val) ){
            $result = array_merge( $result, yacs_flatten_array($val, $new_key, $sep) );
        } else {
            if ( is_bool($val) ) $val = $val ? '1' : '0';
            elseif ( is_object($val) ) $val = wp_json_encode($val);
            $result[$new_key] = (string) $val;
        }
    }
    return $result;
}

function yacs_unflatten_array( $flat, $prefix, $sep = '__' ){
    $out = [];
    $start = $prefix . $sep;
    $len = strlen($start);
    foreach ($flat as $k => $v){
        if ( $k === $prefix ){
            $out = $v;
            continue;
        }
        if ( strpos($k, $start) === 0 ){
            $path = explode($sep, substr($k, $len));
            $ref =& $out;
            foreach( $path as $i => $p ){
                if ( $i === count($path) - 1 ){
                    $decoded = json_decode($v, true);
                    $ref[$p] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $v;
                } else {
                    if ( ! isset($ref[$p]) || ! is_array($ref[$p]) ) $ref[$p] = [];
                    $ref =& $ref[$p];
                }
            }
            unset($ref);
        }
    }
    return $out;
}
