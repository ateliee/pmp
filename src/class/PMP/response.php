<?php
namespace PMP;

/**
 * Class Response
 * @package PMP
 */
class Response {
    private $contents;
    private $headers;

    /**
     * @param $arr
     */
    function  __construct($contents){
        $this->contents = $contents;
        $this->setHeaders(array());
    }

    /**
     * @param $key
     * @param $val
     */
    public function setHeader($key,$val=null){
        $this->headers[$key] = $val;
    }

    /**
     * @param $arr
     */
    public function setHeaders($arr){
        $this->headers = $arr;
    }

    /**
     * @param $expires
     */
    public function setCacheHeader($expires){
        $this->setHeader('Last-Modified',gmdate('D, d M Y H:i:s T', time()));
        $this->setHeader('Expires',gmdate('D, d M Y H:i:s T', time() + $expires));
        $this->setHeader('Cache-Control','private, max-age=' . $expires);
        $this->setHeader('Pragma','');
    }

    /**
     *
     */
    public function output(){
        foreach($this->headers as $key => $val){
            header($key.":".$val);
        }
        print $this->contents;
    }
}

/**
 * Class JsonResponse
 * @package PMP
 */
class JsonResponse extends Response{

    function __construct($contents)
    {
        parent::__construct($contents);
        $this->setHeaders(array(
            'Content-Type' => 'application/json; charset=utf-8'
        ));
    }
}