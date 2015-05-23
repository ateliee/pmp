<?php
namespace PMP;

/**
 * Class Shell
 * @package PMP
 */
class Shell
{
    /**
     * @param $str
     * @return mixed
     */
    public function escape($str)
    {
        $str = str_replace("\\","\\\\",$str);
        $str = str_replace("\"","\\\"",$str);
        return $str;
    }

    /**
     * @param $str
     * @return string
     */
    public function outputLine($str)
    {
        $command = $str."\n";
        print $command;
        return $command;
    }

    /**
     * @param $str
     * @return string
     */
    public function exitLine($str)
    {
        $command = $str."\n";
        exit($command);
    }

    /**
     * @param $command
     * @param $output
     * @return string
     */
    public function exec($command,&$output)
    {
        return exec($command,$output);
    }
}

/**
 * Class CommandShell
 * @package PMP
 */
class CommandShell extends Shell{
    private $message;
    private $callback;
    private $result;

    function __construct($message,$callback=null)
    {
        $this->message = $message;
        $this->callback = $callback;
        $this->result = null;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $input
     * @return bool|mixed
     */
    public function executeInput($input)
    {
        if($this->callback){
            if(is_string($this->callback) || is_array($this->callback)){
                return call_user_func_array($this->callback,array($input));
            }else if(is_callable($this->callback)){
                $func = ($this->callback);
                return $func($input);
            }else{
                exit('un support callback '.$this->callback.' ');
            }
        }else{
            if($input != ""){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $input
     * @return bool
     */
    public function execute($input)
    {
        if($this->executeInput($input)){
            $this->result = $input;
            return true;
        }
        return false;
    }

    /**
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }
}

/**
 * Class CommandAction
 * @package PMP
 */
class CommandAction extends Shell{
    private $description;
    private $shells;
    private $callback;
    private $help;
    private $call_command;
    private $call_params;
    private $call_options;

    /**
     *
     */
    function __construct()
    {
        $this->call_command = null;
        $this->call_options = array();
        $this->call_params = array();
        $this->shells = array();
        $this->callback = null;
    }

    /**
     * @param CommandShell $shell
     */
    public function addShell(CommandShell $shell)
    {
        $this->shells[] = $shell;
        return $this;
    }

    /**
     * @param $key
     * @return CommandShell
     */
    public function getShell($key)
    {
        return $this->shells[$key];
    }

    /**
     * @param $func
     * @return $this
     */
    public function setCallback($func)
    {
        $this->callback = $func;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallParams()
    {
        return $this->call_params;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getCallParam($key,$default=null)
    {
        if(isset($this->call_params[$key])){
            return $this->call_params[$key];
        }
        return $default;
    }

    /**
     * @return mixed
     */
    public function getCallOptions()
    {
        return $this->call_options;
    }

    /**
     * @param $key
     * @return bool
     */
    public function checkCallOption($key)
    {
        if(in_array($key,$this->call_options)){
            return true;
        }
        return false;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $help
     * @return $this
     */
    public function setHelp($help){
        $this->help = $help;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param $message
     */
    protected function error($message){
        exit($message);
    }

    /**
     * @param $message
     */
    protected function output($message)
    {
        print $message;
    }

    /**
     * @return bool|mixed
     */
    protected function execute()
    {
        if($this->callback){
            if(is_string($this->callback) || is_array($this->callback)){
                return call_user_func_array($this->callback,array($this));
            }else if(is_callable($this->callback)){
                $func = ($this->callback);
                return $func($this);
            }else{
                $this->error('un support callback '.$this->callback.' ');
            }
        }
        return true;
    }

    /**
     * @param $command
     * @param $params
     * @param $options
     * @return bool
     */
    public function run($command,$params,$options)
    {
        $this->call_command = $command;
        $this->call_params = $params;
        $this->call_options = $options;

        set_time_limit(0);

        if(count($this->shells) > 0){
            $stdin = fopen("php://stdin", "r");
            if ( ! $stdin) {
                $this->error("[error] STDIN failure.\n");
            }

            $keys = array_keys($this->shells);
            $index = 0;
            while (true) {
                if(count($keys) <= $index){
                    break;
                }
                $current_key = $keys[$index];
                $shell = $this->shells[$current_key];

                $this->output($shell->getMessage());

                $res = trim(fgets($stdin, 256));
                if(!$shell->execute($res)){
                    continue;
                }
                $index ++;
            }
            fclose($stdin);
        }
        return $this->execute();
    }
}
