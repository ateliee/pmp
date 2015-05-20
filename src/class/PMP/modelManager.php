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
    protected $where;
    protected $params;

    function __construct($where=null)
    {
        $this->params = array();
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
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
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
        $unique_key = get_class($this);
        if(is_array($val)){
            $w = array();
            foreach($val as $k => $v){
                $id = $unique_key.'_'.$key.'_'.$k;
                $w[] = $key." LIKE '%".Model::$DB_ARRAY_SPACER.':'.$id.Model::$DB_ARRAY_SPACER."%'";
                $this->setParamater($id,$v);
            }
            $this->where(implode(' OR ',$w));
        }else{
            $id = $unique_key.'_'.$key;
            $this->where($key." LIKE '%".Model::$DB_ARRAY_SPACER.':'.$id.Model::$DB_ARRAY_SPACER."%'");
            $this->setParamater($id,$val);
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
        $unique_key = get_class($this);
        if(is_array($val)){
            $w = array();
            foreach($val as $k => $v){
                $id = $unique_key.'_'.$key.'_'.$k;
                $w[] = $key."=':".$id."'";
                $this->setParamater($id,$v);
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

}

/**
 * Class Model_QueryBase
 * @package PMP
 */
class Model_QueryBase extends Model_QueryWhere
{
    /**
     * @var Model
     */
    protected $model;
    protected $model_name;
    protected $alias;

    function __construct($name,$alias=null)
    {
        $this->model = null;
        $this->model_name = $name;
        $this->alias = $alias;
    }

    /**
     * @return Model
     */
    protected function getModel()
    {
        if(!$this->model){
            $this->model = new $this->model_name;
        }
        return $this->model;
    }

    /**
     * @return null
     */
    protected function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string[]
     */
    protected function getModelFind()
    {
        $p = array();
        $alias_name = $this->alias ? $this->alias : $this->getModel()->getTableName();
        foreach($this->getModel()->getColumns() as $column){
            if($column->isDBColumn()){
                $p[] = '`'.$alias_name.'`.`'.$column->getName().'`';
            }
        }
        return $p;
    }
}

/**
 * Class Model_SubQuery
 * @package PMP
 */
class Model_SubQuery extends Model_QueryBase
{
    const MODE_LEFTJOIN = 1;
    const MODE_RIGHTJOIN = 2;
    const MODE_INNERJOIN = 3;

    /**
     * @var int
     */
    private $joinType;
    function __construct($type,$name,$alias=null)
    {
        $q = explode(' ',$name);
        $q = array_filter($q,'strlen');
        if(count($q) == 0){
            throw new \Exception(sprintf('Un Support SubQuery Near "%s"',$name));
        }
        $where = null;
        if(count($q) == 1){
        }else if(count($q) >= 2){
            $name = $q[0];
            $alias = $q[1];
            if(count($q) == 3 || strtoupper($q[2]) != 'ON'){
                throw new \Exception(sprintf('Un Error SQL."%s"',$name));
            }
            $where = implode(' ',array_slice($q,3));
        }
        parent::__construct($name,$alias);

        if(!in_array($type,array(self::MODE_LEFTJOIN,self::MODE_RIGHTJOIN,self::MODE_INNERJOIN))){
            throw new \Exception(sprintf('Un Support Join Type %s',$type));
        }
        $this->joinType = $type;

        if($where){
            $this->where($where);
        }
    }

    /**
     * @return int
     */
    public function getJoinType()
    {
        return $this->joinType;
    }
}

/**
 * Class Model_Query_Result
 * @package PMP
 */
/*
class Model_Query_Result implements \ArrayAccess{
    private $container = array();

    public function __construct() {
        $this->container = array();
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}*/

/**
 * Class ModelQuery
 * @package PMP
 */
class Model_Query extends Model_QueryBase
{
    const MODE_SELECT = 1;
    const MODE_DELETE = 3;

    private $mode;
    private $db;
    private $query;

    /**
     * @var Model_SubQuery[]
     */
    private $subquery;
    private $finds;
    private $order;
    private $group;
    private $start;
    private $limit;

    function __construct($name,Database $db,$alias=null)
    {
        parent::__construct($name,$alias);

        $this->mode = self::MODE_SELECT;
        $this->db = $db;

        $this->subquery = array();
        $this->finds = null;
        $this->order = null;
        $this->group = null;
        $this->start = null;
        $this->limit = null;
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
     * @return $this
     * @throws \Exception
     */
    public function find($find)
    {
        if(!$find){
            throw new \Exception('"find" Paramater Is Empty.');
        }
        $this->finds = $find;
        return $this;
    }

    /**
     * @param $target
     * @param null $alias
     * @return $this
     */
    public function leftJoin($target,$alias=null)
    {
        $sub = new Model_SubQuery(Model_SubQuery::MODE_LEFTJOIN,$target,$alias);
        $this->subquery[] = $sub;
        return $this;
    }

    /**
     * @param $target
     * @param null $alias
     * @return $this
     */
    public function rightJoin($target,$alias=null)
    {
        $sub = new Model_SubQuery(Model_SubQuery::MODE_RIGHTJOIN,$target,$alias);
        $this->subquery[] = $sub;
        return $this;
    }

    /**
     * @param $target
     * @param null $alias
     * @return $this
     */
    public function innerJoin($target,$alias=null)
    {
        $sub = new Model_SubQuery(Model_SubQuery::MODE_INNERJOIN,$target,$alias);
        $this->subquery[] = $sub;
        return $this;
    }

    /**
     * @param $order
     * @param null $sort
     * @return $this
     * @throws \Exception
     */
    public function order($order,$sort=null)
    {
        if(!$order){
            throw new \Exception('"order" Paramater Is Empty.');
        }
        if($sort){
            $order .= ' '.$sort;
        }
        $this->order = $order;
        return $this;
    }

    /**
     * @param $group
     * @return $this
     * @throws \Exception
     */
    public function group($group)
    {
        if(!$group){
            throw new \Exception('"group" Paramater Is Empty.');
        }
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
        $model = $this->getModel();

        if($this->mode == self::MODE_SELECT){
            $this->query->select($model->getTableName(),$this->alias);

            if($this->finds){
                $this->query->find($this->finds);
            }else{
                $p = $this->getModelFind();
                /*foreach($this->subquery as $q){
                    $p += $q->getModelFind();
                }*/
                $this->query->find($p);
            }
        }else{
            $this->query->delete($model->getTableName());
        }
        if(count($this->subquery) > 0){
            foreach($this->subquery as $q){
                $qn = $q->getModel()->getTableName();
                if($q->getAlias()){
                    $qn .= ' AS '.$q->getAlias();
                }
                if($q->getJoinType() == Model_SubQuery::MODE_LEFTJOIN){
                    $this->query->leftJoin($qn,$q->getWhere());
                }else if($q->getJoinType() == Model_SubQuery::MODE_RIGHTJOIN){
                    $this->query->rightJoin($qn,$q->getWhere());
                }else if($q->getJoinType() == Model_SubQuery::MODE_INNERJOIN){
                    $this->query->innerJoin($qn,$q->getWhere());
                }
            }
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
        $params = array();
        if($this->params){
            foreach($this->params as $key => $p){
                if($p instanceof Model){
                    $params[$key] = $p->getId();
                }else{
                    $params[$key] = $p;
                }
            }
        }
        return $this->query->execute($params);
    }

    /**
     * @return Model
     */
    public function getResult()
    {
        $model = $this->getModel();
        if($results = $this->getArrayResult()){
            $model->setArray($results,true,true);
        }
        return $model;
    }

    /**
     * @return Model
     */
    public function getFirstResult()
    {
        $model = $this->getModel();
        if($results = $this->getArrayFirstResult()){
            $model->setArray($results,true,true);
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
            $res = reset($results);
            $model = new $this->model_name;
            $model->setArray($res,true,true);
            return $model;
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
                $model->setArray($res,true,true);
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