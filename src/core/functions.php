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

/**
 *
 */
function autoload_class(){
    if(function_exists("spl_autoload_register")){
        function autoloader($class) {
            $dir = dirname(__FILE__).'/../class/';
            $filename = $dir.($class).'.php';
            $filename2 = $dir.strtolower($class).'.php';
            if(file_exists($filename)){
                include_once( $filename );
            } else if(file_exists($filename2)){
                include_once( $filename2 );
            }else{
                throw new PMPException("Error Class Name '".$class."'");
            }
        }
        spl_autoload_register('autoloader');
    }else{
        dir_include_all(dirname(__FILE__).'/../class');
    }
}
/**
 * @param $path
 * @param null $callback
 */
function dir_include_all($path,$callback=null) {
    if (is_dir($path)) {
        if ($dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != ".." && preg_match("/\.php$/",$file)) {
                    include_once($path."/".$file);
                    if(is_callable($callback)){
                        $callback($file);
                    }
                }
            }
            closedir($dh);
        }
    }
}

/**
 * @param $path
 */
function load_user_application($path){
    if (is_dir($path)) {
        $libs_path = $path."/libs";
        // include model
        dir_include_all($libs_path."/model",function($file){
            $model_name = ucfirst(pathinfo($file,PATHINFO_FILENAME));
            Database::addManagementModel($model_name);
        });
        dir_include_all($libs_path."/include");
        dir_include_all($libs_path."/conf");

        if ($dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    load_application_model($path."/".$file);
                }
            }
            closedir($dh);
        }
    }
}
/**
 * @param $path
 */
function load_application_model($path){
    if (!is_dir($path)) {
        return ;
    }
    $filename = $path."/init.inc.php";
    if(file_exists($filename)){
        include_once($filename);
    }
}

function __pmp(){
    $arg_num = func_num_args();
    if($arg_num == 1){
        return Localize::dgetText(PMP_TEXTDOMAIN,func_get_arg(0),Localize::getEncoding());
    }else if($arg_num > 1){
        $args = array_merge(
            array(Localize::dgetText(PMP_TEXTDOMAIN,func_get_arg(0),Localize::getEncoding())),
            array_slice(func_get_args(),1));
        return call_user_func_array("sprintf",$args);
    }
}