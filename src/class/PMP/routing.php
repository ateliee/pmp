<?php
namespace PMP;

/**
 * Class RoutingRoule
 * @package PMP
 */
class RoutingRoule
{
    private $url;
    private $query;
    private $class;
    private $args;
    private $requirements;

    function __construct($url,$class='',$args=null,$requirements=array())
    {
        if(($args != null) && !is_array($args)){
            throw new \Exception('args must be array.');
        }
        if(($requirements != null) && !is_array($requirements)){
            throw new \Exception('requirements must be array.');
        }
        $this->url = $url;
        $this->query = null;
        $this->class = $class;
        $this->args = $args;
        $this->requirements = $requirements;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }


    /**
     * @return null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return null
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

}

/**
 * Class Routing
 * @package PMP
 */
class Routing
{
    static private $roules = array();
    private $prex;
    private $prex_url;

    function  __construct(){
        $this->prex = null;
        $this->prex_url = null;
    }

    /**
     * @param $prex
     * @param $prex_url
     */
    public function setPrex($prex,$prex_url)
    {
        $this->prex = $prex;
        $this->prex_url = $prex_url;
    }

    /**
     * @param $name
     * @param RoutingRoule $roule
     * @param string $class
     * @param null $args
     * @param array $requirements
     */
    public function add($name,RoutingRoule $roule)
    {
        if($this->prex && $this->prex_url){
            if($name != ''){
                $name = $this->prex.'_'.$name;
            }else{
                $name = $this->prex.$name;
            }
            $roule->setUrl($this->prex_url.$roule->getUrl());
        }
        self::$roules[$name] = $roule;
    }

    /**
     * @param $name
     * @return RoutingRoule|null
     */
    static function getRoule($name)
    {
        if(!isset(self::$roules[$name])){
            return null;
        }
        return self::$roules[$name];
    }

    /**
     * @return roules
     */
    /*static public function getRoules(){
        return self::$roules;
    }*/

    /**
     * @param roule name
     * @param format args
     * @return string
     */
    static function generateUrl(){
        $args = func_get_args();
        if(count($args) <= 0){
            throw new \Exception('must be args 1 paratameter.');
        }
        $roule = self::getRoule($args[0]);
        if(!$roule){
            throw new \Exception('not found roule '.$args[0]);
        }
        $format = $roule->getUrl();
        $replacement = array();
        if(isset($args[1])){
            if(is_array($args[1])){
                foreach($args[1] as $key => $val){
                    $replacement["{".$key."}"] = $val;
                }
            }else{
                throw new \Exception('must be args 2 paramater is array.');
            }
        }
        $query = '';
        if(isset($args[2])){
            if(is_array($args[2])){
                $query = http_build_query($args[2]);
            }else{
                throw new \Exception('must be args 3 paramater is array.');
            }
        }
        return strtr($format,$replacement).($query ? '?'.$query : '');
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        $url = call_user_func_array(array(__CLASS__ , 'generateUrl'),func_get_args());
        return Application::getBaseUrl($url);
    }

    /**
     * @param $key
     * @return null
     */
    static protected function getPreg($key){
        if(isset(self::$roules[$key])){
            $roule = self::getRoule($key);
            $replacement = array();
            foreach($roule->getRequirements() as $key => $val){
                $replacement["\{".$key."\}"] = "(".$val.")";
            }
            return strtr(preg_quote($roule->getUrl(),"/"),$replacement);
        }
        return null;
    }

    /**
     * url to class name
     */
    static public function getRouleClass($url,$query=array()){
        foreach(self::$roules as $key => $val){
            if(preg_match("/^".self::getPreg($key)."$/",$url,$matchs)){
                /*if(isset($val["rule"]["query"])){
                    foreach($val["rule"]["query"] as $k => $v){
                        if(!(isset($query[$k]) && preg_match("/^".($v)."$/",$query[$k],$mt))){
                            continue 2;
                        }
                    }
                }*/
                $q = null;
                if($val->getArgs()){
                    $q = array();
                    $args = $val->getArgs();
                    foreach($args as $key => $v){
                        $q[$key] = $v;
                    }
                    /*foreach($q as $k => $v){
                        if(preg_match("/^(.*)%([0-9]*)(.*)$/",$v,$mt)){
                            $vv = $mt[1];
                            if(isset($matchs[$mt[2]])){
                                $vv .= $matchs[$mt[2]];
                            }
                            $vv .= $mt[3];
                            $q[$k] = $vv;
                        }
                    }*/
                }
                $url = $val->getUrl();
                $replacement = array();
                if(preg_match_all("/\{(.+?)\}/",$url,$mt)){
                    foreach($mt[0] as $k => $v){
                        $arg_key = $k + 1;
                        $replacement[$v] = $matchs[$arg_key];
                    }
                    foreach($q as $k => $v){
                        $q[$k] = strtr($v,$replacement);
                    }
                }
                return array(
                    "name" => $key,
                    "class" => $val->getClass(),
                    "param" => $q,
                );
            }
        }
        return null;
    }
}