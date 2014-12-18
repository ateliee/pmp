<?php
/**
 * Class Application
 */
class Application{
    static private $root_dir;
    static private $base_url;
    static private $web_url;
    static private $source_dir;
    static private $auth = array();
    static private $debug_mode = false;
    /**
     *
     */
    function __construct(){
    }

    /**
     *
     */
    function __destruct(){
        //Session::clearFlash();
    }

    /**
     * @param null $mode
     * @return null
     */
    static public function DebugMode($mode=null){
        if($mode !== null){
            self::$debug_mode = $mode;
        }
        return self::$debug_mode;
    }

    /**
     * @param $path
     * @return mixed
     */
    static function setRootDir($path){
        return self::$root_dir = $path;
    }

    /**
     * @param string $path
     * @return string
     */
    static function getRootDir($path=""){
        return self::$root_dir.$path;
    }

    /**
     * @param $path
     * @return mixed
     */
    static function setBaseUrl($path){
        return self::$base_url = $path;
    }

    /**
     * @param $path
     * @return mixed
     */
    static function setSourceDir($path){
        return self::$source_dir = $path;
    }

    /**
     * @param string $path
     * @return string
     */
    static function getBaseUrl($path=""){
        return self::$base_url.$path;
    }

    /**
     * @param $path
     * @return mixed
     */
    static function setWebUrl($path){
        return self::$web_url = $path;
    }

    /**
     * @param string $path
     * @return string
     */
    static function getWebUrl($path=""){
        return self::$web_url.$path;
    }

    /**
     * @param $path
     * @return mixed
     */
    static public function toPath($path){
        return preg_replace("/^".preg_quote(self::$base_url,"/")."(.+)$/",self::$root_dir."$1",$path);
    }

    /**
     * @param $path
     * @return mixed
     */
    static public function toUri($path){
        return preg_replace("/^".preg_quote(self::$root_dir,"/")."(.+)$/",self::$base_url."$1",$path);
    }

    /**
     * @param $key
     * @param AuthManager $auth
     */
    static public function addAuthArea($key,AuthManager $auth){
        self::$auth[$key] = $auth;
    }

    /**
     * @param $key
     * @return null
     */
    static public function getAuthUser($key){
        $auth = self::getAuthArea($key);
        if($auth){
            return $auth->getFindUser();
        }
        return null;
    }

    /**
     * @param $key
     * @return AuthManager|null
     */
    static public function getAuthArea($key){
        if(isset(self::$auth[$key])){
            return self::$auth[$key];
        }
        return null;
    }

    /**
     * @return string
     */
    static public function getRoutingUrl(){
        return self::getBaseUrl(call_user_func_array(array("Routing","generateUrl"),func_get_args()));
    }

    /**
     * request to load controller
     */
    static function action(){
        // benchmark
        $benchmark = new Benchmark();
        $benchmark->start();

        $request = new Request();
        $request->setBaseUri(Application::getBaseUrl());

        // auth checkc
        if(self::$auth){
            foreach(self::$auth as $auth){
                $auth->check($request->getUri());
            }
        }
        $routing = new Routing();
        if(!$data = $routing::getRouleClass($request->getUri(),$request->getQuery())){
            if($data = $routing::getRouleClass($request->getUri()."/",$request->getQuery())){
                Server::redirect(Application::getBaseUrl().$request->getUri()."/");
            }
        }
        if(empty($data)){
            $data = array("class" => "core:default:error_404");
        }else if($data["class"] == ""){
            $data = array("class" => "core:default:index");
        }
        // pearse class method
        if(preg_match("/^([0-9a-zA-Z\-_]+):([0-9a-zA-Z\-_]+):?([0-9a-zA-Z\-_]*)$/",$data["class"],$matchs)){
            $project = $matchs[1];
            $class = $matchs[2];
            $method = $matchs[3];
            $method = (!empty($method)) ? $method : "index";
        }else{
            throw new PMPException('Error Class Method or Class Name(`'.$data["class"].'` is not routing find).');
        }
        $benchmark->setMark("routing");
        try{
            $path = self::$source_dir."/".$project;
            $filename = $path."/conf";
            dir_include_all($filename);
            $filename = $path."/class";
            dir_include_all($filename);

            $filename = $path."/controller/".$class.".php";
            if(file_exists($filename)){
                include_once($filename);
            }else{
                $path = dirname(__FILE__)."/../component";
                $filename = $path."/controller/".$class.".php";
                if(file_exists($filename)){
                    include_once($filename);
                }
            }
            $classname = $class."Controller";
            $controller = new $classname($path,$class,$method,$project);

            $benchmark->setMark("included");
            if(isset($data["param"])){
                call_user_func_array(array($controller,$method), $data["param"]);
            }else{
                $controller->$method();
            }
            $benchmark->setMark("action");
            $benchmark->stop();
            //$benchmark->display(false);

        }catch (Exception $e){
            throw new PMPException($e);
        }
    }

}

{
    $rootdir = dirname(__FILE__).'/../../../../../';
    $hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    if(isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'],array(80,443))){
        $hostname .= ':'.$_SERVER['SERVER_PORT'];
    }
    $documentroot = preg_replace('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',realpath($rootdir));

    Application::setRootDir($rootdir);
    Application::setBaseUrl($documentroot);
    Application::setWebUrl($documentroot.'/web');

}