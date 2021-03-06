<?php
namespace PMP;

/**
 * Class FormError
 * @package PMP
 *
 * TODO: 自動コンバート・int,floatチェック
 */
class FormError
{
    private $errors;
    function __construct($key=null,$message=null){
        $this->errors = array();
        $this->add($key,$message);
    }

    /**
     * @param $key
     * @param $message
     */
    function add($key,$message)
    {
        if($key && $message){
            $this->errors[$key] = $message;
        }
    }

    /**
     * @return int
     */
    function count()
    {
        return count($this->errors);
    }

    /**
     * @return string
     */
    function __toString()
    {
        return implode("\n",$this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}

/**
 * Class FormElement
 * @package PMP
 */
class FormElement{
    /**
     * public type
     */
    static $TYPE_HIDDEN = 'hidden';
    static $TYPE_TEXT = 'text';
    static $TYPE_TEXTAREA = 'textarea';
    static $TYPE_SELECT = 'select';
    static $TYPE_CHECKBOX = 'checkbox';
    static $TYPE_CHECKLIST = 'checklist';
    static $TYPE_RADIO = 'radio';
    static $TYPE_EMAIL = 'email';
    static $TYPE_URL = 'url';
    static $TYPE_DATE = 'date';
    static $TYPE_DATETIME = 'datetime';
    static $TYPE_TIME = 'time';
    static $TYPE_PASSWORD = 'password';

    static $TYPE_LIST = array(
        'hidden',
        'text',
        'textarea',
        'select',
        'checkbox',
        'checklist',
        'radio',
        'password',
        'email',
        'url',
        'date',
        'datetime',
        'time',
        'password',
    );
    /**
     * public attr
     */
    static $ATTR_ATTR = 'attr';
    static $ATTR_FORMAT = 'format';
    static $ATTR_CHOICES = 'choices';
    static $ATTR_LABEL = 'label';
    static $ATTR_MAXLENGTH = 'maxlength';
    static $ATTR_REQUIRED = 'required';
    static $ATTR_VALUE = 'value';
    /**
     * private attr
     */
    static $ATTR_LIST = array(
        'attr',
        'format',
        'choices',
        'label',
        'maxlength',
        'required',
        'value',
    );

    private $type;
    private $attr;
    private $prex;
    private $value;
    private $isArray;

    private $name;
    private $error;
    private $output;

    function __construct($type,$prex=NULL){
        $type = strtolower($type);
        if(!in_array($type,self::$TYPE_LIST)){
            throw new \Exception(sprintf('Not support Form Type [%s].Support Is "%s"',$type,implode('" or "',self::$TYPE_LIST)));
        }
        $this->type = $type;
        $this->attr = array();
        $this->prex = $prex;
        $this->value = null;
        $this->isArray = false;

        $this->name = null;
        $this->error = null;
        $this->output = false;
    }

    /**
     * @param boolean $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return boolean
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getFormLabel()
    {
        return $this->getAttrValue(self::$ATTR_LABEL,$this->getName());
    }

    /**
     * @return null
     */
    public function getFormName()
    {
        return $this->getPrex().$this->getName();
    }

    /**
     * @return string
     */
    public function getFormId()
    {
        return $this->getPrex().$this->getName();
    }

    /**
     * @param FormError $error
     */
    public function setError(FormError $error)
    {
        $this->error = $error;
    }

    /**
     * @return null|FormError
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $attr
     */
    public function setAttr($attr)
    {
        $this->attr = array();
        foreach($attr as $key => $val){
            $this->setAttrValue($key,$val);
        }
    }

    /**
     * @param mixed $attr
     */
    public function setAttrValue($key,$value)
    {
        if(!in_array($key,self::$ATTR_LIST)){
            throw new \Exception(sprintf('Not Support Form Attr [%s]. Support is "%s"',$key,implode('" or "',self::$ATTR_LIST)));
        }
        $this->attr[$key] = $value;
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

    /**
     * @param boolean $isArray
     */
    public function setIsArray($isArray)
    {
        $this->isArray = $isArray;
    }

    /**
     * @return boolean
     */
    public function getIsArray()
    {
        return $this->isArray;
    }

    /**
     * extend form type to output type
     *
     * @return mixed
     */
    private function getFormType()
    {
        $type = strtolower($this->type);
        if($type == FormElement::$TYPE_TIME){
            return "input";
        }
        return $type;
    }

    /**
     * @param $attr
     * @return array
     */
    private function getHTMLAttr(){
        $list = array();
        foreach($this->attr as $k => $v){
            if(in_array($k,array(
                FormElement::$ATTR_FORMAT,
                FormElement::$ATTR_CHOICES,
                FormElement::$ATTR_ATTR
            ))){
                continue;
            }
            $list[$k] = $v;
        }
        if(isset($this->attr[FormElement::$ATTR_ATTR])){
            $list = array_merge($this->attr[FormElement::$ATTR_ATTR],$list);
        }
        $list['name'] = $this->getFormName();
        $list['id'] = $this->getFormId();
        return $list;
    }

    /**
     * @param array $html_attr
     * @return null|htmlElement
     */
    public function getTag($html_attr=array())
    {
        $html = null;
        $attr = $this->getAttr();
        $html_attr = array_merge($this->getHTMLAttr(),$html_attr);
        $value = $this->getValue();
        $label = $this->getAttrValue(FormElement::$ATTR_LABEL,'');
        $type = $this->getFormType();
        switch($type){
            case FormElement::$TYPE_SELECT:
                $options = $this->getAttrValue(FormElement::$ATTR_CHOICES,array());

                $list = array();
                if(is_array($options)){
                    $list = $options;
                }else if(is_object($options) && ($options instanceof Model)){
                    $results = $options->findQuery()->getResults();
                    if($results){
                        foreach($results as $val){
                            $list[$val['id']] = $val['name'];
                        }
                    }
                }else if(is_callable($options)){
                    $list = $options();
                }
                $html = new htmlElement('select',$html_attr,null,false);
                foreach($list as $k => $v){
                    $opt = array();
                    if($value instanceof Model){
                        if($value->getId() == $k){
                            $opt['selected'] = 'selected';
                        }
                    }else if($value == $k){
                        $opt['selected'] = 'selected';
                    }
                    $opt['value'] = $k;
                    $html->addChilds(new htmlElement('option',$opt,$v,false));
                }
                break;
            case FormElement::$TYPE_CHECKBOX:
                $id = $html_attr['id'];

                $checkbox_attr = array_merge($html_attr,array('type' => $type,'value' => (isset($attr[FormElement::$ATTR_VALUE]) ? $attr[FormElement::$ATTR_VALUE] : '')));
                if(isset($attr[FormElement::$ATTR_VALUE]) && $attr[FormElement::$ATTR_VALUE] == $value){
                    $checkbox_attr['checked'] = 'checked';
                }
                $html = new htmlElement(null);
                $html->addChilds(
                    new htmlElement('input',$checkbox_attr)
                );
                $html->addChilds(
                    new htmlElement('label',array('for' => $id),htmlElement::escape($label),false));
                break;
            case FormElement::$TYPE_RADIO:
            case FormElement::$TYPE_CHECKLIST:
                $html = new htmlElement(null);
                if(isset($attr[FormElement::$ATTR_CHOICES])){
                    foreach($attr[FormElement::$ATTR_CHOICES] as $key => $v){
                        $id = $html_attr['id'].'-'.$key;
                        $name = $this->getFormName().'[]';
                        $ctype = 'radio';
                        if($type == FormElement::$TYPE_CHECKLIST){
                            $ctype = 'checkbox';
                        }
                        $input_attr = array_merge($html_attr,array('type' => $ctype,'id' => $id,'name' => $name,'value' => $key));

                        if(is_array($value)){
                            if(in_array($key,$value)){
                                $input_attr['checked'] = 'checked';
                            }
                        }else{
                            if($key == $value){
                                $input_attr['checked'] = 'checked';
                            }
                        }
                        $html->addChilds(new htmlElement('input',$input_attr));
                        $html->addChilds(
                            new htmlElement('label',array('for' => $id),htmlElement::escape($v),false));
                    }
                }else{
                    throw new \Exception('Form Type Radio OR Checklist Must Be "choices" attr.');
                }
                break;
            case FormElement::$TYPE_TEXTAREA:
                $html = new htmlElement('textarea',$html_attr,$value,false);
                break;
            case FormElement::$TYPE_PASSWORD:
                $html = new htmlElement('input',array_merge($html_attr,array('type' => $type)));
                break;
            case FormElement::$TYPE_TEXT:
            case FormElement::$TYPE_EMAIL:
            case FormElement::$TYPE_URL:
            default:
                $html = new htmlElement('input',array_merge($html_attr,array('type' => $type,'value' => $value)));
                break;
        }
        return $html;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return (string)$this->getTag();
    }

}

/**
 * Class FormView
 * @package PMP
 */
class FormView
{
    private $elem;
    private $_url;

    function __construct($url)
    {
        $this->_url = $url;
        $this->elem = array();
    }

    /**
     * @param $key
     * @param FormElement $elm
     */
    public function add($key,FormElement $elm)
    {
        $this->elem[$key] = $elm;
    }

    /**
     * @return array
     */
    public function getElement()
    {
        return $this->elem;
    }

    /**
     * @return mixed
     */
    public function getFormActionUrl()
    {
        return $this->_url;
    }

    /**
     * @param $name
     * @return null
     */
    function __get($name)
    {
        if(isset($this->elem[$name])){
            return $this->elem[$name];
        }
        return null;
    }

}

/**
 * Class Form
 * @package PMP
 */
class Form{
    private $elem;
    private $request;
    private $models;

    function __construct(){
        $this->elem = array();
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
            $value = $this->getValue($key);
            $elm = new FormElement($type,$prex);
            $elm->setAttr(array_merge($this->elem[$key]->getAttr(),$this->makeAttr($name,$type,$attr)));
            if($prex !== NULL){
                $elm->setPrex($prex);
            }
            $this->addElement($key,$elm);
            $this->setValue($key,$value);
        }else{
            $elm = new FormElement($type,$prex);
            $elm->setAttr($this->makeAttr($name,$type,$attr));
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
     * @return array
     */
    public function getElement()
    {
        return $this->elem;
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

        $prex = "";
        if($multi_form){
            $prex = $table_name."-";
        }
        $fields = $model->getColumns();
        foreach($fields as $key => $column){
            if(!$column->getFormenable()){
                continue;
            }
            $type = $this->convertColumnsToFormType($column);
            $elm = new FormElement($type,$prex);
            $elm->setIsArray(($column->getType() == ModelColumn::$TYPE_ARRAY) && ($column->getChoices() > 0));
            $elm->setAttr($this->makeAttr($key,$type,array()));
            $elm->setAttrValue(FormElement::$ATTR_FORMAT,$column->getFormat());
            $elm->setAttrValue(FormElement::$ATTR_CHOICES,$column->getChoices());
            $elm->setAttrValue(FormElement::$ATTR_LABEL,$column->getComment());
            if($column->getType() == ModelColumn::$TYPE_BOOLEAN){
                $elm->setAttrValue(FormElement::$ATTR_VALUE,1);
            }
            if($column->getLength() > 0){
                $elm->setAttrValue(FormElement::$ATTR_MAXLENGTH,$column->getLength());
            }
            if(($column->getType() == ModelColumn::$TYPE_DATE) || ($column->getType() == ModelColumn::$TYPE_DATETIME)){
                if($column->getNullable() == false){
                    $elm->setAttrValue(FormElement::$ATTR_REQUIRED,true);
                }
            }
            $this->addElement($key,$elm);

            $this->setValue($key,$model->get($key));
        }
        $this->models[$table_name] = $model;
        return $this;
    }

    /**
     * @param ModelColumn $column
     * @return string
     */
    private static function convertColumnsToFormType(ModelColumn $column)
    {
        $field = $column->getDBColumn();
        $ctype = FormElement::$TYPE_TEXT;
        if($field->getAi()){
            $ctype = FormElement::$TYPE_HIDDEN;
        }else{
            if($column->getType() == ModelColumn::$TYPE_ARRAY){
                if(count($column->getChoices()) > 0){
                    $ctype = FormElement::$TYPE_CHECKLIST;
                }else{
                    $ctype = FormElement::$TYPE_TEXTAREA;
                }
            }else if($field->getType() == ModelColumn::$TYPE_BOOLEAN){
                $ctype = FormElement::$TYPE_CHECKBOX;
            }else if($field->isInt()){
                if(count($column->getChoices()) > 0){
                    $ctype = FormElement::$TYPE_SELECT;
                }else{
                    $ctype = FormElement::$TYPE_TEXT;
                }
            }else if($field->isFloat()){
                $ctype = FormElement::$TYPE_TEXT;
            }else if($field->isDate()){
                $ctype = FormElement::$TYPE_TEXT;
            }else if($field->isText()){
                $ctype = FormElement::$TYPE_TEXTAREA;
            }else if($field->isString()){
                $ctype = FormElement::$TYPE_TEXT;
            }
        }
        return $ctype;
    }

    /**
     * @return FormView
     */
    public function getForm(){
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $results = new FormView($url);
        foreach($this->elem as $name => $value){
            $value->setName($name);
            $results->add($name,$value);
        }
        return $results;
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
     * @return array
     */
    /*public function getString(){
        $tags = array();
        $labels = array();
        $values = array();
        foreach($this->elem as $name => $value){
            $val = $value->getValue();
            $val = $val ? $val : "";
            $attr = $value->getAttr();
            $attr[FormElement::$ATTR_NAME] = $value->getPrex().$name;

            $id = $value->getPrex().$name;
            if(isset($attr[FormElement::$ATTR_ATTR]['id'])){
                $id = $attr[FormElement::$ATTR_ATTR]['id'];
            }
            $label = $attr[FormElement::$ATTR_NAME];
            if(isset($attr[FormElement::$ATTR_LABEL])){
                $label = $attr[FormElement::$ATTR_LABEL];
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
    }*/

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
    public function isValid()
    {
        if($this->request){
            $request = $this->request;
            $check_all = true;

            foreach($this->elem as $key => $val){
                if(!($val instanceof FormElement)){
                    throw new \Exception(sprintf('Form Element "%s" Not Type FormElement.',$key));
                }
                $error = new FormError();
                $label = $val->getAttrValue(FormElement::$ATTR_LABEL,$key);
                $form_key = $val->getPrex().$key;

                $value = null;
                if($val->getIsArray()){
                    $value = $request->get($form_key,array());
                    if(!is_array($value)){
                        $error->add('array',$this->getErrorMessage('array',$label));
                    }
                }else{
                    $value = $request->get($form_key,"");
                }
                if($val->getAttrValue(FormElement::$ATTR_REQUIRED,false) === true){
                    if(!(($request->is($form_key)) && ($value != ""))){
                        $error->add('required',$this->getErrorMessage('required',$label));
                    }
                }
                if($value != ""){
                    if(($error->count() <= 0) && $val->getAttrValue(FormElement::$ATTR_FORMAT)){
                        if(!preg_match("/^".$val->getAttrValue(FormElement::$ATTR_FORMAT)."$/",$value)){
                            $error->add('format',$this->getErrorMessage('format',$label));
                        }
                    }
                }
                if(($request->is($form_key)) && ($value != "")){
                    if($val->getType() == FormElement::$TYPE_EMAIL){
                        if(!$this->checkMail($value)){
                            $error->add('email',$this->getErrorMessage('email',$label));
                        }
                    }else if($val->getType() == FormElement::$TYPE_URL){
                        if(!$this->checkURL($value)){
                            $error->add('url',$this->getErrorMessage('url',$label));
                        }
                    }else if($val->getType() == FormElement::$TYPE_DATE){
                        if(!$this->checkDateString($value)){
                            $error->add('date',$this->getErrorMessage('date',$label));
                        }
                    }else if($val->getType() == FormElement::$TYPE_DATETIME){
                        if(!$this->checkDateTimeString($value)){
                            $error->add('datetime',$this->getErrorMessage('datetime',$label));
                        }
                    }else if($val->getType() == FormElement::$TYPE_TIME){
                        if(!$this->checkTimeString($value)){
                            $error->add('time',$this->getErrorMessage('time',$label));
                        }
                    }else if($val->getType() == FormElement::$TYPE_PASSWORD){
                        if(!preg_match("/^([0-9a-zA-Z]*)$/",$value)){
                            $error->add('password',$this->getErrorMessage('password',$label));
                        }
                    }
                    if(($error->count() <= 0) && $val->getAttrValue(FormElement::$ATTR_CHOICES)){
                        $choices = $val->getAttrValue(FormElement::$ATTR_CHOICES);
                        $c = true;
                        if(is_array($choices)){
                            if(is_array($value)){
                                foreach($value as $ck => $cv){
                                    if(!array_key_exists($cv,$choices)){
                                        $c = false;
                                        break;
                                    }
                                }
                            }else if(!isset($choices[$value])){
                                $c = false;
                            }
                        }else if(is_object($choices) && ($choices instanceof Model)){
                            if(!$choices->findQuery(intval($value))->getResults()){
                                $c = false;
                            }
                        }else if(is_callable($choices)){
                            $c = $choices();
                            if(!isset($c[$value])){
                                $c = false;
                            }
                        }
                        if(!$c){
                            $error->add('choices',$this->getErrorMessage('choices',$label));
                        }
                    }
                }
                if($error->count() <= 0){
                    if($this->models){
                        foreach($this->models as $k => $v){
                            if($v->isExists($key)){
                                $column = $v->getColumn($key);
                                if(!$column->getNullable()){
                                    if($column->getType() == ModelColumn::$TYPE_ARRAY){
                                        if(count($value) <= 0){
                                            $error->add('required',$this->getErrorMessage('required',$label));
                                        }
                                    }
                                }
                                if($column->getDBColumn()->isUnique()){
                                    if($res = $v->findQuery(array($key => $value))->getResults()){
                                        $idv = $request->get($val->getPrex().'id',null);
                                        foreach($res as $rk => $rv){
                                            if($idv && isset($rv['id']) && ($rv['id'] == $idv)){
                                                continue;
                                            }
                                            $error->add('unique',$this->getErrorMessage('unique',$label));
                                            break;
                                        }
                                    }
                                }
                                if($column->getConnection()){
                                    $taget_class = $column->getConnection()->getTargetName();
                                    $target = new $taget_class();
                                    if(!$target->find(array($column->getConnection()->getTargetColumn() => $value))){
                                        $error->add('required',$this->getErrorMessage('required',$label));
                                    }
                                }
                                $v->set($key,$value);
                            }
                        }
                    }
                }
                if($error->count() > 0){
                    $val->setError($error);
                    $check_all = false;
                }
                /*if($val->getIsArray()){
                    foreach($this->models as $k => $v){
                        if($v->isExists($key)){
                            $v->set($key,$valuelist);
                        }
                    }
                }*/
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
            case "unique":
                return __('%1$s is exists.',$key);
                break;
            default:
                throw new \Exception(sprintf('Not Found Error Message "%s"',$type));
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