<?php
namespace PMP;

/**
 * Class FileLoader
 * @package PMP
 */
class FileLoader
{
    private $url;
    private $headers;
    private $options;
    private $result;
    private $info;
    private $errorno;
    private $error;

    function __construct()
    {
        $this->headers = array(
            "HTTP/1.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Encoding:gzip ,deflate",
            "Accept-Language:ja,en-us;q=0.7,en;q=0.3",
            "Connection:keep-alive",
            "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:26.0) Gecko/20100101 Firefox/26.0"
        );
        $this->options = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_ENCODING => 'gzip',
        );
    }

    /**
     * @param $key
     * @param $val
     */
    public function setOption($key,$val)
    {
        $this->options[$key] = $val;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * @param $filename
     * @return bool
     */
    public function setCookieFile($filename)
    {
        if(!file_exists($filename)){
            if(!@touch($filename)){
                return false;
            }
        }
        $this->setOption(CURLOPT_COOKIEFILE,$filename);
        $this->setOption(CURLOPT_COOKIEJAR,$filename);
        return true;
    }

    /**
     * @param $url
     * @param array $headers
     * @param array $options
     * @return bool|mixed
     */
    public function loadUrl($url,$headers=array(),$options=array())
    {
        $this->url = $url;
        $options = $this->options + array(
                CURLOPT_HTTPHEADER => array_merge($this->headers,$headers),
            ) + $options;

        $ch = curl_init($url);
        foreach($options as $key => $val){
            curl_setopt( $ch, $key, $val);
        }
        $this->info = curl_getinfo($ch);
        $this->result = curl_exec($ch);
        if($this->errorno = curl_errno($ch)) {
            $this->error = curl_error($ch);
            $this->result = false;
        }
        curl_close($ch);
        return $this->result;
    }

    /**
     * @param $url
     * @param $post_data
     * @param array $headers
     * @return bool|mixed
     */
    public function post($url,$post_data,$headers=array())
    {
        return $this->loadUrl($url,$headers,array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post_data)
        ));
    }

    /**
     * @return mixed
     */
    public function getErrorno()
    {
        return $this->errorno;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}