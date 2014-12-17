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

/**
 * check string to url
 * @param $url
 * @return int
 */
function checkURL($url)
{
    $preg_str = '/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/';
    return preg_match($preg_str, $url);
}

/**
 * check string to mail address
 * @param $mail
 * @return int
 */
function checkMail($mail)
{
    $preg_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\\._-]+)+$/";
    return preg_match($preg_str, $mail);
}

/**
 * @param $time
 * @return bool
 */
function checkDateString($time)
{
    if(preg_match("/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/",$time)){
        return true;
    }else if(preg_match("/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/",$time)){
        return true;
    }
    return false;
}

/**
 * @param $time
 * @return bool
 */
function checkTimeString($time)
{
    if(preg_match("/^([0-9]{4}):([0-9]{2}):([0-9]{2}?)$/",$time)){
        return true;
    }
    return false;
}

/**
 * @param $time
 * @return bool
 */
function checkDateTimeString($time)
{
    if(preg_match("/^(.+) (.+)$/",$time)){
        list($d,$t) = explode(" ",$time);
        if(checkDateString($d) && checkTimeString($t)){
            return true;
        }
    }
    return false;
}