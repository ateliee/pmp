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

        $this->where = null;
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
        $this->where = $where;
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        $this->where .= ($this->where != "" ? " OR " : "").$where;
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function andWhere($where){
        $this->where .= ($this->where != "" ? " AND " : "").$where;
        return $this;
    }

    /**
     * @param $order
     */
    public function order($order)
    {
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
        if($this->where){
            $this->query->where($this->where);
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
     *
     */
    public function getResult()
    {
        $model = new $this->model_name;
        if($results = $this->getArrayResult()){
            $model->setParameters($results);
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
        if($results = $this->getArrayResult()){
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
                $model->setParameters($res);
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