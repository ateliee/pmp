<?php
namespace PMP;

/**
 * Class AuthBasic
 */
class AuthSession extends AuthManager{
    private $session_name = "auth";

    function __construct($name=""){
        $this->session_name = $name;
        $this->setType(AuthManager::AUTHTYPE_SESSION);
    }

    /**
     * @param Auth $user
     */
    public function setUser(Auth $user){
        Session::set($this->session_name,array("user" => $user->getUserID(),"password" => $user->getPassword()));
    }

    /**
     *
     */
    public function resetUser(){
        Session::destroy($this->session_name);
    }

    /**
     * @return Auth|null
     */
    public function getUser(){
        $auth = Session::get($this->session_name);
        if($auth){
            $a = new Auth();
            return $a->set($auth['user'],$auth['password']);
        }
        return null;
    }

    /**
     *
     */
    protected function auth(){
    }
}