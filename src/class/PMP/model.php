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
        return new ModelColumn(array("type" => "bigint","ai" => true,"null" => false,"comment" => __("primary key")));
    }

    /**
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @param $name
     * @param $field
     * @return array
     */
    /*
    private static function makeColumnToDBColumnData($name,$field){
        return array(
            "Column" => $name,
            "Type" => (isset($field["type"]) ? $field["type"] : "").(isset($field["length"]) ? "(".$field["length"].")" : "").(isset($field["attribute"]) ? " ".$field["attribute"] : ""),
            "Null" => (isset($field["null"]) && ($field["null"] == false) ? "NO" : "YES"),
            "Default" => (isset($field["default"]) ? $field["default"] : false),
            "Collation" => (isset($field["collation"]) ? $field["collation"] : false),
            "Key" => (
                (isset($field["index"]) && $field["index"]) ? "index" :
                (isset($field["unique"]) && $field["unique"]) ? "unique" :
                (isset($field["fulltext"]) && $field["fulltext"]) ? "fulltext" :
                (isset($field["primary"]) && $field["primary"]) ? "primary" :
                (isset($field["ai"]) && $field["ai"]) ? "primary" :
                false
            ),
            "Extra" => ((isset($field["ai"]) && $field["ai"]) ? "auto_increment" : false),
            "Comment" => (isset($field["comment"]) ? $field["comment"] : false),
            "Format" => (isset($field["format"]) ? $field["format"] : null),
            "Choices" => (isset($field["choices"]) ? $field["choices"] : null),
            "Form" => (isset($field["form"]) ? $field["form"] : true),
        );
    }*/

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
            $fields[$key] = $colum->getDBColumn();
        }
        return $fields;
    }

    /**
     * @return array
     */
    public function getFormColumns(){
        $columns = array();
        foreach($this->table_fields as $k => $v){
            if($v->getFormenable()){
                $columns[$k] = array(
                    "type" => self::convertColumnsToFormType($v),
                    "value" => $this->getParamater($k),
                    "attr" => self::convertColumnsToFormAttr($v),
                );
            }
        }
        return $columns;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isExists($key){
        return (isset($this->table_fields[$key]) ? true : false);
    }

    /**
     * @param $key
     * @return ModelColumn
     */
    public function get($key){
        return $this->table_fields[$key];
    }

    /**
     * @param ModelColumn $column
     * @return string
     */
    private static function convertColumnsToFormType(ModelColumn $column){
        $field = $column->getDBColumn();
        $ctype = "text";
        if($field->getAi()){
            $ctype = "hidden";
        }else{
            if($field->isInt()){
                $ctype = "select";
            }else if($field->isFloat()){
                $ctype = "text";
            }else if($field->isDate()){
                $ctype = "text";
            }else if($field->isText()){
                $ctype = "textarea";
            }else if($field->isString()){
                $ctype = "text";
            }
        }
        return $ctype;
    }

    /**
     * @param ModelColumn $column
     * @return array
     */
    private static function convertColumnsToFormAttr(ModelColumn $column)
    {
        $field = $column->getDBColumn();
        $attr = array();
        $attr["format"] = $column->getFormat();
        $attr["choices"] = $column->getChoices();
        $attr["label"] = $field->getComment();
        if($field->getLength() > 0){
            $attr["maxlength"] = $field->getLength();
        }
        if(($field->getType() == 'date') || ($field->getType() == 'datetime')){
            if($field->getNull() == false){
                $attr["required"] = true;
            }
        }
        return $attr;
    }

    /**
     * @return string
     */
    public function getTableName(){
        return get_class($this);
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
        if($first_set){
            $results = $this->findQuery($args)->getResults();
            if(count($results) > 0){
                $result = $results[0];
            }
        }else{
            $result = $this->findQuery($args)->getResult();
        }
        if(isset($result)){
            $this->setParameters($result,true,true);
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
            $vv = $this->{$k};
            if($vv instanceof Model){
                $columns[$k] = $vv->getId();
            }else if($v->getType() == ModelColumn::$TYPE_ARRAY){
                $columns[$k] = self::$DB_ARRAY_SPACER.implode(self::$DB_ARRAY_SPACER,$vv).self::$DB_ARRAY_SPACER;
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
    public function findByModel($args,$order=array(),$limit=0,$offset=0){
        $results = $this->findBy($args,$order,$limit,$offset);
        $list = null;
        if($results){
            $list = array();
            foreach($results as $key => $item){
                $class_name = get_class($this);
                $obj = new $class_name();
                $obj->setParameters($item,true,true);
                $list[$key] = $obj;
            }
        }
        return $list;
    }

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
    public function setParameter($key,$val,$method_call = true,$db_data=false){
        if(is_string($key)){
            $method = "set".ucfirst($key);
            if($method_call && method_exists($this,$method)){
                $this->$method($val);
            }else if(property_exists($this,$key)){
                if($db_data){
                    if($this->table_fields[$key]->getType() == ModelColumn::$TYPE_ARRAY){
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
                    }else if($this->table_fields[$key]->getType() == ModelColumn::$TYPE_DATA){
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
        }else{
            throw new PMPException('Model->setParameter() args.');
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
    public function setParameters($args,$method_call = true,$db_data=false)
    {
        if(is_array($args)){
            foreach($args as $k => $v){
                $this->setParameter($k,$v,$method_call,$db_data);
            }
        }else{
            throw new PMPException('Model->setParameters() args.');
        }
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getParamater($key)
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
}
