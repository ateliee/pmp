<?php
namespace PMP;

include_once(dirname(__FILE__) . "/auth.php");

/**
 * Class AuthBasic
 */
class AuthBasic extends AuthManager{
    public $realm;
    public $failed_text;

    function __construct(){
        $this->setType(AuthManager::AUTHTYPE_BASIC);
        $this->realm = "Restricted Area";
        $this->failed_text = __("failed auth.");
    }

    /**
     * @return Auth|null
     */
    protected function getUser(){
        if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
            return (new Auth())->set($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
        }
        return null;
    }

    /**
     *
     */
    protected function auth(){
        header('WWW-Authenticate: Basic realm="' . $this->realm . '"');
        header('HTTP/1.0 401 Unauthorized');
        header('Content-type: text/html; charset=' . mb_internal_encoding());
        die($this->failed_text);
    }
}