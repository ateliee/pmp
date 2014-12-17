<?php

class CommandShell{
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
 */
class CommandAction{
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

/**
 * Class Command
 */
class Command{
    private $actions;

    private $command;
    private $option;
    private $params;

    /**
     *
     */
    function __construct()
    {
        $this->actions = array();
    }

    /**
     * @param $name
     * @param CommandAction $opt
     * @return $this
     */
    public function addAction($name,CommandAction $opt)
    {
        $this->actions[$name] = $opt;
        return $this;
    }

    /**
     * @return bool
     */
    protected function parseCommand()
    {
        global $argv;
        $this->command = null;
        $this->option = array();
        $this->params = array();

        $step = 0;
        for($i=1;$i<count($argv);$i++){
            if($step){
                $this->params[] = $argv[$i];
            }else{
                if(substr($argv[$i],0,1) == '-'){
                    $this->option[] = substr($argv[$i],1);
                }else{
                    $this->command = $argv[$i];
                    $step = 1;
                }
            }
        }
        return false;
    }

    /**
     * @param $str
     */
    protected function writeln($str){
        print $str;
    }

    /**
     * @param $message
     */
    protected function error($message){
        exit($message);
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $this->parseCommand();
        if($this->command && isset($this->actions[$this->command])){
            if($this->actions[$this->command]->run($this->command,$this->params,$this->option)){
                return true;
            }else{
                $response = 'not support action '.$this->command."\n";
                $response .= $this->actions[$this->command]->getHelp()."\n";
                $this->error($response);
                return false;
            }
        }
        $response = $this->helpAction();
        $this->writeln($response);
        return false;
    }

    /**
     * @return string
     */
    public function helpAction(){
        global $argv;
        $response = 'not found command action.'."\n";
        foreach($this->actions as $name => $val){
            $response .= '    php '.$argv[0].' '.$name.' : '.$val->getDescription();
            $response .= "\n";
        }
        return $response;
    }
}