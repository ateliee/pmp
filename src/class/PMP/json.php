<?php
namespace PMP;

if(!defined('JSON_PRETTY_PRINT')){
    define('JSON_PRETTY_PRINT',128);
    define('PMP_JSON_PRETTY_PRINT',true);
}

/**
 * Class jsonObj
 * @package PMP
 */
class jsonObj{
    private $data;
    private $option = 0;

    public function __construct($option=0){
        if($option != 0){
            $this->setOption($option);
        }
    }

    /**
     * @param $option
     */
    public function setOption($option){
        $this->option = $option;
    }

    /**
     * @param $obj
     */
    public function encode($obj){
        $this->data = $obj;
        return $this->encodeData();
    }

    /**
     * @return string
     */
    public function encodeData(){
        $str = json_encode($this->data,$this->option);
        if(defined('PMP_JSON_PRETTY_PRINT')){
            $str = $this->parse_json($str);
        }
        return $str;
    }

    /**
     * @param $str
     * @return mixed
     */
    public function decode($str){
        $this->data = json_decode($str);
        return $this->data;
    }

    /**
     * @param $json
     * @return string
     */
    private function parse_json($json) {
        /**
         * Indents a flat JSON string to make it more human-readable.
         *
         * @param string $json The original JSON string to process.
         *
         * @return string Indented version of the original JSON string.
         */
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

                // If this character is the end of an element,
                // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }
}
