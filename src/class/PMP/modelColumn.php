<?php
namespace PMP;

/**
 * Class ModelColumn
 * @package PMP
 */
class ModelColumn{
    protected $dbcolumn;
    protected $column;
    protected $options;

    function __construct($field){
        $this->column = array();
        foreach($field as $k => $v){
            $k = strtolower($k);
            if(in_array($k,array('form','choices','format'))){
                $this->options[$k] = $v;
            }else{
                $this->column[$k] = $v;
            }
        }
    }

    /**
     * @return DatabaseColumn
     */
    public function getDBColumn(){
        if(!$this->dbcolumn){
            $column = $this->column;
            if($column['type'] == 'url'){
                $column['type'] = 'varchar';
                $column['length'] = '250';
            }
            $this->dbcolumn = new DatabaseColumn($column);
        }
        return $this->dbcolumn;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function setColumn($key,$val){
        $this->column[$key] = $val;
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getColumn($key,$default=null){
        if(isset($this->column[$key])){
            return $this->column[$key];
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
