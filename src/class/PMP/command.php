<?php
namespace PMP;

/**
 * Class Command
 */
class Command{
    /**
     * @var CommandAction[]
     */
    static private $actions = array();

    static private $command;
    static private $option;
    static private $params;

    /**
     * @return \PMP\CommandAction[]
     */
    public static function getActions()
    {
        return self::$actions;
    }

    /**
     * @return mixed
     */
    public static function getCommand()
    {
        return self::$command;
    }

    /**
     * @return mixed
     */
    public static function getOption()
    {
        return self::$option;
    }

    /**
     * @return mixed
     */
    public static function getParams()
    {
        return self::$params;
    }

    /**
     * @param $name
     * @param CommandAction $opt
     */
    static public function addAction($name,CommandAction $opt)
    {
        self::$actions[$name] = $opt;
    }

    /**
     * @return bool
     */
    static protected function parseCommand()
    {
        global $argv;
        self::$command = null;
        self::$option = array();
        self::$params = array();

        $step = 0;
        for($i=1;$i<count($argv);$i++){
            if($step){
                self::$params[] = $argv[$i];
            }else{
                if(substr($argv[$i],0,1) == '-'){
                    self::$option[] = substr($argv[$i],1);
                }else{
                    self::$command = $argv[$i];
                    $step = 1;
                }
            }
        }
        return false;
    }

    /**
     * @param $str
     */
    static protected function writeln($str){
        print $str;
    }

    /**
     * @param $message
     */
    static protected function error($message){
        exit($message);
    }

    /**
     * @return bool
     */
    static public function execute()
    {
        self::parseCommand();
        if(self::$command && isset(self::$actions[self::$command])){
            if(self::$actions[self::$command]->run(self::$command,self::$params,self::$option)){
                return true;
            }else{
                $response = 'Not Support Action '.self::$command."\n";
                $response .= self::$actions[self::$command]->getHelp()."\n";
                self::error($response);
                return false;
            }
        }
        $response = self::helpAction();
        self::writeln($response);
        return false;
    }

    /**
     * @return string
     */
    static public function helpAction(){
        global $argv;
        $response = 'Not Found Command Action.'."\n";
        foreach(self::$actions as $name => $val){
            $response .= '    php '.$argv[0].' '.$name.' : '.$val->getDescription();
            $response .= "\n";
        }
        return $response;
    }
}