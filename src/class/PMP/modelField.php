<?php
namespace PMP;

/**
 * Class ModelField
 * @package PMP
 */
class ModelField{
    protected $dbfield;
    protected $field;
    protected $options;

    function __construct($field){
        $this->field = array();
        foreach($field as $k => $v){
            $k = strtolower($k);
            if(in_array($k,array('form','choices','format'))){
                $this->options[$k] = $v;
            }else{
                $this->field[$k] = $v;
            }
        }
    }

    /**
     * @return DatabaseField
     */
    public function getDBField(){
        if(!$this->dbfield){
            $this->dbfield = new DatabaseField($this->field);
        }
        return $this->dbfield;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function setField($key,$val){
        $this->field[$key] = $val;
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getField($key,$default=null){
        if(isset($this->field[$key])){
            return $this->field[$key];
        }
        return $default;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getOption($key,$default=null){
        if(isset($this->options[$key])){
            return $this->options[$key];
        }
        return $default;
    }
}
