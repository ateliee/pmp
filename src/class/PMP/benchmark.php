<?php
namespace PMP;

/**
 * Class Benchmark
 */
class Benchmark{
    private $time_start;
    private $time_end;
    private $marker;

    function __construct(){
        $this->start();
    }

    /**
     *
     */
    public function start(){
        $this->time_end = 0;
        $this->marker = array();
        return $this->time_start = microtime(true);
    }

    /**
     * @param $name
     * @return int
     */
    public function setMark($name){
        $this->marker[$name] = microtime(true);
        return $this->getTime($name);
    }

    /**
     * @param $name
     * @param null $start_key
     * @return int
     */
    protected function getTime($name,$start_key=null){
        $time_start = ($start_key === null) ? $this->time_start : $this->getMark($start_key);
        $time_end = $this->getMark($name);
        return ($time_end - $time_start);
    }

    /**
     * @param $name
     * @return null
     */
    protected function getMark($name){
        if(isset($this->marker[$name])){
            return $this->marker[$name];
        }
        return null;
    }

    /**
     *
     */
    public function stop(){
        return $this->time_end = microtime(true);
    }

    /**
     * @param bool $diplay
     * @return string
     */
    public function display($diplay=true){
        $param = array();
        $param[] = array("name" => "Start","time" => $this->time_start);
        foreach($this->marker as $key => $val){
            $param[] = array("name" => $key,"time" => $val);
        }
        $param[] = array("name" => "Stop","time" => $this->time_end);
        $total = ($this->time_end - $this->time_start);

        $html = '';
        $html .= '<table>'."\n";
        $html .= '<tr>'."\n";
            $html .= '<th></th>'."\n";
            $html .= '<th>time index</th>'."\n";
            $html .= '<th>ex time</th>'."\n";
            $html .= '<th>%</th>'."\n";
        $html .= '</tr>'."\n";
        $before = 0;
        foreach($param as $key => $val){
            $time = ($before ? (($val["time"] != $before) ? ($val["time"] - $before) : 0) : 0);
            $p = ($time ? (($val["time"] != $before) ? (($time / $total) * 100) : 100) : 0);
            $html .= '<tr>'."\n";
                $html .= '<th>'.$val["name"].'</th>'."\n";
                $html .= '<td>'.$val["time"].'</td>'."\n";
                $html .= '<td>'.($time ? $time : "-").'</td>'."\n";
                $html .= '<td>'.round($p,2).'%</td>'."\n";
            $html .= '</tr>'."\n";
            $before = $val["time"];
        }
        $html .= '</table>';

        if($diplay){
            print $html;
        }
        return $html;
    }
}