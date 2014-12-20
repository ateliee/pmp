<?php
namespace PMP;

if(!function_exists("_e")){
    function _e($text){
        echo Localize::getText($text,Localize::getEncoding());
    }
}
if(!function_exists("__")){
    function __(){
        $arg_num = func_num_args();
        if($arg_num == 1){
            return Localize::getText(func_get_arg(0),Localize::getEncoding());
        }else if($arg_num > 1){
            $args = array_merge(
                array(Localize::getText(func_get_arg(0),Localize::getEncoding())),
                array_slice(func_get_args(),1));
            return call_user_func_array("sprintf",$args);
        }
    }
}

/**
 * Class Localize
 * @package PMP
 */
class Localize{
    private static $language;
    private static $region;
    private static $encoding;
    private static $current_domain = "";
    private static $domains = array();

    /**
     * @param $locale_head
     * @return string
     */
    static function setLocale($language,$region,$encoding){
        if(self::$language != $language){
            foreach(self::$domains as $domain){
                self::resetDomain($domain);
            }
        }
        self::$language = $language;
        self::$region = $region;
        self::$encoding = $encoding;
        $locale = self::makeLocale($language,$region,$encoding);

        setlocale(LC_ALL, $locale);
        // set init encoding
        mb_language($language);
        mb_internal_encoding($encoding);
        mb_http_input($encoding);
        mb_http_output($encoding);

        return $locale;
    }

    /**
     * @return mixed
     */
    static function getLocale($category = LC_ALL){
        //return self::$locale;
        return setlocale($category, 0);
    }

    /**
     * @return mixed
     */
    static function getLanguage(){
        return self::$language;
    }

    /**
     * @return mixed
     */
    static function getRegion(){
        return self::$region;
    }

    /**
     * @return mixed
     */
    static function getEncoding(){
        return self::$encoding;
    }

    /**
     * @param $language
     * @param $region
     * @param $encoding
     * @return string
     */
    static private function makeLocale($language,$region,$encoding){
        $locale = $language;
        if($region != ""){
            $locale .= "_".$region;
        }
        if($encoding != ""){
            $locale .= ".".$encoding;
        }
        return $locale;
    }
    /**
     * @param int $category
     * @return array
     */
    static function getLocaleInfo($category = LC_ALL){
        $language = "";
        $region = "";
        $encoding = "";

        $locale = self::getLocale($category);
        if(preg_match("/^(.+?)(_(.+))?(\.(.+))?$/",$locale,$mt)){
            if(count($mt) >= 2){
                $language = $mt[1];
            }else if(count($mt) >= 4){
                $region = $mt[3];
            }else if(count($mt) >= 6){
                $encoding = $mt[5];
            }
        }
        return array(
            "language" => $language,
            "region" => $region,
            "encoding" => $encoding
        );
    }

    /**
     * @param $zone
     */
    static function setTimeZone($zone){
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set($zone);
        }
    }

    /**
     * @param $domain
     * @return string
     */
    static function textDomain($domain){
        if($domain !== NULL){
            self::$current_domain = $domain;
            if(!isset(self::$domains[$domain])){
                self::$domains[$domain] = array(
                    "text" => array(),
                    "path" => array(),
                    "load" => array(),
                    "all_load" => false
                );
            }
        }
        return self::$current_domain;
    }

    /**
     * @param $domain
     * @param $directory
     * @return mixed
     */
    static function bindTextDomain($domain,$directory){
        if(!isset(self::$domains[$domain])){
            self::$domains[$domain] = array(
                "text" => array(),
                "path" => array(),
                "load" => array(),
                "all_load" => false
            );
        }
        self::$domains[$domain]["path"][] = ($directory);
        self::$domains[$domain]["load"][] = false;
        self::$domains[$domain]["all_load"] = false;
        return self::$domains[$domain]["path"];
    }

    /**
     * @param $domain
     */
    static private function resetDomain($domain){
        if(isset(self::$domains[$domain])){
            foreach(self::$domains[$domain]["path"] as $k => $v){
                self::$domains[$domain]["load"][$k] = false;
            }
            self::$domains[$domain]["text"] = array();
            self::$domains[$domain]["all_load"] = false;
        }
    }
    /**
     * @param $domain
     */
    static private function loadDomain($domain){
        if(!self::$domains[$domain]["all_load"]){
            foreach(self::$domains[$domain]["path"] as $k => $v){
                if(!self::$domains[$domain]["load"][$k]){
                    $filename = $v."/".self::$current_domain."-".self::$language.".po";
                    if(file_exists($filename)){
                        if($fp = fopen($filename, "r")){
                            $tmp_key = 0;
                            $tmp_list = array();
                            while ($line = fgets($fp)) {
                                $key = "";
                                $text = $line = trim($line);
                                $check = false;
                                if(preg_match("/^(.+) \"(.*)\"$/",$line,$mt)){
                                    $key = $mt[1];
                                    $text = $mt[2];
                                    $check = true;
                                }else if(preg_match("/^\"(.*)\"$/",$line,$mt)){
                                    $text = $mt[1];
                                    $check = true;
                                }
                                if($check){
                                    if($key != ""){
                                        if(count($tmp_list) > 0){
                                            $tmp_key ++;
                                        }
                                        $tmp_list[$tmp_key] = array(
                                            "key" => $key,
                                            "text" => $text
                                        );
                                    }else{
                                        if(isset($tmp_list[$tmp_key])){
                                            if($tmp_list[$tmp_key]["text"] != ""){
                                                //$tmp_list[$tmp_key]["text"] .= PHP_EOL;
                                            }
                                            $tmp_list[$tmp_key]["text"] .= $text;
                                        }
                                    }
                                }
                            }
                            $key = "";
                            $list = array();
                            foreach($tmp_list as $value){
                                if($value["key"] == "msgid"){
                                    $key = $value["text"];
                                }else if($value["key"] == "msgstr"){
                                    if($key != ""){
                                        $list[$key] = $value["text"];
                                    }
                                }
                            }
                            fclose($fp);
                            self::$domains[$domain]["text"] = array_merge(self::$domains[$domain]["text"],$list);
                        }
                    }
                    self::$domains[$domain]["load"][$k] = true;
                }
            }
            self::$domains[$domain]["all_load"] = true;
        }
    }

    /**
     * @param $text
     * @param string $encoding
     * @return mixed
     */
    static function getText($text,$encoding=""){
        return self::dgetText(self::$current_domain,$text,$encoding);
    }

    /**
     * @param $domain
     * @param $text
     * @param string $encoding
     * @return string
     */
    static function dgetText($domain,$text,$encoding=""){
        if(!$encoding){
            $encoding = self::$encoding;
        }
        if(isset(self::$domains[$domain])){
            self::loadDomain($domain);
            foreach(self::$domains[$domain]["text"] as $key => $val){
                if($key == $text){
                    $text = $val;
                    if(!$encoding){
                        $text = mb_convert_encoding(($text),$encoding,'AUTO');
                    }
                    break;
                }
            }
        }
        return $text;
    }
}