<?php
namespace PMP;

/**
 * Class Model
 * @package PMP
 */
class Model{
    static $DB_ARRAY_SPACER = '-';

    private $id;
    private $db;
    private $table_options;
    private $table_fields;

    static private $from_connection = array();

    function  __construct(){
        $this->db = Database::getCurrentDB();
        $this->table_options = array(
            "engine" => Database::ENGINE_DEFAULT
        );
        $this->table_fields = array();
        $parents_class = class_parents($this);
        $class_name = get_class($this);
        $class = array_merge($parents_class,array($class_name => $class_name));
        foreach($class as $class_name){
            $vars = get_class_vars($class_name);
            foreach($vars as $k => $v){
                $method_name = str_replace('_','',$k);
                if(method_exists($this,$method_name.'Column')){
                    $v = $method_name.'Column';
                    $key = $k;
                    try{
                        $field = ($this->{$v}());
                    }catch (PMPException $e){
                        throw new PMPException($class_name.'->'.$v.'() is return value error.'.$e->getMessage());
                    }
                    if($field && is_object($field) && ($field instanceof ModelColumn)){
                        if(!$field->getName()){
                            $field->setName($key);
                        }
                        $this->table_fields[$key] = $field;

                        if($field->isCompareColumn()){
                            $self = $field->getSelfColumn();
                            if(!isset(self::$from_connection[$class_name])){
                                self::$from_connection[$class_name] = array();
                            }
                            if(!isset(self::$from_connection[$class_name][$self])){
                                self::$from_connection[$class_name][$self] = array();
                            }
                            self::$from_connection[$class_name][$self][$k] = $k;
                        }
                    }else{
                        throw new PMPException($class_name.'->'.$v.'() must be return ModelColumn');
                    }
                }
            }
        }
    }

    /**
     *
     */
    protected function modelUpdate(){
    }

    /**
     * @return array
     */
    public function idColumn(){
        return new ModelColumn(array("type" => ModelColumn::$TYPE_BIGINT,"ai" => true,"null" => false,"comment" => __("primary key")));
    }

    /**
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @return array
     */
    public function getColumns(){
        return $this->table_fields;
    }

    /**
     * @return array
     */
    private function getDBColumns(){
        $fields = array();
        foreach($this->table_fields as $key => $colum){
            if(!$colum->isCompareColumn()){
                $fields[$key] = $colum->getDBColumn();
            }
        }
        return $fields;
    }

    /**
     * @return array
     */
    /*
    public function getFormColumns(){
        $columns = array();
        foreach($this->table_fields as $k => $v){
            if($v->getFormenable()){
                $columns[$k] = array(
                    "type" => self::convertColumnsToFormType($v),
                    "value" => $this->get($k),
                    "attr" => self::convertColumnsToFormAttr($v),
                );
            }
        }
        return $columns;
    }*/

    /**
     * @param $key
     * @return bool
     */
    public function isExists($key){
        return (array_key_exists($key,$this->table_fields) ? true : false);
    }

    /**
     * @param $key
     * @return ModelColumn
     */
    public function getColumn($key){
        return $this->table_fields[$key];
    }

    /**
     * @return string
     */
    public function getTableName(){
        $classes = explode('\\',get_class($this));
        return end($classes);
    }

    /**
     * @param Database $db
     * @return $this
     */
    public function setDatabase(Database $db){
        $this->db =  $db;
        return $this;
    }

    /**
     * @param $args
     * @param bool $first_set
     * @return $this|null
     */
    public function find($args,$first_set=false){
        $params = array();
        if(is_numeric($args)){
            $params = array('id' => $args);
        }else if(is_array($args)){
            foreach($args as $key => $val){
                if($val instanceof Model){
                    $params[$key] = $val->getId();
                }else{
                    $params[$key] = $val;
                }
            }
        }else{
            throw new \Exception('find() Paramater Must Be number or array.');
        }
        if($first_set){
            $results = $this->findQuery($params)->getResults();
            if(count($results) > 0){
                $result = $results[0];
            }
        }else{
            $result = $this->findQuery($params)->getResult();
        }
        if(isset($result)){
            $this->setArray($result,true,true);
            return $this;
        }
        return null;
    }

    /**
     * @param $args
     * @return mixed
     */
    public function findArray($args){
        $this->find($args);
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray(){
        $columns = array();
        foreach($this->getColumns() as $k => $v){
            $vv = $this->{$k};
            if($vv instanceof Model){
                $columns[$k] = $vv->getId();
            }else{
                $columns[$k] = $vv;
            }
        }
        return $columns;
    }

    /**
     * @return array
     */
    private function toDBArray(){
        $columns = array();
        foreach($this->getColumns() as $k => $v){
            if($v->isCompareColumn()){
                continue;
            }
            $vv = $this->{$k};
            if($vv instanceof Model){
                $columns[$k] = $vv->getId();
            }else if($v->getType() == ModelColumn::$TYPE_ARRAY){
                if(count($vv) > 0){
                    $columns[$k] = self::$DB_ARRAY_SPACER.implode(self::$DB_ARRAY_SPACER,$vv).self::$DB_ARRAY_SPACER;
                }else{
                    $columns[$k] = null;
                }
            }else if($v->getType() == ModelColumn::$TYPE_DATA){
                $columns[$k] = serialize($vv);
            }else{
                $columns[$k] = $vv;
            }
        }
        return $columns;
    }

    /**
     * @param $args
     * @return mixed
     * @throws PMPException
     */
    public function findQuery($args=array()){
        $select_fields = array();
        foreach($this->getDBColumns() as $k => $v){
            $select_fields[$k] = '`'.$k.'`';
        }
        $query = $this->db->createQuery()
            ->select($this->getTableName())->find($select_fields);

        if(is_numeric($args)){
            $db = $query->where($this->db->escapeColumn("id")."=':ID'")->execute(array("ID" => $args));
        }else if(is_array($args)){
            if(count($args) > 0){
                $where = array();
                foreach($args as $k => $v){
                    $key = ":".$k;
                    //$where[] = $this->db->escapeColumn($k)."='".$key."'";
                    $where[] = $k."='".$key."'";
                }
                $query = $query->where(implode(" AND ",$where));
            }
            $db = $query->execute($args);
        }else{
            throw new PMPException("find() error argment.");
        }
        return $db;
    }

    /**
     * @param $args
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @return array|null
     */
    /*
    public function findBy($args,$order=array(),$limit=0,$offset=0){
        $results = $this->db->findBy($args,$order,$limit,$offset);
        $list = null;
        if($results){
            $list = array();
            foreach($results as $key => $item){
                $class_name = get_class($this);
                $obj = new $class_name();
                $obj->setArray($item,true,true);
                $list[$key] = $obj;
            }
        }
        return $list;
    }*/

    /**
     * @return null
     */
    public function flush()
    {
        $result = null;
        $columns = $this->toDBArray();
        if($this->id){
            $result = $this->db->update($this->getTableName(),$columns,$this->getDBColumns(),array("id" => $this->id));
            if($this->db->affectedRows() <= 0){
                if($this->db->createQuery($this->getTableName())->find("`id`")->where("`id`=:id")->execute(array("id" => $this->id))->findRow() <= 0){
                    $result = $this->db->insert($this->getTableName(),$columns,$this->getDBColumns());
                    $this->id = $this->db->lastId();
                }
            }
        }else{
            $result = $this->db->insert($this->getTableName(),$columns,$this->getDBColumns());
            $this->id = $this->db->lastId();
        }
        return $result;
    }

    /**
     * @param $key
     * @param $val
     * @param bool $method_call
     * @param bool $db_data
     * @return $this
     * @throws PMPException
     */
    public function set($key,$val,$method_call = true,$db_data=false)
    {
        if(is_string($key)){
            $column = $this->getColumn($key);
            if(in_array($column->getType(),array(ModelColumn::$TYPE_DATE))){
                $val = new \PMP\Date($val);
            }else if(in_array($column->getType(),array(ModelColumn::$TYPE_DATETIME,ModelColumn::$TYPE_TIMESTAMP))){
                $val = new \PMP\DateTime($val);
            }

            $method = "set".ucfirst($key);
            if($method_call && method_exists($this,$method)){
                $this->$method($val);
            }else if(property_exists($this,$key)){
                if($db_data){
                    $val = $column->getConvertValue($val);
                    if($column->getType() == ModelColumn::$TYPE_ARRAY){
                        $v = array();
                        if($val != ''){
                            $arr = explode(self::$DB_ARRAY_SPACER,$val);
                            array_shift($arr);
                            array_pop($arr);
                            foreach($arr as $vv){
                                $v[] = $vv;
                            }
                        }
                        $this->{$key} = $v;
                    }else if($column->getType() == ModelColumn::$TYPE_DATA){
                        $v = null;
                        if($val){
                            $v = unserialize($val);
                        }
                        $this->{$key} = $v;
                    }else{
                        $this->{$key} = $val;
                    }
                }else{
                    $this->{$key} = $val;
                }
            }
            $class = get_class($this);
            if(isset(self::$from_connection[$class][$key])){
                foreach(self::$from_connection[$class][$key] as $k => $vv){
                    $c = $this->getColumn($k);
                    if($c->isCompareColumn()){
                        $mm = new ModelManager();
                        $v = $mm->createQuery($c->getTargetName(),'p')
                            ->where('`p`.`'.$c->getTargetColumn().'`=:key')
                            ->setParamater('key',$this->{$key});
                        if($c->getTargetOrder()){
                            $v->order($c->getTargetOrder(),$c->getTargetSort());
                        }
                        $this->{$k} = $v;
                    }
                }
            }
        }else{
            throw new PMPException('Model->set() args.');
        }
        return $this;
    }

    /**
     * @param $args
     * @param bool $method_call
     * @param bool $db_data
     * @return $this
     * @throws PMPException
     */
    public function setArray($args,$method_call = true,$db_data=false)
    {
        if(is_array($args)){
            foreach($args as $k => $v){
                $this->set($k,$v,$method_call,$db_data);
            }
        }else{
            throw new PMPException('Model->setArray() args.');
        }
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $method = 'get'.ucfirst($key);
        if(method_exists($this,$method)){
            $get = $this->{$method}();
        }else{
            $get = $this->{$key};
        }
        return $get;
    }

    /**
     * @return int
     */
    public function upgrade()
    {
        $change_column_num = 0;
        $table_name = $this->getTableName();

        $columns = $this->getDBColumns();
        if($this->db->checkTableExists($table_name)){
            $results = $this->db->getTableColumns($table_name);
            $results_index = $this->db->getTableIndex($table_name);
            $add_columns = array();
            $change_columns = array();
            $delete_columns = array();
            $delete_indexs = array();
            $delete_foreignkey = array();
            $columns_keys = array_keys($columns);
            foreach($columns as $k => $v){
                if(isset($results[$k])){
                    $change_columns[$k] = $v;
                }else{
                    $add_columns[$k] = $v;
                }
            }
            foreach($results as $k => $v){
                if(!isset($columns[$k])){
                    $delete_columns[$k] = $v;
                }
            }
            foreach($results_index as $k){
                if($k == "PRIMARY"){
                    continue;
                }
                if(preg_match('/^.+_fk$/',$k,$matchs)){
                    $delete_foreignkey[$k] = $k;
                    continue;
                }
                $delete_indexs[$k] = $k;
            }
            // update
            foreach($delete_foreignkey as $k => $v){
                try{
                    if($this->db->dropForeignKey($table_name,$k) && ($this->db->affectedRows() > 0)){
                        $change_column_num ++;
                    }
                }catch (\Exception $e){
                }
            }
            foreach($delete_columns as $k => $v){
                if($this->db->alterTableDrop($table_name,$k) && ($this->db->affectedRows() > 0)){
                    $change_column_num ++;
                }
            }
            foreach($delete_indexs as $k => $v){
                if($this->db->alterTableDropIndex($table_name,$k) && ($this->db->affectedRows() > 0)){
                    $change_column_num ++;
                }
            }
            foreach($change_columns as $k => $v){
                if($this->db->alterTableChange($table_name,$k,$v,$k) && ($this->db->affectedRows() > 0)){
                    $change_column_num ++;
                }
            }
            foreach($add_columns as $k => $v){
                $current_key = array_search($k,$columns_keys);
                if($this->db->alterTableAdd(
                        $table_name,$k,$v,($current_key > 0 ? $this->db->escapeColumn($columns_keys[$current_key - 1]) : "FIRST"))
                    &&
                    ($this->db->affectedRows() > 0)
                ){
                    $change_column_num ++;
                }
            }
            // update callback
            $this->modelUpdate();
        }else{
            if($result = $this->db->createTable($table_name,$columns,$this->table_options) && ($this->db->affectedRows() > 0)){
                $change_column_num ++;
            }
            // update callback
            $this->modelUpdate();
        }
        return $change_column_num;
    }

    /**
     * @return int
     */
    public function addReference()
    {
        $change_column_num = 0;
        $table_name = $this->getTableName();
        $columns = $this->getDBColumns();
        foreach($columns as $column){
            if($column->getReference()){
                if($this->db->addForeignKey(
                    $table_name,
                    $table_name.'_'.$column->getReference()->getName(),
                    $column->getName(),
                    $column->getReference()->getTableName(),
                    $column->getReference()->getColumn(),
                    $column->getReference()->getDelete(),
                    $column->getReference()->getUpdate()
                )){
                    $change_column_num ++;
                }
            }
        }
        return $change_column_num;
    }

    /**
     * @return ModelManager
     */
    public function getManager()
    {
        $mm = new ModelManager();
        return $mm;
    }

    /**
     * @param $name
     * @return mixed
     */
    function __get($name)
    {
        $method_name = 'get'.ucfirst($name);
        if(method_exists(get_class($this),$method_name)){
            return $this->$method_name();
        }else if(property_exists(get_class($this),$name)){
            if($this->isExists($name)){
                $column = $this->getColumn($name);
                if($this->$name instanceof Model_Query){
                    $this->$name = $this->$name->getResults();
                }else if($column->getConnection()){
                    $target_name = $column->getConnection()->getTargetName();
                    $target_column = $column->getConnection()->getTargetColumn();
                    $model = new $target_name();
                    $model->find(array($target_column => $this->$name));
                    return $model;
                }
            }
            return $this->$name;
        }
        trigger_error(sprintf('Undefined property %s:%s',get_class($this),$name));
    }
}
