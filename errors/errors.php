<?php

/**
 * @param $text
 * @return array
 */
function __gt($text){
    return array($text,__($text));
}
/**
 * Class PHPLangError
 */
class PHPLangError{
    static $domain = "PHPLangError";
    static $path;
    static $sub_list = array();
    static $lang_list = array();
    static $errorno_str = array();
    static $errorno_list = array();

    function  __construct(){
    }

    /**
     * @param string $locale
     */
    static function init($locale = ""){
        if(!self::$path){
            self::setLanguagesPath(dirname(__FILE__)."/languages");
        }
        // domain setting
        Localize::bindTextDomain(self::$domain, self::$path);
        // setting
        set_error_handler( array(get_class(),'error_handler'), error_reporting() );
        register_shutdown_function( array(get_class(),'shutdown_handler') );
    }

    /**
     * @param $path
     * @return mixed
     */
    static function setLanguagesPath($path){
        self::$path = $path;
        return $path;
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     */
    static function error_handler ( $errno, $errstr, $errfile, $errline, $errcontext ) {
        $default_domain = Localize::textDomain(NULL);
        Localize::textDomain(self::$domain);

        $format = "[%s] %s %s(%s)\n";
        $lang_sub_list = array(
            __gt("array"),
            __gt("string"),
            __gt("object"),
        );
        $lang_list = array(
            __gt('Missing argument %d for %s, called in'),
            __gt('Use of undefined constant %s - assumed \'%s\''),
            __gt('%s to %s conversion'),
            __gt('Illegal string offset \'%s\''),
            __gt('Call to undefined method'),
            __gt('Non-static method %s should not be called statically'),
            __gt('Call to protected method %s from context \'%s\''),
            __gt('Cannot access private property'),
            __gt('Undefined variable: %s'),
            __gt('Undefined index: %s'),
            __gt('Illegal offset type in isset or empty'),
            __gt('%s expects parameter %s to be %s, %s given'),
            __gt('Call to private method %s from context \'%s\''),
            __gt('Call to a member function %s on a non-object'),
            __gt('Invalid argument supplied for %s'),
            __gt('Only variables should be passed by reference'),
            __gt('Too few arguments'),
        );
        $errorno_str = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING  => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING  => 'COMPILE_WARNING',
            E_USER_ERROR  => 'USER_ERROR',
            E_USER_WARNING   => 'USER_WARNING',
            E_USER_NOTICE   => 'USER_NOTICE',
            E_STRICT  => 'STRICT',
            E_RECOVERABLE_ERROR   => 'RECOVERABLE_ERROR',
            E_DEPRECATED  => 'DEPRECATED',
            E_USER_DEPRECATED   => 'USER_DEPRECATED',
        );
        $errorno_list = array(
            E_ERROR => __('Fatal run-time errors'),
            E_WARNING => __('Run-time warnings (non-fatal errors)'),
            E_PARSE => __('Compile-time parse errors'),
            E_NOTICE => __('Run-time notices'),
            E_CORE_ERROR => __('Fatal errors that occur during PHP\'s initial startup'),
            E_CORE_WARNING  => __('Warnings (non-fatal errors)'),
            E_COMPILE_ERROR => __('Fatal compile-time errors'),
            E_COMPILE_WARNING  => __('Compile-time warnings (non-fatal errors)'),
            E_USER_ERROR  => __('User-generated error message'),
            E_USER_WARNING   => __('User-generated warning message'),
            E_USER_NOTICE   => __('User-generated notice message'),
            E_STRICT  => __('Enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code'),
            E_RECOVERABLE_ERROR   => __('Catchable fatal error'),
            E_DEPRECATED  => __('Run-time notices'),
            E_USER_DEPRECATED   => __('User-generated warning message'),
        );
        $errno_lg = "";
        if(isset($errorno_list[$errno])){
            $errno_lg = $errorno_list[$errno];
        }
        $errno = $errorno_str[$errno].":".$errno_lg;
        foreach($lang_list as $v){
            $pv0 = self::get_pregstr($v[0]);
            if(preg_match("/".$pv0."/",$errstr,$mt)){
                $pv1 = self::get_pregstr_replace($v[1]);
                $errstr = preg_replace("/".$pv0."/",$pv1,$errstr,1);
                foreach($lang_sub_list as $vv){
                    $pvv0 = self::get_pregstr($vv[0]);
                    $pvv1 = self::get_pregstr_replace($vv[1]);
                    $errstr = preg_replace("/([\b])".$pvv0."([\b])/i","$1".$pvv1."$2",$errstr);
                }
                break;
            }
        }

        $display = ini_get("display_errors");
        $display = (is_numeric($display) ? ($display ? true : false) : ($display == "On" ? true : false));
        if($display){
            echo sprintf($format,$errno,$errstr,$errfile,$errline,$errcontext);
        }
        Localize::textDomain($default_domain);
    }

    /**
     * @param $text
     * @return mixed
     */
    static function get_pregstr($text){
        $text = preg_replace_callback(
            "/(%[a-zA-Z])/",
            function($match){
                $str = $match[1];
                if($str == "%d"){
                    return "(\d+?)";
                }else if($str == "%s"){
                    return "(\S+?)";
                }
                return $str;
            },
            $text);
        return $text;
    }

    /**
     * @param $text
     * @return mixed
     */
    static function get_pregstr_replace($text){
        $n = 1;
        $offset = 0;
        while(preg_match("/(%([0-9]*?)(\\$?)([a-zA-Z]+))/",$text,$matchs,PREG_OFFSET_CAPTURE,$offset)){
            if($matchs[2][0] > 0){
                $t = "$".$matchs[2][0];
            }else{
                $t = "$".$n;
            }
            $text = substr_replace($text,$t,$matchs[0][1],strlen($matchs[0][0]));
            $offset += strlen($t);
            $n ++;
        }
        return $text;
    }

    /**
     *
     */
    static function shutdown_handler(){
        $isError = false;
        if ($error = error_get_last()){
            switch($error['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_CORE_WARNING:
                case E_COMPILE_ERROR:
                case E_COMPILE_WARNING:
                    $isError = true;
                    break;
            }
        }
        if ($isError){
            echo self::error_handler(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line'],
                null );
        }
    }
}
