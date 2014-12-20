<?php
include_once(dirname(__FILE__) . '/json.php');
/**
 * Class Controller
 */
class Controller{
    protected $path;
    protected $class;
    protected $method;
    protected $session;
    protected $database;
    protected $project;

    /**
     * @param $path
     * @param $class
     * @param $method
     * @param $project
     */
    function  __construct($path,$class,$method,$project){
        $this->path = $path;
        $this->class = $class;
        $this->method = $method;
        $this->project = $project;

        $this->template = new Template();
        $this->session = new Session();
        $this->db = Database::getCurrentDB();
    }

    /**
     * @return Database
     */
    function getDB(){
        return $this->db;
    }
    /**
     *
     */
    protected function rendarParamSet(){
        // set template
        $this->template->assign_vars(array(
            // default set param
            "PATH" => Application::getBaseUrl(),
            "PPATH" => Application::getBaseUrl("/".$this->project),
            "ROOT" => Application::getWebUrl(),
            "PROOT" => Application::getWebUrl("/".$this->project),
            "PROJECT_NAME" => $this->project,
            "CLASS_NAME" => get_class($this),
            "SELF" => Application::getBaseUrl().Request::getUri(),
            // REQUEST
            "SERVER" => Request::getServer()->getVars(),
            "REQUEST" => Request::getRequest()->getVars(),
            "POST" => Request::post()->getVars(),
            "GET" => Request::getQuery()->getVars(),
            // session
            "FLASH" => Session::getFlashData(),
        ));
        // functions
        $this->template->filter("path",function($name,$val){
            # TODO : Routing To Path
            return $val;
        });

        Session::clearFlash();
    }

    /**
     * @param string $filename
     * @param array $param
     * @param array $headers
     * @return bool
     * @throws PMPException
     */
    public function rendar($filename="",$param=array(),$headers=array()){
        if(!$filename){
            $filename = $this->class.".html";
        }
        try{
            $this->rendarParamSet();
            // set template
            $this->template->assign_vars($param);
            // load template
            if($this->template->load($this->path.'/view/'.$filename)){
                $html = $this->template->get_display_template(true);
                return $this->rendarString($html,$headers);
            }else{
                throw new PMPException('File Not Found(`'.$this->path.'/view/'.$filename.'`).');
            }
        }catch(PHPException $e){
            $e->displayError();
            return false;
        }
        return true;
    }

    /**
     * @param $str
     * @param array $headers
     * @return bool
     */
    public function rendarString($str,$headers=array()){
        $headers = array_merge(array(
            "Content-Type" => "text/html"
        ),$headers);

        if(Application::DebugMode()){
            $debug = new Debug();
            $debug->appendRendarDebug($str);
        }
        $response = new Response($str);
        $response->setHeaders($headers);
        $response->output();
        return true;
    }

    /**
     * @param string $filename
     * @param array $param
     * @return bool
     */
    public function jsonRendar($filename="",$param=array()){
        return $this->rendar($filename,$param,array("Content-Type"=>"application/json"));
    }

    /**
     * @param $json
     * @return bool
     */
    public function jsonRendarObject($json,$option=0){
        if(!is_string($json)){
            $j = new jsonObj($option);
            $str = $j->encode($json);
        }else{
            $str = $json;
        }
        return $this->rendarString($str,array("Content-Type"=>"application/json"));
    }

    /**
     * @param string $filename
     * @param array $param
     * @return bool
     */
    public function xmlRendar($filename="",$param=array()){
        return $this->rendar($filename,$param,array("Content-Type"=>"text/xml"));
    }

    /**
     * @param $str
     * @return bool
     */
    public function xmlRendarString($str){
        return $this->rendarString($str,array("Content-Type"=>"text/xml"));
    }
}
