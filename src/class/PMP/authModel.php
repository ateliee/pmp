<?php
namespace PMP;

/**
 * Class Auth Model
 */
class AuthModel extends Model implements AuthInterface{
    protected $userid;
    protected $password;
    protected $salt;

    /**
     * @return array
     */
    public function useridColumn(){
        return new ModelColumn(array("type" => "varchar","length" => 250,"null" => false,"unique"=> true,"comment" => __("userid")));
    }
    /**
     * @return array
     */
    public function passwordColumn(){
        return new ModelColumn(array("type" => "varchar","length" => 30,"null" => false,"comment" => __("password")));
    }
    /**
     * @return array
     */
    public function saltColumn(){
        return new ModelColumn(array("type" => "varchar","length" => 32,"null" => false,"comment" => __("salt string"),"form" => false));
    }

    function __construct(){
        parent::__construct();
        $this->login_id = "";
    }

    /**
     * @return mixed
     */
    public function getUserID(){
        return $this->userid;
    }

    /**
     * @return mixed
     */
    public function getPassword(){
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getSalt(){
        return $this->salt;
    }

    /**
     * @param $val
     * @return string
     */
    public function setPassword($val){
        $this->set("salt",md5(uniqid(rand(),1)));
        return ($this->password = $this->makePassword($val));
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
        $results = $this->findQuery(array("userid" => $user->getUserID()))->getResults();
        $class_name = get_class($this);
        foreach($results as $result){
            $class = (new $class_name);
            $u = $class->setArray($result,false);
            if($u->getUserID() == $user->getUserID() && $u->equalsPassword($user->getPassword())){
                return $u;
            }
        }
        return false;
    }
}
