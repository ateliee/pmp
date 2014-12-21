<?php
namespace PMP;

/**
 * Class FormElement
 * @package PMP
 */
class FormElement{
    private $type;
    private $attr;
    private $prex;
    private $value;

    function __construct($type,$prex=NULL){
        $this->type = $type;
        $this->attr = array();
        $this->prex = $prex;
        $this->value = null;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $attr
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;
    }

    /**
     * @return mixed
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getAttrValue($key,$default=null)
    {
        if(isset($this->attr[$key])){
            return $this->attr[$key];
        }
        return $default;
    }

    /**
     * @return null
     */
    public function getPrex()
    {
        return $this->prex;
    }

    /**
     * @param mixed $prex
     */
    public function setPrex($prex)
    {
        $this->prex = $prex;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}

/**
 * Class Form
 * @package PMP
 */
class Form{
    private $elem;
    private $request;
    private $errors;
    private $models;

    function __construct(){
        $this->elem = array();
        $this->errors = array();
        $this->models = array();
    }

    /**
     * @param $name
     * @param $type
     * @param array $attr
     * @param null $prex
     * @return $this
     */
    public function add($name,$type,$attr=array(),$prex=NULL){
        $key = $name;
        if(isset($this->elem[$key])){
            $this->elem[$key]->setType($type);
            $this->elem[$key]->setAttr(array_merge($this->elem[$key]->getAttr(),$this->makeAttr($key,$type,$attr)));
            if($prex !== NULL){
                $this->elem[$key]->setPrex($prex);
            }
        }else{
            $elm = new FormElement($type,$prex);
            $elm->setAttr($this->makeAttr($name,$type,is_array($attr) ? $attr : array()));
            $this->addElement($key,$elm);
        }
        return $this;
    }

    /**
     * @param $key
     * @param FormElement $elm
     */
    public function addElement($key,FormElement $elm)
    {
        $this->elem[$key] = $elm;
    }

    /**
     * @param $name
     * @param $type
     * @param $attr
     * @return mixed
     */
    protected function makeAttr($name,$type,$attr){
        return $attr;
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    public function setValue($name,$value){
        if(isset($this->elem[$name])){
            $this->elem[$name]->setValue($value);
            return true;
        }
        return false;
    }

    /**
     * @param $name
     * @return null
     */
    public function getValue($name){
        if(isset($this->elem[$name])){
            return $this->elem[$name]->getValue();
        }
        return null;
    }

    /**
     * @param Model $model
     * @return $this
     */
    public function createFormModel(Model &$model,$multi_form=false){
        $table_name = $model->getTablename();

        $fields = $model->getFormColumns();
        foreach($fields as $key => $val){
            $prex = "";
            if($multi_form){
                $prex = $table_name."-";
            }
            $this->add($key,$val["type"],$val["attr"],$prex);
            $this->setValue($key,$val["value"]);
        }
        $this->models[$table_name] = $model;
        return $this;
    }

    /**
     * @return $this
     */
    public function getForm(){
        return $this->getString();
    }

    /**
     * @return array
     */
    public function getString(){
        $tags = array();
        $labels = array();
        $values = array();
        foreach($this->elem as $name => $value){
            $val = $value->getValue();
            $val = $val ? $val : "";
            $attr = $value->getAttr();
            $attr["name"] = $value->getPrex().$name;

            $id = "";
            if(isset($attr["id"])){
                $id = $attr["id"];
            }
            $label = $attr["name"];
            if(isset($attr["label"])){
                $label = $attr["label"];
            }
            $label = new htmlElement('label',array('for' => $id),$this->escape($label));

            $tags[$name] = $this->getStringHTML($value->getType(),$val,$attr);
            $values[$name] = $val;

            $labels[$name] = $label;
        }
        $form = array();
        $form["tags"] = $tags;
        $form["label"] = $labels;
        $form["value"] = $values;
        $form["errors"] = $this->errors ? $this->errors : array();
        return $form;
    }

    /**
     * @param $type
     * @param $value
     * @param $attr
     * @return htmlElement|htmlElementList|null
     */
    private function getStringHTML($type,$value,$attr){
        $html = null;

        $label = "";
        if(isset($attr["label"])){
            $label = $attr["label"];
            unset($attr["label"]);
        }
        $type = $this->convertToFormType(strtolower($type));
        switch($type){
            case "select":
                $options = array();
                if(isset($attr["choices"])){
                    $options = $attr["choices"];
                }

                $list = array();
                if(is_array($options)){
                    $list = $options;
                }else if(is_object($options) && ($options instanceof Model)){
                    $results = $options->findQuery()->getResults();
                    if($results){
                        foreach($results as $val){
                            $list[$val["id"]] = $val["name"];
                        }
                    }
                }else if(is_callable($options)){
                    $list = $options();
                }
                $inner_html = '';
                foreach($list as $k => $v){
                    $opt = array();
                    if($value == $k){
                        $opt["selected"] = "selected";
                    }
                    $opt["label"] = $v;
                    $inner_html .= $this->getStringHTML("option",$k,$opt);
                }
                $html = new htmlElement('select',$this->getAttrHTML($attr),$inner_html,false);
                break;
            case "option":
                $html = new htmlElement('option',array_merge(
                    $this->getAttrHTML($attr),
                    array('type' => $type,'value' => $value)),
                    $this->escape($label),false);
                break;
            case "radio":
            case "checkbox":
                $html = new htmlElementList();
                $html->addElement(
                    new htmlElement('input',array_merge(
                    $this->getAttrHTML($attr),
                    array('type' => $type)))
                );
                $html->addElement(new htmlEmptyElement($this->escape($label)));
                break;
            case "textarea":
                $html = new htmlElement('textarea',$this->getAttrHTML($attr),$value,false);
                break;
            case "password":
                $html = new htmlElement('input',array_merge(
                    $this->getAttrHTML($attr),
                    array('type' => $type)));
                break;
            case "text":
            case "email":
            default:
                $html = new htmlElement('input',array_merge(
                    $this->getAttrHTML($attr),
                    array('type' => $type,'value' => $value)));
                break;
        }
        return $html;
    }

    /**
     * extend form type to output type
     *
     * @param $type
     * @return mixed
     */
    private function convertToFormType($type){
        if($type == "time"){
            return "input";
        }
        return $type;
    }

    /**
     * @param $attr
     * @return array
     */
    private function getAttrHTML($attr){
        $list = array();
        foreach($attr as $k => $v){
            if(in_array($k,array(
                "format",
                "choices"
            ))){
                continue;
            }
            $list[$k] = $v;
        }
        return $list;
    }

    /**
     * @param $str
     * @return string
     */
    private function escape($str){
        return htmlspecialchars($str, ENT_QUOTES, mb_internal_encoding());
    }

    /**
     * @param RequestVars $request
     * @return $this
     */
    public function bindRequest(RequestVars $request){
        $this->request = $request;
        foreach($this->elem as $name => $value){
            $v = $this->request->get($value->getPrex().$name,null);
            if($v !== null){
                $this->setValue($name,$v);
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(){
        $this->errors = array();
        if($this->request){
            $request = $this->request;
            $check_all = true;
            foreach($this->elem as $key => $val){
                $check = true;
                $label = $val->getAttrValue('label',$key);
                $form_key = $val->getPrex().$key;
                $value = $request->get($form_key,"");
                if($val->getAttrValue('required',false) === true){
                    if(($request->is($form_key)) && ($value != "")){
                        $check = true;
                    }else{
                        $this->errors[$key] = $this->getErrorMessage("required",$label);
                        $check = false;
                    }
                }
                if($value != ""){
                    if($check && $val->getAttrValue("format")){
                        if(!preg_match("/^".$val->getAttrValue("format")."$/",$value)){
                            $this->errors[$key] = $this->getErrorMessage("format",$label);
                            $check = false;
                        }
                    }
                }
                if(($request->is($form_key)) && ($value != "")){
                    if($val->getType() == "email"){
                        if(!$this->checkMail($value)){
                            $this->errors[$key] = $this->getErrorMessage("email",$label);
                            $check = false;
                        }
                    }else if($val->getType() == "url"){
                        if(!$this->checkURL($value)){
                            $this->errors[$key] = $this->getErrorMessage("url",$label);
                            $check = false;
                        }
                    }else if($val->getType() == "date"){
                        if(!$this->checkDateString($value)){
                            $this->errors[$key] = $this->getErrorMessage("date",$label);
                            $check = false;
                        }
                    }else if($val->getType() == "datetime"){
                        if(!$this->checkDateTimeString($value)){
                            $this->errors[$key] = $this->getErrorMessage("datetime",$label);
                            $check = false;
                        }
                    }else if($val->getType() == "time"){
                        if(!$this->checkTimeString($value)){
                            $this->errors[$key] = $this->getErrorMessage("time",$label);
                            $check = false;
                        }
                    }else if($val->getType() == "password"){
                        if(!preg_match("/^([0-9a-zA-Z]*)$/",$value)){
                            $this->errors[$key] = $this->getErrorMessage("password",$label);
                            $check = false;
                        }
                    }
                    if($check && $val->getAttrValue("choices")){
                        $choices = $val->getAttrValue("choices");
                        $c = true;
                        if(!$value){
                            $c = false;
                        }else if(is_array($choices) && !isset($choices[$value])){
                            $c = false;
                        }else if(is_object($choices) && ($choices instanceof Model)){
                            if(!$choices->findQuery(intval($value))->getResults()){
                                $c = false;
                            }
                        }else if(is_callable($choices)){
                            if(!isset($choices()[$value])){
                                $c = false;
                            }
                        }
                        if(!$c){
                            $this->errors[$key] = $this->getErrorMessage("choices",$label);
                            $check = false;
                        }
                    }
                }
                if($check){
                    foreach($this->models as $k => $v){
                        if($v->isExists($key)){
                            if($v->get($key)->getDBColumn()->isUnique()){
                                $count = count($v->findBy(array($key => intval($value))));
                                $check = ($count > 0) ? false : true;
                            }
                            $v->setParameter($key,$value);
                        }
                    }
                }
                if(!$check){
                    $check_all = false;
                }
            }
            return $check_all;
        }
        return false;
    }

    /**
     * @param $type
     * @param $key
     * @return string
     */
    private function getErrorMessage($type,$key){
        switch($type){
            case "required":
                return __('input required from %1$s.',$key);
                break;
            case "email":
                return __('error format email from %1$s.',$key);
                break;
            case "url":
                return __('error format url from %1$s.',$key);
                break;
            case "date":
                return __('error format date from %1$s.',$key);
                break;
            case "datetime":
                return __('error format datetime from %1$s.',$key);
                break;
            case "time":
                return __('error format time from %1$s.',$key);
                break;
            case "password":
                return __('error format password from %1$s.',$key);
                break;
            case "format":
                return __('error format from %1$s.',$key);
                break;
            case "choices":
                return __('error choices select from %1$s.',$key);
                break;
            default:
                break;
        }
    }

    /**
     * check string to url
     * @param $url
     * @return int
     */
    protected function checkURL($url)
    {
        $preg_str = '/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/';
        return preg_match($preg_str, $url);
    }

    /**
     * check string to mail address
     * @param $mail
     * @return int
     */
    protected function checkMail($mail)
    {
        $preg_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\\._-]+)+$/";
        return preg_match($preg_str, $mail);
    }

    /**
     * @param $time
     * @return bool
     */
    protected function checkDateString($time)
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
    protected function checkTimeString($time)
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
    protected function checkDateTimeString($time)
    {
        if(preg_match("/^(.+) (.+)$/",$time)){
            list($d,$t) = explode(" ",$time);
            if($this->checkDateString($d) && $this->checkTimeString($t)){
                return true;
            }
        }
        return false;
    }
}