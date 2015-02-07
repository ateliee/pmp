<?php
namespace PMP;

/**
 * Class htmlElement
 * @package PMP
 */
class htmlElement{
    protected $tag_name;
    protected $attr;
    protected $inner_html;
    protected $empty_tag;
    static private $is_html5 = true;
    static private $spacer = '"';

    /**
     * @param $enable
     */
    static function is_html5($enable){
        self::$is_html5 = $enable;
    }

    /**
     * @param $spacer
     */
    static function setSpacer($spacer){
        self::$spacer = $spacer;
    }

    /**
     * @param $tag_name
     * @param array $attr
     * @param null $inner_html
     */
    public function __construct($tag_name,$attr=array(),$inner_html=null,$empty_tag=true){
        $this->tag_name = $tag_name;
        $this->attr = $attr;
        $this->inner_html = $inner_html;
        $this->empty_tag = $empty_tag;
    }

    /**
     *
     */
    public function __toString(){
        $tag = '';
        if(!$this->tag_name){
            return $this->inner_html;
        }
        $tag .= '<'.$this->tag_name;
        if(count($this->attr) > 0){
            $attr_list = array();
            foreach($this->attr as $key => $val){
                if(is_bool($val) && $val === true){
                    $attr_list[] = $key;
                }else{
                    $attr_list[] = $key.'='.self::$spacer.preg_replace('/'.self::$spacer.'/','\\'.self::$spacer,$val).self::$spacer;
                }
            }
            $tag .= ' '.implode(' ',$attr_list);
        }
        if($this->empty_tag && ($this->inner_html == null)){
            $tag .= (self::$is_html5 ? '' : ' /').'>';
        }else{
            $tag .= '>';
            $tag .= $this->inner_html;
            $tag .= '</'.$this->tag_name.'>';
        }
        return $tag;
    }

    /**
     * @param $key
     * @param $val
     */
    public function addAttr($key,$val){
        $this->attr[$key] = $val;
    }

    /**
     * @param $key
     */
    public function removeAttr($key){
        unset($this->attr[$key]);
    }

    /**
     * @param $str
     * @return string
     */
    static function escape($str){
        return htmlspecialchars($str, ENT_QUOTES, mb_internal_encoding());
    }
}

/**
 * Class htmlEmptyElement
 * @package PMP
 */
class htmlEmptyElement extends htmlElement{

    function __construct($value){
        $this->tag_name = null;
        $this->inner_html = $value;
    }
}

/**
 * Class htmlElementList
 * @package PMP
 */
class htmlElementList{
    private $element;

    /**
     * @param $elm
     */
    function __construct(){
        $this->element = array();
    }

    /**
     * @param htmlElement $elm
     */
    public function addElement(htmlElement $elm){
        $this->element[] = $elm;
    }

    /**
     * @return string
     */
    public function __toString(){
        return implode('',$this->element);
    }
}
