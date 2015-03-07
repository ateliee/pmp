<?php
namespace PMP;

/**
 * Class Pager
 * @package PMP
 */
class Pager {
    const PAGE_PARAM = 1;
    const PAGE_URI = 2;

    private $mode;
    private $param_name;
    private $current;
    private $total;
    private $limit;
    private $round;
    private $uri;

    function  __construct($mode=self::PAGE_PARAM){
        $this->setMode($mode,'page');
        $this->mode = $mode;
        $this->current = 0;
        $this->total = 0;
        $this->limit = 0;
    }

    /**
     * @param $mode
     * @param $param
     * @throws \Exception
     */
    public function setMode($mode,$param){
        switch($mode){
            case self::PAGE_PARAM;
            case self::PAGE_URI;
                $this->mode = $mode;
                $this->param_name = $param;
                break;
            default:
                throw new \Exception(sprintf('UnSupport Mode %s',$mode));
                break;
        }
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
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
     * @return string
     * @throws \Exception
     */
    public function getUri($num){
        if(!$this->uri){
            throw new \Exception('Pager() NuSetup uri Param. Please setBaseUri() Call.');
        }
        $uri = $this->uri;

        $pase_url = parse_url($uri);
        if($this->mode == self::PAGE_PARAM){
            $query = array();
            if(isset($pase_url['query'])){
                parse_str(urldecode($pase_url['query']),$query);
            }
            if($num > 0){
                $query[$this->param_name] = $num;
            }
            $pase_url['query'] = http_build_query($query);
        }else if($this->mode == self::PAGE_URI){
            if(!isset($pase_url['path'])){
                $pase_url['path'] = '';
            }
            if($num > 0){
                $page_append = '';
                if(substr($pase_url,strlen($pase_url) - 1) != '/'){
                    $page_append .= '/';
                }
                if (strstr($this->param_name, '%d')) {
                    $page_append .= sprintf($this->param_name,$num);
                }else{
                    $page_append .= $this->param_name.'/'.$num;
                }
                $pase_url['path'] .= $page_append;
            }
        }
        return $this->unparse_url($pase_url);
    }

    private function unparse_url($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
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