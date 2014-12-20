<?php
/**
 * PMP Application
 */
// config file put loading
require_once(dirname(__FILE__).'/app/init.php');
dir_include_all(dirname(__FILE__).'/src/core');
autoload_class();


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
require_once(dirname(__FILE__) . '/../class/template.php');

Template::filter("html_element",function(htmlElement $element,$param=array()){
    foreach($param as $key => $val){
        $element->addAttr($key,$val);
    }
    return $element;
});