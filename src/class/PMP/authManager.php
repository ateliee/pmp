<?php
namespace PMP;

/**
 * Class AuthManager
 * @package PMP
 */
class AuthManager{
    const AUTHTYPE_BASIC = 1;
    const AUTHTYPE_SESSION = 2;
    const AUTHTYPE_COOKIE = 3;

    private $type;
    private $roules;
    private $security;
    private $failed_url;
    private $find_user;

    function __construct(){
        $this->setType(self::AUTHTYPE_BASIC);
        $this->find_user = null;
    }

    /**
     * @return null
     */
    public function getFindUser(){
        return $this->find_user;
    }

    /**
     * @param $type
     * @return $this
     */
    protected function setType($type){
        $this->type = $type;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function addRouting($rule){
        $this->roules[] = array("rule" => $rule);
        return $this;
    }

    /**
     * @param $user
     * @return $this
     */
    public function addSecurity($user){
        $this->security[] = $user;
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setFailedUrl($url){
        $this->failed_url = $url;
        return $this;
    }

    /**
     * @param AuthInterface $user
     * @return bool
     */
    public function find(AuthInterface $user){
        foreach($this->security as $security){
            if($u = $security->equals($user)){
                return $u;
            }
        }
        return false;
    }

    /**
     * @param $url
     * @return bool
     */
    public function check($url){
        if($this->checkRouting($url)){
            if($this->checkAuth()){
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function checkRouting($url){
        foreach($this->roules as $key => $val){
            if(preg_match("/^".$val["rule"]."/",$url,$matchs)){
                return true;
            }
        }
        return false;
    }


    /**
     * @return bool
     */
    protected function checkAuth(){
        $check = false;
        if ($user = $this->getUser()) {
            if($u = $this->find($user)){
                $this->find_user = $u;
                return $u;
            }
        }
        if(!$check){
            $this->failedAuth();
        }
        $this->auth();
        return false;
    }

    /**
     *
     */
    public function failedAuth(){
        if($this->failed_url){
            Server::redirect($this->failed_url);
        }
    }

    /**
     * @return Auth|null
     */
    protected function getUser(){
        return null;
    }

    /**
     *
     */
    protected function auth(){
    }
}
