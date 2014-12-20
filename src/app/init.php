<?php

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