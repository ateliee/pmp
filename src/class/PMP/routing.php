<?php
namespace PMP;

/**
 * Class RoutingRoule
 * @package PMP
 */
class RoutingRoule
{
    private $name;
    private $url;
    private $class;
    private $defaults;
    private $requirements;

    private $url_pattern;
    private $url_params;
    private $url_params_num;

    function __construct($url,$class='',$defaults=array(),$requirements=array())
    {
        if(($defaults != null) && !is_array($defaults)){
            throw new \Exception('Default Must Be Array.');
        }
        if(($requirements != null) && !is_array($requirements)){
            throw new \Exception('Requirements Must Be Array.');
        }
        $this->url = $url;
        $this->class = $class;
        $this->defaults = $defaults;
        $this->requirements = $requirements;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->url_pattern = null;
        $this->url_params = null;
        $this->url_params_num = null;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @throws PMPException
     */
    private function createParamsVars()
    {
        if(!$this->url_pattern){
            $preg_str = preg_quote($this->url,'/');

            $this->url_params = array();
            $this->url_params_num = array();

            if(preg_match_all('/{(.+)}/',$this->url,$matchs)){
                $replacement_params = array();
                $replacement_nums = array();
                foreach($matchs[1] as $key){
                    $replacement_params[$key] = '(.+)';
                    $replacement_nums[] = $key;
                }
                foreach($this->requirements as $key => $val){
                    if(!array_key_exists($key,$replacement_params)){
                        throw new PMPException(sprintf('Routing "%s" is Requirements param "%s" must be url.',$this->name,$key));
                    }
                    $replacement_params[$key] = '('.$val.')';
                }

                foreach($replacement_params as $key => $val){
                    $preg_str = str_replace(preg_quote('{'.$key.'}','/'),$val,$preg_str);
                }
                $this->url_params = $replacement_params;
                $this->url_params_num = $replacement_nums;
            }
            $this->url_pattern = $preg_str;
        }
    }

    /**
     * @param $url
     * @return array
     * @throws PMPException
     */
    public function checkUrl($url){
        $this->createParamsVars();
        if(preg_match('/'.$this->url_pattern.'/',$url,$matchs)){
            $params = array();
            foreach($this->defaults as $key => $val){
                $params[$key] = $val;
            }
            foreach($this->url_params_num as $num => $key){
                $params[$key] = $matchs[$num + 1];
            }
            return $params;
        }
        return array();
    }

    /**
     * @param array $params
     * @return string
     * @throws PMPException
     */
    public function generateUrl($params=array())
    {
        $this->createParamsVars();

        $p = array();
        $query = array();
        foreach($params as $key => $val){
            if(in_array($key,$this->url_params_num)){
                $p['{'.$key.'}'] = $val;
            }else{
                $query[$key] = $val;
            }
        }
        if(count($p) != count($this->url_params_num)){
            $less = array();
            foreach($this->url_params_num as $key){
                if(!isset($p['{'.$key.'}'])){
                    $less[] = $key;
                }
            }
            throw new PMPException(sprintf('generateUrl(%s) Must Be "%s" Paramater.',$this->name,implode(' and ',$less)));
        }
        if(!($url = strstr($this->url,$p))){
            $url = $this->url;
        }
        if(count($query) > 0){
            $url .= '?'.http_build_query($query);
        }
        return $url;
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
            $roule->setName($name);
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
     * @return string
     * @throws PMPException
     */
    static function generateUrl(){
        $args = func_get_args();
        if(count($args) <= 0){
            throw new PMPException('Must Be Args 1 Paratameter.');
        }
        $roule = self::getRoule($args[0]);
        if(!$roule){
            throw new PMPException('Not Found Roule '.$args[0]);
        }
        $params = array();
        if(isset($args[1])){
            if(is_array($args[1])){
                $params = $args[1];
            }else{
                throw new PMPException('Must Be Args 2 Paramater is Array.');
            }
        }
        return $roule->generateUrl($params);
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
     * url to class name
     */
    static public function getRouleClass($url){
        foreach(self::$roules as $key => $val){
            if($params = $val->checkUrl($url)){
                return array(
                    "name" => $key,
                    "class" => $val->getClass(),
                    "param" => $params,
                );
            }
        }
        return null;
    }
}