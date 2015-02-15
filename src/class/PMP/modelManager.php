<?php
namespace PMP;

/**
 * Class ModelManager
 * @package PMP
 */
class ModelManager
{
    private static $models = array();

    /**
     * @param $name
     */
    static function add($name){
        $key = $name;
        self::$models[$key] = $name;
    }

    /**
     * @param $name
     * @return null
     */
    static function find($name)
    {
        $key = $name;
        if(isset(self::$models[$key])){
            return self::$models[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getModels()
    {
        return self::$models;
    }

    /**
     * @param $name
     * @param $alias
     * @return Model_Query
     * @throws \Exception
     */
    public function createQuery($name,$alias=null)
    {
        if(isset(self::$models[$name])){
            $query = new Model_Query($name,Database::getCurrentDB(),$alias);
            return $query;
        }

        $trace = debug_backtrace();
        throw new \Exception(sprintf('Not Found model : %s of %s line %d',$name,$trace[0]['file'],$trace[0]['line']));
    }

    /**
     * @param null $where
     * @return null|Model_QueryWhere
     */
    public function qw($where=null)
    {
        $where = new Model_QueryWhere($where);
        return $where;
    }
}

/**
 * Class Model_QueryWhere
 * @package PMP
 */
class Model_QueryWhere
{
    private $where;

    function __construct($where=null)
    {
        $this->where = '';
        if($where){
            $this->andWhere($where);
        }
    }

    /**
     * @param $where
     * @return $this
     */
    public function where($where){
        $this->where = $where;
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function andWhere($where)
    {
        $this->where .= ($this->where != "" ? " AND " : "").$where;
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where)
    {
        $this->where .= ($this->where != "" ? " OR " : "").$where;
        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function inArray($key,$val)
    {
        if(is_array($val)){
            $w = array();
            foreach($val as $k => $v){
                $w[] = $key." LIKE '%".Model::$DB_ARRAY_SPACER.$v.Model::$DB_ARRAY_SPACER."%'";
            }
            $this->where(implode(' OR ',$w));
        }else{
            $this->where($key." LIKE '%".Model::$DB_ARRAY_SPACER.$val.Model::$DB_ARRAY_SPACER."%'");
        }
        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     * @throws \Exception
     */
    public function in($key,$val)
    {
        if(is_array($val)){
            $w = array();
            foreach($val as $k => $v){
                $w[] = $key."=".$v;
            }
            $this->where(implode(' OR ',$w));
        }else{
            throw new \Exception('Model_Query:in() Is Must Be Array.');
        }
        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function andIn($key,$val)
    {
        $where = new Model_QueryWhere();
        $this->andWhere($where->in($key,$val));
        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function orIn($key,$val)
    {
        $where = new Model_QueryWhere();
        $this->orWhere($where->in($key,$val));
        return $this;
    }

    function __toString()
    {
        return ($this->where) ? '('.$this->where.')' : '';
    }

}

/**
 * Class ModelQuery
 * @package PMP
 */
class Model_Query
{
    const MODE_SELECT = 1;
    const MODE_DELETE = 3;

    private $mode;
    private $db;
    private $model_name;
    private $alias;
    private $query;

    private $finds;
    private $where;
    private $order;
    private $group;
    private $start;
    private $limit;
    private $params;

    function __construct($name,Database $db,$alias=null)
    {
        $this->mode = self::MODE_SELECT;
        $this->db = $db;
        $this->model_name = $name;
        $this->alias = $alias;

        $this->where = new Model_QueryWhere();
        $this->finds = null;
        $this->order = null;
        $this->group = null;
        $this->start = null;
        $this->limit = null;
        $this->params = array();
    }

    /**
     * @return $this
     */
    public function select(){
        $this->mode = self::MODE_SELECT;
        return $this;
    }

    /**
     * @return $this
     */
    public function delete(){
        $this->mode = self::MODE_DELETE;
        return $this;
    }

    /**
     * @param $find
     */
    public function find($find)
    {
        $this->finds = $find;
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function where($where){
        $this->where->where($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        $this->where->orWhere($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function andWhere($where){
        $this->where->andWhere($where);
        return $this;
    }

    /**
     * @param $order
     * @param null $sort
     * @return $this
     */
    public function order($order,$sort=null)
    {
        if($sort){
            $order .= ' '.$sort;
        }
        $this->order = $order;
        return $this;
    }

    /**
     * @param $group
     */
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function limit(){
        if(func_num_args() == 1){
            $this->start = 0;
            $this->limit = func_get_arg(0);
        }else if(func_num_args() == 2){
            $this->start = func_get_arg(0);
            $this->limit = func_get_arg(1);
        }else{
            throw new \Exception('Must Be limit() call Paramater 1 or 2.');
        }
        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function setParamater($key,$val)
    {
        $this->params[$key] = $val;
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParamaters(array $params)
    {
        foreach($params as $key => $val){
            $this->setParamater($key,$val);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if($this->executeQuery()){
            return true;
        }
        return false;
    }

    /**
     * @return Database
     */
    private function executeQuery()
    {
        $this->query = new SQL_Query($this->db);
        $model = new $this->model_name;

        if($this->mode == self::MODE_SELECT){
            $this->query->select($model->getTableName(),$this->alias);

            if($this->finds){
                $this->query->find($this->finds);
            }else{
                $p = array();
                $alias_name = $this->alias ? $this->alias : $model->getTableName();
                foreach($model->getColumns() as $column){
                    if($column->isDBColumn()){
                        $p[] = '`'.$alias_name.'`.`'.$column->getName().'`';
                    }
                }
                $this->query->find($p);
            }
        }else{
            $this->query->delete($model->getTableName());
        }
        $where = (string)$this->where;
        if($where){
            $this->query->where($where);
        }
        if($this->group){
            $this->query->group($this->group);
        }
        if($this->order){
            $this->query->order($this->order);
        }
        if($this->limit){
            if($this->start){
                $this->query->limit($this->start,$this->limit);
            }else{
                $this->query->limit($this->limit);
            }
        }
        return $this->query->execute($this->params);
    }

    /**
     * @return Model
     */
    public function getResult()
    {
        $model = new $this->model_name;
        if($results = $this->getArrayResult()){
            $model->setArray($results);
        }
        return $model;
    }

    /**
     * @return Model
     */
    public function getFirstResult()
    {
        $model = new $this->model_name;
        if($results = $this->getArrayFirstResult()){
            $model->setArray($results);
        }
        return $model;
    }

    /**
     * @return array|null
     */
    public function getArrayResult()
    {
        return $this->executeQuery()->getResult();
    }

    /**
     * @return array|null
     */
    public function getArrayFirstResult()
    {
        return $this->executeQuery()->getFirstResult();
    }

    /**
     * @return array|null
     */
    public function getArrayResults()
    {
        return $this->executeQuery()->getResults();
    }

    /**
     * @param null $default
     * @return null
     */
    public function getResultOne($default=null)
    {
        if($results = $this->getArrayFirstResult()){
            return reset($results);
        }
        return $default;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        $results = array();
        if($query_results = $this->getArrayResults()){
            foreach($query_results as $res){
                $model = new $this->model_name;
                $model->setArray($res);
                $results[] = $model;
            }
        }
        return $results;
    }

    /**
     * @return int
     */
    public function findRow()
    {
        return $this->db->findRow();
    }
}