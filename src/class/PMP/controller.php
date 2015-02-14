<?php
namespace PMP;

include_once(dirname(__FILE__) . '/json.php');

/**
 * Class Controller
 * @package PMP
 */
class Controller{
    protected $path;
    protected $class;
    protected $method;
    protected $template;
    protected $default_templatefiles;
    protected $session;
    protected $database;
    protected $project;
    protected $modelManager;

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
        $this->default_templatefiles = array();
        $this->session = new Session();
        $this->modelManager = new ModelManager();
        //$this->db = Database::getCurrentDB();
    }

    /**
     * @param $filename
     */
    public function addDefaultTemplatefiles($filename)
    {
        $this->default_templatefiles[] = $filename;
    }

    /**
     * @return Database
     */
    /*
    function getDB(){
        return $this->db;
    }*/
    /**
     * @return ModelManager
     */
    protected function getModelManager()
    {
        return $this->modelManager;
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
            "HOST" => Application::getHostname(),
            "URL" => Application::getHostUrl(),
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

        Session::clearFlash();
    }

    /**
     * @param string $filename
     * @param array $param
     * @param array $headers
     * @return bool
     * @throws PMPException
     */
    public function rendar($filename,$param=array(),$headers=array()){
        try{
            $this->rendarParamSet();
            // set template
            $this->template->assign_vars($param);
            // default file
            foreach($this->default_templatefiles as $file){
                $template = new \PMP\Template();
                if($template->load($file)){
                    $template->setBlockData();
                    foreach($template->getBlocks() as $key => $val){
                        $this->template->setBlock($key,$val);
                    }
                }
            }
            // load template
            if($this->template->load($this->path.'/view/'.$filename)){
                $html = $this->template->get_display_template(true);
                return $this->rendarString($html,$headers);
            }else{
                throw new PMPException('File Not Found(`'.$this->path.'/view/'.$filename.'`).');
            }
        }catch(PMPException $e){
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
