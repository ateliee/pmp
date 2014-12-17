<?php
/**
 * Class Routing
 */
class Routing{
    static private $roules = array();

    function  __construct(){
    }

    /**
     * @param $name
     * @param $rule
     * @param string $class
     * @param null $args
     * @param array $requirements
     */
    public function add($name,$rule,$class="",$args=null,$requirements=array()){
        self::$roules[$name] = array("rule" => $rule,"class" => $class,"args" => $args,"requirements" => $requirements);
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
        $format = self::$roules[$args[0]]["rule"]["url"];
        $replacement = array();
        if(isset($args[1])){
            if(is_array($args[1])){
                foreach($args[1] as $key => $val){
                    $replacement["{".$key."}"] = $val;
                }
            }else{
                if(preg_match_all("/\{(.+?)\}/",$format,$mt)){
                    foreach($mt[0] as $k => $v){
                        $arg_key = $k + 1;
                        $replacement[$v] = $args[$arg_key];
                    }
                }
            }
        }
        return strtr($format,$replacement);
    }

    /**
     * @param $key
     * @return null
     */
    static protected function getPreg($key){
        if(isset(self::$roules[$key])){
            $roule = self::$roules[$key];
            $replacement = array();
            foreach($roule["requirements"] as $key => $val){
                $replacement["\{".$key."\}"] = "(".$val.")";
            }
            return strtr(preg_quote($roule["rule"]["url"],"/"),$replacement);
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
                if($val["args"]){
                    $q = array();
                    foreach($val["args"] as $key => $v){
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
                $url = $val["rule"]["url"];
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
                    "class" => $val["class"],
                    "param" => $q,
                );
            }
        }
        return null;
    }
}