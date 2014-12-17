<?php

/**
 * Class defaultController
 */
class defaultController extends Controller
{
    public function index(){

    }
    public function error_404()
    {
        Server::http_status_code(404);
        return $this->rendar("error.tpl",array(
            "title" => "404 Error",
            "h1" => "404 Error",
            "description" => "Page not found"
        ));
    }
}