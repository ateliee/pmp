<?php
namespace PMP;

/**
 * Class ModelManager
 * @package PMP
 */
class ModelManager
{
    private static $models = array();

    /**
     * @param $name
     */
    static function add($name){
        $key = strtolower($name);
        self::$models[$key] = $name;
    }

    /**
     * @param $name
     * @return null
     */
    static function find($name)
    {
        $key = strtolower($name);
        if(isset(self::$models[$key])){
            return self::$models[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getModels()
    {
        return self::$models;
    }

}