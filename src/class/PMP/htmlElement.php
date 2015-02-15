<?php
namespace PMP;

/**
 * Class htmlElement
 * @package PMP
 */
class htmlElement{
    protected $tag_name;
    protected $attr;
    protected $childs;
    protected $empty_tag;
    protected $inner_html;
    static private $is_html5 = true;
    static private $spacer = '"';

    /**
     * @param $tag_name
     * @param array $attr
     * @param null $inner_html
     */
    public function __construct($tag_name,$attr=array(),$inner_html=null,$empty_tag=true){
        $this->tag_name = $tag_name;
        $this->attr = $attr;
        $this->childs = array();
        $this->empty_tag = $empty_tag;
        $this->inner_html = $inner_html;
    }

    /**
     * @return mixed
     */
    public function getTagName()
    {
        return $this->tag_name;
    }

    /**
     * @return array
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @return array
     */
    public function getChilds()
    {
        return $this->childs;
    }

    /**
     * @param htmlElement $elm
     */
    public function addChilds(htmlElement $elm)
    {
        $this->childs[] = $elm;
    }

    /**
     * @param null $inner_html
     */
    public function setInnerHtml($inner_html)
    {
        $this->inner_html = $inner_html;
    }

    /**
     * @return null
     */
    public function getInnerHtml()
    {
        return $this->inner_html;
    }

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
     *
     */
    public function __toString(){
        $inner_tag = '';
        if(count($this->childs) > 0){
            foreach($this->childs as $v){
                $inner_tag .= $v;
            }
        }
        if($this->inner_html){
            $inner_tag .= $this->inner_html;
        }

        if(!$this->tag_name){
            return $inner_tag;
        }
        $tag = '<'.$this->tag_name;
        if(count($this->attr) > 0){
            $attr_list = array();
            foreach($this->attr as $key => $val){
                //if(is_bool($val) && $val === true){
                    //$attr_list[] = $key;
                //}else{
                    $attr_list[] = $key.'='.self::$spacer.preg_replace('/'.self::$spacer.'/','\\'.self::$spacer,$val).self::$spacer;
                //}
            }
            $tag .= ' '.implode(' ',$attr_list);
        }
        if($this->empty_tag && (count($this->childs) <= 0)){
            $tag .= (self::$is_html5 ? '' : ' /').'>';
        }else{
            $tag .= '>';
            $tag .= $inner_tag;
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
