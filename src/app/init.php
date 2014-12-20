<?php
use PMP\htmlElement;
use PMP\Localize;

require_once(dirname(__FILE__).'/../class/PMP/Localize.php');

if(!function_exists("_e")){
    /**
     * @param $text
     */
    function _e($text){
        echo Localize::getText($text,Localize::getEncoding());
    }
}
if(!function_exists("__")){
    /**
     * @return mixed
     */
    function __(){
        $arg_num = func_num_args();
        if($arg_num == 1){
            return Localize::getText(func_get_arg(0),Localize::getEncoding());
        }else if($arg_num > 1){
            $args = array_merge(
                array(Localize::getText(func_get_arg(0),Localize::getEncoding())),
                array_slice(func_get_args(),1));
            return call_user_func_array("sprintf",$args);
        }
    }
}
// set locale
Localize::setLocale("ja","JP","UTF-8");
Localize::setTimeZone('Asia/Tokyo');
// language file
Localize::textDomain('application');
Localize::bindTextDomain('application',dirname(__FILE__).'/languages');

//PHPLangError::init();

/**
 * init framework file
 */
PMP\Template::filter("html_element",function(htmlElement $element,$param=array()){
    foreach($param as $key => $val){
        $element->addAttr($key,$val);
    }
    return $element;
});