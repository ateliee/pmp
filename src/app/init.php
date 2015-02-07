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
PMP\Template::filter('html_element',function(htmlElement $element,$param=array()){
    foreach($param as $key => $val){
        $element->addAttr($key,$val);
    }
    return $element;
});
/**
 * form template function
 */
\PMP\Template::filter('form_label',function($form,$attr=array()){
    if(!($form instanceof \PMP\FormElement)){
        throw new \PMP\PMPException('form_label() paramater is not instanceof Form.');
    }
    $label = new htmlElement('label',
        array_merge($attr,array('for' => $form->getFormId())),htmlElement::escape($form->getFormLabel()));
    return $label;
});
\PMP\Template::filter('form_wedget',function(\PMP\Template $template,$form,$attr=array()){
    if(!($form instanceof \PMP\FormElement)){
        throw new \PMP\PMPException('form_label() paramater is not instanceof Form.');
    }
    $form->setOutput(true);
    return $form->getTag($attr);
},true);
\PMP\Template::filter('form_rest',function($form){
    $output = '';
    foreach($form as $key => $val){
        if(!($val instanceof \PMP\FormElement)){
            continue;
        }
        if(!$val->getOutput()){
            $output .= $val->getTag();
        }
    }
    return $output;
});

\PMP\Template::filter('form_errors',function($form){
    $errors = array();
    if(is_array($form)){
        foreach($form as $key => $val){
            $errors[] = \PMP\Template::callFilter('form_error',$val);
        }
    }else{
        throw new \PMP\PMPException('form_errors() paramater is not array or FormElement.');
    }
    return implode('',$errors);
});
\PMP\Template::filter('form_error',function($form){
    $errors = null;
    if($form instanceof \PMP\FormElement){
        if($error = $form->getError()){
            $errors = $error;
        }
    }else{
        throw new \PMP\PMPException('form_errors() paramater is not array or FormElement.');
    }
    return $errors;
});