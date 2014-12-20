<?php
namespace PMP;

/**
 * Class RequestVars
 * @package PMP
 */
class RequestVars{
    private $vars;

    /**
     * @param $arr
     */
    function  __construct($arr=array()){
        $this->vars = array();
        $this->setVars($arr);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key,$value){
        $this->vars[$key] = $value;
        return $this;
    }
    /**
     * @param $arr
     * @return bool
     * @throws PMPException
     */
    public function setVars($arr){
        if(is_array($arr)){
            foreach($arr as $k => $v){
                $this->set($k,$v);
            }
            return true;
        }
        throw new PMPException("set Param is not Array()");
    }

    /**
     * @param $key
     * @return bool
     */
    public function is($key){
        return isset($this->vars[$key]);
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     * @throws PMPException
     */
    public function get($key,$default=null){
        if($this->is($key)){
            return $this->vars[$key];
        }
        return $default;
    }

    /**
     * @return mixed
     */
    public function getVars(){
        return $this->vars;
    }
}
/**
 * Class Request
 */
class Request{
    private static $request;
    private static $get;
    private static $post;
    private static $server;
    private static $files;
    private static $base_uri;

    const METHOD_POST = "POST";
    const METHOD_GET = "GET";
    const METHOD_HEAD = "HEAD";
    const METHOD_PUT = "PUT";

    function  __construct(){
        //if(isset($_REQUEST)){
            //self::$request = new RequestVars($_REQUEST);
            //unset($_REQUEST);
        //}
        self::$request = new RequestVars();
        if(isset($_SERVER)){
            self::$server = new RequestVars($_SERVER);
            //unset($_SERVER);
        }
        if(isset($_POST)){
            self::$post = new RequestVars($_POST);
            self::$request->setVars($_POST);
            //unset($_POST);
        }
        if(isset($_GET)){
            //self::$get = $_GET;
            $get = array();
            if(isset($_SERVER["REQUEST_URI"])){
                parse_str(self::getURLQuery($_SERVER["REQUEST_URI"]),$get);
            }
            self::$get = new RequestVars($get);
            self::$request->setVars($get);
            //unset($_GET);
        }
        if(isset($_FILES)){
            self::$files = new RequestVars($_FILES);
            //unset($_FILES);
        }
    }

    /**
     * @param $base_uri
     * @return $this
     */
    static function setBaseUri($base_uri){
        self::$base_uri = $base_uri;
        return $base_uri;
    }

    /**
     * get $_SERVER["REQUEST_METHOD"]
     * @return mixed
     */
    static function getMethod(){
        return self::$server->get("REQUEST_METHOD");
    }

    /**
     * @return mixed|null
     */
    static public function getUri(){
        $uri = self::$server->get("REQUEST_URI");
        $uri = preg_replace("/^".preg_quote(self::$base_uri,"/")."(.*)$/","$1",$uri);
        return parse_url($uri,PHP_URL_PATH);
    }

    /**
     * @return null
     */
    static public function getHost(){
        return self::$server->get("HTTP_HOST");
    }

    /**
     * @return null
     */
    static public function getScheme(){
        return (self::isSecure() ? "https://" : "http://");
        //return parse_url($url,PHP_URL_SCHEME);
    }

    /**
     * @return null
     */
    static public function getPort(){
        return self::$server->get("SERVER_PORT");
        //return parse_url($url,PHP_URL_PORT);
    }

    /**
     * @return string
     */
    static public function getHttpHost(){
        $scheme = self::getScheme();
        $user = self::$server->get("REMOTE_USER","");
        $user = $user ? $user."@" : "";
        $port = self::getPort();
        $host = self::getHost();
        $host = $host ? $host : self::$server->get("SERVER_NAME").((self::isSecure() && ($port == 443) || $port == 80) ? "" : ":".$port);
        return $scheme.$user.$host;
    }

    /**
     * @return string
     */
    static public function getUrl(){
        return self::getHttpHost().self::$server->get("REQUEST_URI","");
    }
    /*static public function getBasePath($url = false){
        $url = ($url) ? $url : self::$url;
        return basename(parse_url($url,PHP_URL_PATH));
    }

    static public function getFragment($url = false){
        $url = ($url) ? $url : self::$url;
        return parse_url($url,PHP_URL_FRAGMENT);
    }*/

    /**
     * @param bool $url
     * @return mixed
     */
    static public function getURLQuery($url = false){
        $url = ($url) ? $url : (self::$server->get("REQUEST_URI"));
        return parse_url($url,PHP_URL_QUERY);
    }


    /**
     * @return bool
     */
    static public function isAjax()
    {
        if(self::$server->is('HTTP_X_REQUESTED_WITH') && strtolower(self::$server->get('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest'){
            return true;
        }
        return false;
    }

    /**
     * is https request
     * @return bool
     */
    static public function isSecure(){
        if(strcasecmp(self::$server->get('HTTPS','off'),'on') === 0){
            return true;
        }
        return false;
    }

    /**
     * @return RequestVars
     */
    static public function getServer(){
        return self::$server;
    }

    /**
     * @return RequestVars
     */
    static public function getQuery(){
        return self::$get;
    }

    /**
     * @return RequestVars
     */
    static public function post(){
        return self::$post;
    }

    /**
     * @return RequestVars
     */
    static public function get(){
        return self::$get;
    }

    /**
     * @return RequestVars
     */
    static public function getRequest(){
        return self::$request;
    }

    /**
     * @return RequestVars
     */
    static public function getFiles(){
        return self::$files;
    }

    /**
     * @param $name
     * @return null
     */
    static public function getFileName($name){
        $file = self::$files->get($name);
        if($file){
            return $file["name"];
        }
        return NULL;
    }

    // MOVE STATUS
    const STATUS_MOVE_SUCCESS = 0;
    const STATUS_MOVE_NOTFOUND = 1;
    const STATUS_MOVE_ERROR_TYPE = 2;
    const STATUS_MOVE_SIZE = 4;
    const STATUS_MOVE_FORBIDDEN = 8;

    /**
     * @param $name
     * @param $output
     * @param array $options
     * @return mixed
     */
    static public function moveFile($name,$output,$options=array()){
        $default = array(
            "max_size" => 0,    // upload max file size
            "extension" => "/(gif|jpeg|jpg|png)/i",
        );
        $options = array_merge($default,$options);
        $file = self::$files->get($name);
        if(!$file){
            return self::STATUS_MOVE_NOTFOUND;
        }
        // extension check
        $filename = $file['name'];
        $extension = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
        $size = $file['size'];
        if($extension == "" || !preg_match($options["extension"],$extension)){
            return self::STATUS_MOVE_ERROR_TYPE;
        }
        // size check
        if($options["extension"] != 0 && $size > $options["extension"]){
            return self::STATUS_MOVE_ERROR_TYPE;
        }
        $tmp_file = $file['tmp_name'];
        // move file
        if(!move_uploaded_file($tmp_file, $output)){
            return self::STATUS_MOVE_FORBIDDEN;
        }

        return self::STATUS_MOVE_SUCCESS;
    }
}