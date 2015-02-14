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
 * routing
 */
PMP\Template::filter("path",function(){
    $routing = new \PMP\Routing();
    return call_user_func_array(array($routing , 'getUrl'),func_get_args());
});

/**
 * form template function
 */
\PMP\Template::filter('form_start',function(\PMP\Template $template,$form,$options){
    if($form instanceof \PMP\FormView){
        $params = array(
            'action' => $form->getUrl(),
            'method' => 'POST',
            'attr' => array()
        );
        foreach($options as $key => $val){
            if(!array_key_exists($key,$params)){
                throw new \PMP\PMPException(
                    sprintf('form_start() option "%s" not Support. param is %s',$key,implode(' or ',array_keys($params)))
                );
            }
            $params[$key] = $val;
        }
        $attr = array();
        $attr['action'] = $params['action'];
        $attr['method'] = $params['method'];
        $attr = array_merge($attr,$params['attr']);

        $attr_tag = array();
        foreach($attr as $key => $val){
            $attr_tag[] = ' '.$key.'="'.$val.'"';
        }

        $tag = '<form'.implode('',$attr_tag).'>';
        return $tag;
    }else{
        throw new \PMP\PMPException('form_start() 1 paramater is not FormView.');
    }
},true);
/**
 * form template function
 */
\PMP\Template::filter('form_end',function(\PMP\Template $template,$form,$options){
    if($form instanceof \PMP\FormView){
        $params = array(
            'form_rest' => true,
        );
        foreach($options as $key => $val){
            if(!array_key_exists($key,$params)){
                throw new \PMP\PMPException(
                    sprintf('form_end() option "%s" not Support. param is %s',$key,implode(' or ',array_keys($params)))
                );
            }
            $params[$key] = $val;
        }

        $tag = '';
        if($params['form_rest']){
            $tag .= \PMP\Template::callFilter('form_rest',$form);
        }
        $tag .= '</form>';
        return $tag;
    }else{
        throw new \PMP\PMPException('form_start() 1 paramater is not FormView.');
    }
},true);
/**
 * form template function
 */
\PMP\Template::filter('form_rows',function(\PMP\Template $template,$form){
    $tags = array();
    if($form instanceof \PMP\FormView){
        foreach($form->getElement() as $key => $val){
            $tags[] = \PMP\Template::callFilter('form_row',$val);
        }
    }else{
        throw new \PMP\PMPException('form_rows() paramater is not FormView.');
    }
    return implode('',$tags);
},true);
\PMP\Template::filter('form_row',function(\PMP\Template $template,$form,$attr=array()){
    $tag = $template->callFilter('form_label',$form);
    $tag .= $template->callFilter('form_wedget',$form,$attr);
    return $tag;
},true);
\PMP\Template::filter('form_label',function($form,$attr=array()){
    if(!($form instanceof \PMP\FormElement)){
        throw new \PMP\PMPException('form_label() paramater is not instanceof FormElement.');
    }
    $label = new htmlElement('label',
        array_merge($attr,array('for' => $form->getFormId())),htmlElement::escape($form->getFormLabel()),false);
    return $label;
});
\PMP\Template::filter('html_element_set',function(\PMP\Template $template,htmlElement $html_element){
    $param = array(
        'tagName' => $html_element->getTagName(),
        'childs' => $html_element->getChilds(),
        'innerHtml' => $html_element->getInnerHtml(),
        'attr' => $html_element->getAttr(),
    );
    $template->assign_vars($param);
},true);
\PMP\Template::filter('html_element_set_checkbox',function(\PMP\Template $template,$childs){
    $checkbox = array();
    for($i=0;$i<count($childs);$i+=2){
        $checkbox[] = array(
            'input' => $childs[$i],
            'label' => $childs[$i+1]
        );
    }
    $template->assign_vars(array('childs' => $checkbox));
},true);
\PMP\Template::filter('form_wedget',function(\PMP\Template $template,$form,$attr=array()){
    if(!($form instanceof \PMP\FormElement)){
        throw new \PMP\PMPException('form_label() paramater is not instanceof Form.');
    }
    $form->setOutput(true);

    $tag = $form->getTag($attr);
    $block = null;
    if(!($block = $template->getBlock('form_wedget_'.$form->getFormId()))){
        if(!($block = $template->getBlock('form_wedget_'.$form->getType()))){
        }
    }
    if($block){
        $template->callFilter('html_element_set',$tag);
        return $template->set_display_template($block);
    }
    return $tag;
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
\PMP\Template::filter('form_errors',function(\PMP\Template $template,$form){
    $errors = array();
    if($form instanceof \PMP\FormView){
        foreach($form->getElement() as $key => $val){
            $errors[] = $template->callFilter('form_error',$val);
        }
    }else{
        throw new \PMP\PMPException('form_errors() paramater is not FormView.');
    }
    return implode('',$errors);
},true);
\PMP\Template::filter('form_error',function($form){
    $errors = null;
    if($form instanceof \PMP\FormElement){
        if($error = $form->getError()){
            $errors = $error;
        }
    }else{
        throw new \PMP\PMPException('form_errors() paramater is not FormElement.');
    }
    return $errors;
});