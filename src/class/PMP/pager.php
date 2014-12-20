<?php
namespace PMP;

/**
 * Class Pager
 * @package PMP
 */
class Pager {
    private $current;
    private $total;
    private $limit;
    private $round;
    private $uri;

    function  __construct(){
        $this->current = 0;
        $this->total = 0;
        $this->limit = 0;
    }

    /**
     * @param $page
     * @return $this
     */
    public function setCurrentPage($page){
        $this->current = $page;
        return $this;
    }

    /**
     * @param $page
     * @return $this
     */
    public function setTotal($page){
        $this->total = $page;
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit){
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $round
     * @return $this
     */
    public function setRound($round){
        $this->round = $round;
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setBaseUri($uri){
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param $num
     * @return mixed
     */
    public function getUri($num){
        $uri = $this->uri;
        $uri = sprintf($uri,$num);
        return $uri;
    }

    /**
     * @return array
     */
    public function getPager(){
        // total page
        $total_page = $this->total > 0 ? ceil($this->total / $this->limit) : 0;

        if(floor($total_page / 2) > $this->current){
            $pager_start = max($this->current - floor($this->round / 2),0);
            $pager_end = min($pager_start + $this->round,$total_page);
        }else{
            $pager_end = min($this->current + ceil($this->round / 2),$total_page);
            $pager_start = max($pager_end - $this->round,0);
        }
        $show_start = max(($this->current * $this->round),0);
        $show_start = ($this->total > 0) ? $show_start + 1 : 0;
        $show_end = min(($show_start + $this->round) - 1,$this->total);
        // ページ送り
        $pager_next = false;
        $pager_prev = false;
        $pager_list = array();
        if(($this->current - 1) >= 0){
            $n = ($this->current - 1);
            $pager_prev = array(
                "page" => ($n + 1),
                "url" => $this->getUri($n)
            );
        }
        $pager_num = 0;
        if($total_page > 1){
            for($i=$pager_start;$i<$pager_end;$i++){
                $n = $i;
                $uri = $this->getUri($n);
                if($i == $this->current){
                    $pager_list[] = array(
                        "page" => ($n + 1),
                        "url" => $uri,
                        "current" => true
                    );
                    $pager_num ++;
                }else{
                    $pager_list[] = array(
                        "page" => ($n + 1),
                        "url" => $uri,
                        "current" => false
                    );
                    $pager_num ++;
                }
            }
            //$pager = implode("",$pager_list);
        }
        if(($this->current + 1) < $total_page){
            $n = ($this->current + 1);
            $pager_next = array(
                "page" => ($n + 1),
                "url" => $this->getUri($n)
            );
        }
        return array(
            "total" => $this->total,
            "limit" => $this->limit,
            "prev" => $pager_prev,
            "next" => $pager_next,
            "start_page" => max($pager_start+1,1),
            "end_page" => $pager_end,
            "start" => $show_start,
            "end" => $show_end,
            "pages" => $pager_list,
        );
    }
}