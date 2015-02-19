<?php
namespace PMP;

/**
 * Class Date
 * @package PMP
 */
class Date
{
    private $time;
    private $default_format;

    function __construct($time=null,$default_format='Y-m-d')
    {
        var_dump($time);
        $this->setDefaultFormat($default_format);
        if(is_numeric($time)){
            $this->time = $time;
        }else if(is_string($time)){
            $this->time = strtotime($time);
        }else if($time instanceof Date){
            $this->time = $time->getTimestamp();
        }else if(!$this->time){
            $this->time = time();
        }else{
            throw new DatabaseException('Not Support "time" Format.');
        }
    }

    /**
     * @param $format
     * @return bool|null|string
     */
    public function format($format)
    {
        if($this->time){
            return date($format,$this->time);
        }
        return null;
    }

    /**
     * @param mixed $default_format
     */
    public function setDefaultFormat($default_format)
    {
        $this->default_format = $default_format;
    }

    /**
     * @return mixed
     */
    public function getDefaultFormat()
    {
        return $this->default_format;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->format('Y-m-d');
    }

    /**
     * @return bool|null|string
     */
    public function getYear()
    {
        return $this->format('Y');
    }

    /**
     * @return bool|null|string
     */
    public function getMonth()
    {
        return $this->format('n');
    }

    /**
     * @return bool|null|string
     */
    public function getDay()
    {
        return $this->format('j');
    }

    /**
     * @return mixed
     */
    public function getDateTime()
    {
        return $this->format('Y-m-d H:i:s');
    }

    function __toString()
    {
        $str = $this->format($this->default_format);
        if(is_string($str)){
            return $str;
        }
        return '';
    }
}
