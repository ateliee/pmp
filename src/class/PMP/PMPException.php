<?php
namespace PMP;

/**
 * Class PMPException
 * @package PMP
 */
class PMPException extends \Exception
{
    static $template_filename = false;
    public static $escape = false;

    public $exception;

    /**
     * @param mixed $template_filename
     */
    public static function setTemplateFilename($template_filename)
    {
        self::$template_filename = $template_filename;
    }

    /**
     * @return mixed
     */
    public static function getTemplateFilename()
    {
        return self::$template_filename;
    }

    function  __construct($message, $code = 0, \Exception $previous = null) {
        //print $this->getMessage();
        //print $this->getFile();
        parent::__construct($message, $code, $previous);
    }
    public function __toString()
    {
        return parent::__toString();
    }
    public function displayError(){
        if(!ini_get( 'display_errors')){
            return;
        }

        $messages = explode("\n", $this->message);
        $messages = array_map('trim', $messages);
        $messages = array_filter($messages, 'strlen');
        $messages = array_values($messages);

        $param = array(
            "code" => $this->code,
            "messages" => $messages,
            "file" => $this->file,
            "line" => $this->line,
            "trace" => debug_backtrace(),
        );

        $template = new Template();
        // set template
        $template->assign_vars($param);
        // load template
        if(!self::$template_filename){
            self::$template_filename = dirname(__FILE__).'/../../component/view/Exception.tpl';
        }
        if($template->load(self::$template_filename)){
            $html = $template->get_display_template(true);
        }else{
            throw new PMPException('Sysmtem Error '.__CLASS__.' '.__LINE__);
        }

        print $html;
        exit;
    }
}
