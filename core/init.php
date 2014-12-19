<?php
/**
 * init framework file
 */
include_once(dirname(__FILE__) . '/../class/template.class.php');

Template::filter("html_element",function(htmlElement $element,$param=array()){
    foreach($param as $key => $val){
        $element->addAttr($key,$val);
    }
    return $element;
});