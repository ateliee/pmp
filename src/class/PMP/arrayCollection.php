<?php
namespace PMP;

/**
 * Class ArrayCollection
 * @package PMP
 */
class ArrayCollection
{
    static $TYPE_ALL = 'all';
    static $TYPE_ARRAY = 'array';
    static $TYPE_INT = 'int';
    static $TYPE_NUMBER = 'number';
    static $TYPE_OBJECT = 'object';
    static $TYPE_STRING = 'string';

    private $variables;
    private $type;
    private $object_type;

    function __construct($values=array(),$type='all',$object_type=null)
    {
        $this->variables = $values;
        $this->type = $type;
        $this->object_type = $object_type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->variables;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->variables[$key];
    }

    /**
     * @param $value
     * @return $this
     * @throws \Exception
     */
    public function add($value)
    {
        if($this->check($value)){
            $this->variables[] = $value;
        }else{
            throw new \Exception(sprintf('can not add value.must be %s.',$this->type));
        }
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function remove($key)
    {
        if(array_key_exists($this->variables,$key)){
            unset($this->variables[$key]);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->variables);
    }

    /**
     * @param $value
     * @return bool
     */
    private function check($value)
    {
        if($this->type != self::$TYPE_ALL){
            if(($this->type == self::$TYPE_NUMBER) && (!is_numeric($value))){
                return false;
            }else if((($this->type == self::$TYPE_INT) || ($this->type == self::$TYPE_NUMBER)) && (!is_integer($value))){
                return false;
            }else if(($this->type == self::$TYPE_NUMBER) && (!is_double($value))){
                return false;
            }else if(($this->type == self::$TYPE_ARRAY) && (!is_array($value))){
                return false;
            }else if(($this->type == self::$TYPE_OBJECT)){
                if((!is_object($value))){
                    return false;
                }else if(($this->object_type) && !($value instanceof $this->object_type)){
                    return false;
                }
            }else if(($this->type == self::$TYPE_STRING) && (!is_string($value))){
                return false;
            }
        }
        return true;
    }
}