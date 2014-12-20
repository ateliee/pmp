<?php
namespace PMP;

/**
 * Class Server
 * @package PMP
 */
class Server{
    static private $default_options = array();

    /**
     * @param $key
     * @param $value
     */
    static public function tempSet($key,$value){
        $get = ini_get($key);
        self::$default_options[$key] = $get;
        ini_set($key,$value);
    }

    /**
     * @param $key
     */
    static public function reset($key){
        if(isset(self::$default_options[$key])){
            ini_set($key,self::$default_options[$key]);
        }
    }

    /**
     * @param $status
     */
    static function header($status){
        header($status);
    }

    /**
     * @param $code
     * @return bool
     */
    static function http_status_code($code){
        switch($code){
            case 200:
                self::header("HTTP/1.1 200 OK");
                break;
            case  301:
                self::header("HTTP/1.1 301 Moved Permanently");
                break;
            case  303:
                self::header("HTTP/1.1 303 See Other");
                break;
            case  401:
                self::header('HTTP/1.0 401 Unauthorized');
                break;
            case  403:
                self::header("HTTP/1.1 403 Forbidden");
                break;
            case  404:
                self::header("HTTP/1.1 404 Not Found");
                break;
            case  500:
                self::header('HTTP/1.0 500 Internal Server Error');
                break;
            case  503:
                self::header('HTTP/1.0 503 Service Unavailable');
                break;
            default:
                return false;
                break;
        }
        return true;
    }

    /**
     * @param $url redirect url
     * @param $status status code
     */
    static function redirect($url,$status=false){
        self::http_status_code($status);
        self::header( "Location: ".$url );
        exit;
    }
}