<?php
namespace PMP;

/**
 * Class Date
 * @package PMP
 */
class Date
{
    private $time;

    function __construct($time=null)
    {
        if(is_numeric($time)){
            $this->time = $time;
        }else if(is_string($time)){
            $this->time = strtotime($time);
        }else if($time instanceof Date){
            $this->time = $time->getTimestamp();
        }else if(!$this->time){
            $this->time = null;
        }else{
            throw new DatabaseException('not support DatabaseDate class.');
        }
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
        return date('Y-m-d',$this->time);
    }

    /**
     * @return mixed
     */
    public function getDateTime()
    {
        return date('Y-m-d H:i:s',$this->time);
    }
    function __toString()
    {
        return $this->getDate();
    }
}
