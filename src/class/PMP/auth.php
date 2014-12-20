<?php
namespace PMP;

/**
 * Class Auth
 * @package PMP
 */
class Auth implements AuthInterface{
    private $userid;
    private $password;
    private $salt;

    function __construct(){
        $this->userid = "";
        $this->password = "";
        $this->salt = "";
    }

    /**
     * @param $id
     * @param $password
     * @param string $salt
     * @return $this
     */
    public function set($id,$password,$salt=""){
        $this->userid = $id;
        $this->salt = $salt;
        $this->password = $this->makePassword($password);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserID(){
        return $this->userid;
    }

    /**
     * @return string
     */
    public function getPassword(){
        return $this->password;
    }

    /**
     * @param $password
     * @return string
     */
    public function makePassword($password){
        if($this->salt){
            $password = crypt($password,$this->salt);
        }
        return $password;
    }

    /**
     * @return mixed
     */
    public function getSalt(){
        return $this->salt;
    }

    /**
     * @param $password
     * @return bool
     */
    protected function equalsPassword($password){
        return ($this->makePassword($password) == $this->password);
    }

    /**
     * @param AuthInterface $user
     */
    public function equals(AuthInterface $user){
        return ($this->getUserID() == $user->getUserID() && $this->equalsPassword($user->getPassword()));
    }
}
