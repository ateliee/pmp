<?php
namespace PMP;

/**
 * Class CommandController
 * @package PMP
 */
class CommandController{

    static private $controller = array();

    function __construct()
    {
        $command = $this->name();
        $keys = array();
        if(is_array($command)){
            $keys = $command;
        }else{
            $keys[] = $command;
        }
        if(count($keys) > 0){
            foreach($keys as $key){
                $command_action = (new CommandAction());
                $command_action->setCallback(array($this,'execute'));
                $this->setup($key,$command_action);
                Command::addAction($command,$command_action);
            }
        }else{
            throw new \Exception(sprintf('Must be "%s" is name() method return array or string command key.',get_class($this)));
        }
    }

    /**
     * @param $command
     */
    static public function add($command){
        $class_name = ($command.'Command');
        $com = new $class_name;
        if($com instanceof CommandController){
            self::$controller[$class_name] = $com;
        }else{
            throw new \Exception(sprintf('"%s" class is must "%s" class.',$class_name,get_class(self)));
        }
    }

    /**
     * abstract action method(return command)
     *
     * @return array|string|null
     */
    public function name(){ return null; }

    /**
     * abstract action method(return command)
     *
     * @param $key
     * @param CommandAction $command
     * @return null
     */
    public function setup($key,CommandAction $command){ return $command; }

    /**
     * abstract action method
     *
     * @param $key
     * @param CommandAction $command
     * @return bool
     */
    public function execute(CommandAction $command){ return false; }
}