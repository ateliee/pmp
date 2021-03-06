<?php
namespace PMP;

class ModelValue{
    private $value;
    private $column;
    private $db_data;
    private $method_call;
    private $converted;

    function  __construct($value,ModelColumn $column,$db_data=true,$method_call=true){
        $this->value = $value;
        $this->column = $column;
        $this->db_data = $db_data;
        $this->method_call = $method_call;
        $this->converted = false;
    }

    /**
     * @return boolean
     */
    public function getMethodCall()
    {
        return $this->method_call;
    }

    /**
     * @return mixed
     */
    public function get(){
        if(!$this->converted){
            $this->value = $this->convert();
            $this->converted = true;
        }
        return $this->value;
    }

    /**
     * @return mixed
     */
    protected function convert(){
        $value = $this->value;

        if(in_array($this->column->getType(),array(ModelColumn::$TYPE_DATE))){
            $value = new \PMP\Date($value);
        }else if(in_array($this->column->getType(),array(ModelColumn::$TYPE_DATETIME,ModelColumn::$TYPE_TIMESTAMP))){
            $value = new \PMP\DateTime($value);
        }
        if($this->db_data){
            $value = $this->column->getConvertValue($value);
            if($this->column->getType() == ModelColumn::$TYPE_BOOLEAN){
                $value = intval($value);
                /*}else if($this->column->getType() == ModelColumn::$TYPE_ARRAY){
                    $v = array();
                    if($value != ''){
                        $arr = explode(Model::$DB_ARRAY_SPACER,$value);
                        array_shift($arr);
                        array_pop($arr);
                        foreach($arr as $vv){
                            $v[] = $vv;
                        }
                    }
                    $value = $v;*/
            }else if(in_array($this->column->getType(),array(ModelColumn::$TYPE_DATA,ModelColumn::$TYPE_ARRAY))){
                if($value){
                    $value = @base64_decode($value);
                    $value = @unserialize($value);
                    if(($this->column->getType() == ModelColumn::$TYPE_ARRAY) && !is_array($value)){
                        $value = null;
                    }
                }else{
                    $value = null;
                }
            }
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function __toString(){
        return $this->get();
    }
}

/**
 * Class Model
 * @package PMP
 */
class Model{
    static $DB_ARRAY_SPACER = '-';

    private $id;
    /**
     * @var Database
     */
    private $db;
    private $table_options;
    /**
     * @var ModelColumn[]
     */
    private $table_fields;
    /**
     * @var ModelIndex[]
     */
    private $table_indexs;

    static private $class_fields = array();
    static private $from_connection = array();

    function  __construct(){
        $this->db = Database::getCurrentDB();
        $this->table_options = array(
            "engine" => Database::ENGINE_DEFAULT
        );
        $parents_class = class_parents($this);
        $class_name = get_class($this);
        $class = array_merge($parents_class,array($class_name => $class_name));

        $this->table_fields = array();
        foreach($class as $class_name){
            $fields = null;
            if(isset(self::$class_fields[$class_name])){
                $fields = self::$class_fields[$class_name];
            }else{
                $fields = array();
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
                            $fields[$key] = $field;

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
                self::$class_fields[$class_name] = $fields;
            }
            if($fields){
                foreach($fields as $k => $f){
                    $this->table_fields[$k] = $f;
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
        return $this->getVal('id');
    }

    /**
     * @return ModelColumn[]
     */
    public function getColumns(){
        if(!$this->table_fields){
            throw new PMPException(sprintf('"%s" Model Columns Not Installed.',get_class($this)));
        }
        return $this->table_fields;
    }

    /**
     * @return DatabaseColumn[]
     */
    private function getDBColumns(){
        $fields = array();
        foreach($this->getColumns() as $key => $colum){
            if(!$colum->isCompareColumn()){
                $fields[$key] = $colum->getDBColumn();
            }
        }
        return $fields;
    }

    /**
     * @return DatabaseColumn[]
     */
    private function getDBReferenceColumn(){
        $results = array();
        $columns = $this->getDBColumns();
        foreach($columns as $column){
            if($column->getReference()){
                $results[$column->getReference()->getName()] = $column;
            }
        }
        return $results;
    }

    /**
     * @return array|ModelIndex[]
     */
    private function getIndexColumns(){
        if(!$this->table_indexs){
            $this->table_indexs = array();
            $v = 'getIndexs';
            if(is_callable(array($this,$v))){
                $res = $this->{$v}();
                if(!is_array($res)){
                    $res = array($res);
                }
                foreach($res as $val){
                    if($val instanceof ModelColumnIndex){
                        $this->table_indexs[] = $val;
                    }else{
                        $class_name = get_class($this);
                        throw new PMPException($class_name.'->'.$v.'() must be return ModelColumnIndex or ModelColumnIndex[]');
                    }
                }
            }
        }
        $fields = array();
        foreach($this->table_indexs as $key => $colum){
            $fields[$colum->generateName()] = $colum;
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
        return (array_key_exists($key,$this->getColumns()) ? true : false);
    }

    /**
     * @param $key
     * @return ModelColumn
     */
    public function getColumn($key){
        if(isset($this->table_fields[$key])){
            return $this->table_fields[$key];
        }
        throw new \Exception(sprintf('"%s" Column Not Found "%s".',get_class($this),$key));
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
        if($args instanceof Model){
            $args = $args->getId();
        }
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
            throw new PMPException('find() Paramater Must Be number or array.');
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
            $vv = $this->get($k);
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
            $vv = $this->get($k);
            if($vv instanceof Model){
                $columns[$k] = $vv->getId();
                /*}else if($v->getType() == ModelColumn::$TYPE_ARRAY){
                    if(is_array($vv) && (count($vv) > 0)){
                        $columns[$k] = self::$DB_ARRAY_SPACER.implode(self::$DB_ARRAY_SPACER,$tmp).self::$DB_ARRAY_SPACER;
                    }else{
                        $columns[$k] = null;
                    }*/
            }else if(in_array($v->getType(),array(ModelColumn::$TYPE_DATA,ModelColumn::$TYPE_ARRAY))){
                $columns[$k] = @serialize($vv);
                $columns[$k] = @base64_encode($columns[$k]);
            }else if(in_array($v->getType(),array(ModelColumn::$TYPE_DATE,ModelColumn::$TYPE_DATETIME))){
                $vv = (string)$vv;
                if(!$vv){
                    $vv = null;
                }
                $columns[$k] = ($vv);
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
        $column = $this->getColumn($key);
        $this->{$key} = new ModelValue($val,$column,$db_data,$method_call);
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
        $get = $this->getVal($key);
        $method = 'get'.ucfirst($key);
        if(method_exists($this,$method)){
            $get = $this->{$method}();
        }
        return $get;
    }

    /**
     * @param $key
     * @return mixed
     * @throws PMPException
     */
    protected function getVal($key){
        if(property_exists(($this),$key)){
            if($this->{$key} instanceof ModelValue){
                $val = $this->{$key};
                $method_call = $val->getMethodCall();
                $val = $val->get();

                $method = "set".ucfirst($key);
                if($method_call && method_exists($this,$method)){
                    $this->$method($val);
                }else{
                    $this->{$key} = $val;
                }
            }
            if($this->isExists($key)){
                $column = $this->getColumn($key);
                if(!($this->$key instanceof Model) && $column->getConnection()){
                    $target_name = $column->getConnection()->getTargetName();
                    $target_column = $column->getConnection()->getTargetColumn();
                    $model = new $target_name();
                    $model->find(array($target_column => $this->$key));
                    $this->$key = $model;
                    return $this->$key;
                }
                if($this->$key instanceof Model_Query){
                    $this->$key = $this->$key->getResults();
                }
            }
            /*
            $class = get_class($this);
            if(isset(self::$from_connection[$class][$key])){
                foreach(self::$from_connection[$class][$key] as $k => $vv){
                    $c = $this->getColumn($k);
                    if($c->isCompareColumn()){
                        $mm = new ModelManager();
                        $v = $mm->createQuery($c->getTargetName(),'p')
                            ->where('`p`.`'.$c->getTargetColumn().'`=:key')
                            ->setParamater('key',$this->get($key));
                        if($c->getTargetOrder()){
                            $v->order($c->getTargetOrder(),$c->getTargetSort());
                        }
                        $this->{$k} = $v;
                    }
                }
            }*/
            return $this->{$key};
        }else{
            throw new PMPException(sprintf('"%s" Model Columns Not Found "%s".',get_class($this),$key));
        }
    }

    /**
     * @return int
     */
    public function upgrade()
    {
        $change_column_num = 0;
        $table_name = $this->getTableName();

        $columns = $this->getDBColumns();
        $index_columns = $this->getIndexColumns();
        $reference_columns = $this->getDBReferenceColumn();
        if($this->db->checkTableExists($table_name)){
            $results = $this->db->getTableColumns($table_name);
            $results_index = $this->db->getTableIndex($table_name);
            //$results_foreignkey = $this->db->getTableForeignKey($table_name);
            $add_columns = array();
            $add_index_columns = array();
            $change_columns = array();
            $delete_columns = array();
            $delete_indexs = array();
            $delete_foreignkey = array();
            $columns_keys = array_keys($columns);
            foreach($columns as $k => $v){
                if(isset($results[$k])){
                    if(!$v->isEqual($results[$k])){
                        $change_columns[$k] = $v;
                    }
                }else{
                    $add_columns[$k] = $v;
                }
            }
            foreach($results as $k => $v){
                if(!isset($columns[$k])){
                    $delete_columns[$k] = $v;
                }
            }
            foreach($index_columns as $k => $v){
                if(isset($results_index[$k])){
                    //$change_columns[$k] = $v;
                }else{
                    $add_index_columns[$k] = $v;
                }
            }
            foreach($results_index as $k){
                if($k == "PRIMARY"){
                    continue;
                }
                if(preg_match('/^(.+_fk)$/',$k,$matchs)){
                    if(!preg_match('/^(.+)_(.+_fk)$/',$matchs[1],$mt) || !isset($reference_columns[$mt[2]])){
                        $delete_foreignkey[$k] = $k;
                    }
                    continue;
                }
                if(!isset($index_columns[$k])){
                    $delete_indexs[$k] = $k;
                }
            }
            // update
            foreach($delete_foreignkey as $k){
                if($this->db->dropForeignKey($table_name,$k) && ($this->db->affectedRows() > 0)){
                    $change_column_num ++;
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
            foreach($add_index_columns as $k => $v){
                if($this->db->alterTableAddIndex(
                        $table_name,$v->generateName(),$v->getFields())
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
        $reference_columns = $this->getDBReferenceColumn();
        $results_foreignkey = $this->db->getTableForeignKey($table_name);
        foreach($reference_columns as $column){
            $key_name = $table_name.'_'.$column->getReference()->getName();
            if(array_search($key_name,$results_foreignkey) !== null){
                continue;
            }
            if($this->db->addForeignKey(
                $table_name,
                $key_name,
                $column->getName(),
                $column->getReference()->getTableName(),
                $column->getReference()->getColumn(),
                $column->getReference()->getDelete(),
                $column->getReference()->getUpdate()
            )){
                $change_column_num ++;
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
     * @param $key
     * @return mixed
     */
    function __get($key)
    {
        return $this->get($key);
    }
}
