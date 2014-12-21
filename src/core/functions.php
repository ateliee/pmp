<?php

/**
 * output print_r
 */
function d()
{
    print '<pre style="background:#fff;color:#333;border:1px solid #ccc;margin:2px;padding:4px;font-family:monospace;font-size:12px">';
    foreach (func_get_args() as $v) {
        var_dump($v);
    }
    print '</pre>';
}
/**
 * @param $val
 * @param $default
 * @return mixed
 */
function isset_value(&$val,$default){
    return isset($val) ? $val : $default;
}
