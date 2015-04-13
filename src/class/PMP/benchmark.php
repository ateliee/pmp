<?php
namespace PMP;

/**
 * Class BenchmarkStamp
 * @package PMP
 */
class BenchmarkStamp{
    private $name;
    private $time;

    function __construct($name,$time)
    {
        $this->name = $name;
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }


}

/**
 * Class Benchmark
 * @package PMP
 */
class Benchmark{
    private $time_start;
    private $time_end;
    /**
     * @var BenchmarkStamp[]
     */
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
        $this->marker[] = new BenchmarkStamp($name,microtime(true));
        return $this->getTime(count($this->marker) - 1);
    }

    /**
     * @param $key
     * @param null $start_key
     * @return mixed
     */
    protected function getTime($key,$start_key=null){
        $time_start = ($start_key === null) ? $this->time_start : $this->getMark(0)->getTime();
        $time_end = $this->getMark($key)->getTime();
        return ($time_end - $time_start);
    }

    /**
     * @param $key
     * @return null|BenchmarkStamp
     */
    protected function getMark($key){
        if(isset($this->marker[$key])){
            return $this->marker[$key];
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
        $param[] = new BenchmarkStamp('Start',$this->time_start);
        foreach($this->marker as $key => $val){
            $param[] = $val;
        }
        $param[] = new BenchmarkStamp('Stop',$this->time_start);
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
            $time = ($before ? (($val->getTime() != $before) ? ($val->getTime() - $before) : 0) : 0);
            $p = ($time ? (($val->getTime() != $before) ? (($time / $total) * 100) : 100) : 0);
            $html .= '<tr>'."\n";
            $html .= '<th>'.$val->getName().'</th>'."\n";
            $html .= '<td>'.$val->getTime().'</td>'."\n";
            $html .= '<td>'.($time ? $time : "-").'</td>'."\n";
            $html .= '<td>'.round($p,2).'%</td>'."\n";
            $html .= '</tr>'."\n";
            $before = $val->getTime();
        }
        $html .= '</table>';

        if($diplay){
            print $html;
        }
        return $html;
    }
}