<?php

/**
 * Class Session
 */
class Session
{
    private static $NameSpace = "";
    private static $flashName = "_FLASH";
    private static $flashData = array();
    /**
     *
     */
    static function start()
    {
        if(!self::isStart()){
            session_start();
            self::init();
        }
    }

    /**
     *
     */
    static private function init(){
        if (!isset($_SESSION['_SESSION_CHECK'])) {
            self::regenerate_id();
            $_SESSION['_SESSION_CHECK'] = array(
                'startTime' => time(),
                'changeTime' => time(),
            );
        } else {
            $_SESSION['_SESSION_CHECK']['changeTime'] = time();
        }
    }
    /**
     * @return bool
     */
    static function isStart(){
        if(function_exists("session_status")){
            if (session_status() != PHP_SESSION_NONE) {
                return true;
            }
        }else if(session_id() != '') {
            return true;
        }
        return false;
    }
    /**
     *
     */
    static function regenerate_id()
    {
        $tmp = $_SESSION;
        session_destroy();
        session_id(md5(uniqid(rand(), true)));
        session_start();
        $_SESSION = $tmp;
    }

    /**
     * @param string $namespace
     */
    static function select($namespace = "")
    {
        if ($namespace != "") {
            self::$NameSpace = $namespace;

            self::start();
            if (isset($_SESSION[$namespace]) == false) {
                $_SESSION[$namespace] = array();
            }
        }
    }

    /**
     * get flash message
     * @param $key
     * @param $val
     */
    static function setFlash($key,$val){
        self::start();

        /*$data = array();
        if(isset(self::getFlashData()[$key])){
            $data = self::getFlashData()[$key];
        }
        $data[] = $val;*/
        self::set(self::$flashName,array_merge(self::getFlashData(),array($key => $val)));
    }

    /**
     * @return array
     */
    static function getFlashData(){
        self::start();
        self::$flashData = self::get(self::$flashName,array());
        return self::$flashData;
    }

    /**
     * @param $name
     * @param bool $default
     * @return bool
     */
    static function getFlash($name,$default=false){
        self::start();
        $flash = self::getFlashData();
        return isset($flash[$name]) ? $flash[$name] : $default;
    }

    /**
     * clearf flash data
     */
    static function clearFlash(){
        self::start();
        self::set(self::$flashName,array());
    }

    /**
    * @param $name
    * @return bool
    */
    static function get($name,$default=false)
    {
        self::start();
        if (isset($_SESSION[self::$NameSpace][$name])) {
            return $_SESSION[self::$NameSpace][$name];
        }
        return $default;
    }

    /**
     * @param $name
     * @param $val
     */
    static function set($name, $val)
    {
        self::start();
        $_SESSION[self::$NameSpace][$name] = $val;
    }

    /**
     * @param $name
     */
    static function unSession($name)
    {
        self::start();
        unset($_SESSION[self::$NameSpace][$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    static function is($name)
    {
        self::start();
        return isset($_SESSION[self::$NameSpace][$name]);
    }

    /**
     * @param $name
     */
    static function destroy($name)
    {
        self::start();
        if (isset($name)) {
            if (isset($_SESSION[self::$NameSpace][$name])) {
                $_SESSION[self::$NameSpace][$name] = array();
            }
        } else {
            if (isset($_SESSION)) {
                $_SESSION = array();
            } else {
                if (function_exists('session_unset')) {
                    session_unset();
                }
            }

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        }
    }
}
