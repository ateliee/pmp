<?php
namespace PMP;

class ModelReference{
    private $model;
    private $column;
    private $update;
    private $delete;

    function __construct(Model &$model,ModelColumn &$column)
    {
        $this->model = $model;
        $this->column = $column;
    }

    /**
     * @return \PMP\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return \PMP\ModelColumn
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param mixed $delete
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;
    }

    /**
     * @return mixed
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @param mixed $update
     */
    public function setUpdate($update)
    {
        $this->update = $update;
    }

    /**
     * @return mixed
     */
    public function getUpdate()
    {
        return $this->update;
    }

}

/**
 * Class ModelConnectionColumn
 * @package PMP
 */
class ModelConnectionColumn{
    private $target_name;
    private $target_column;
    private $delete;
    private $update;
    /**
     * @param $field
     */
    function __construct($field){
        foreach($field as $k => $v){
            $k = strtolower($k);
            if($k == 'target'){
                if(preg_match('/^(.+)\.(.+)$/',$v,$matchs)){
                    $this->target_name = $matchs[1];
                    $this->target_column = $matchs[2];
                }else{
                    $trace = debug_backtrace();
                    throw new \Exception('Must Be Target Column is "ClassName.ColumnName" in '.$trace[1]['class']);
                }
            }else if($k == 'delete'){
                $this->delete = $v;
            }else if($k == 'update'){
                $this->update = $v;
            }else{
                $trace = debug_backtrace();
                throw new \Exception('not found ModelConnectionColumn option "'.$k.'" in '.$trace[1]['class']);
            }
        }
        if(!$this->target_name){
            throw new \Exception('not found ModelConnectionColumn option "target" key.');
        }
    }

    /**
     * @return mixed
     */
    public function getTargetName()
    {
        return $this->target_name;
    }

    /**
     * @return mixed
     */
    public function getTargetColumn()
    {
        return $this->target_column;
    }

    /**
     * @return mixed
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @return mixed
     */
    public function getDelete()
    {
        return $this->delete;
    }

}

/**
 * Class ModelColumn
 * @package PMP
 */
class ModelColumn{
    static $TYPE_CHAR = 'char';
    static $TYPE_VARCHAR = 'varchar';
    static $TYPE_BINARY = 'binary';
    static $TYPE_VARBINARY = 'varbinary';
    static $TYPE_BOOLEAN = 'boolean';
    static $TYPE_INT = 'int';
    static $TYPE_BIGINT = 'bigint';
    static $TYPE_SMALLINT = 'smallint';
    static $TYPE_MEDIUM = 'medium';
    static $TYPE_TINYINT = 'tinyint';
    static $TYPE_FLOAT = 'float';
    static $TYPE_DOUBLE = 'double';
    static $TYPE_DATE = 'date';
    static $TYPE_DATETIME = 'datetime';
    static $TYPE_TIMESTAMP = 'timestamp';
    static $TYPE_TIME = 'time';
    static $TYPE_YEAR = 'year';
    static $TYPE_TINYBLOB = 'tinyblob';
    static $TYPE_BLOB = 'blob';
    static $TYPE_MIDIUMBLOB = 'midiumeblob';
    static $TYPE_LONGBLOB = 'longblob';
    static $TYPE_TINYTEXT = 'tinytext';
    static $TYPE_TEXT = 'text';
    static $TYPE_MIDIUMTEXT = 'midiumtext';
    static $TYPE_LONGTEXT = 'longtext';
    // original
    static $TYPE_URL = 'url';
    static $TYPE_ARRAY = 'array';
    static $TYPE_DATA = 'data';

    static $TYPE_MANY = 'Many';
    static $TYPE_ONE = 'One';

    // convert
    static $CONVERT_NUMBER = 1;
    static $CONVERT_JP = 2;
    static $CONVERT_KANA = 4;
    static $CONVERT_ALPHABET = 8;

    protected $name;
    protected $type;
    protected $length;
    protected $nullable;
    protected $default_set;
    protected $default;
    protected $comment;
    protected $choices;
    protected $format;
    protected $formenable;
    protected $ai;
    protected $unique;
    protected $target_name;
    protected $target_column;
    protected $self_column;
    protected $target_order;
    protected $target_sort;

    protected $reference;
    protected $connection;
    protected $convert;

    /**
     * @param $field
     */
    function __construct($field){
        $this->type = null;
        $this->length = 0;
        $this->nullable = false;
        $this->formenable = true;
        $this->default_set = false;
        $this->default = false;
        $this->reference = null;
        foreach($field as $k => $v){
            $k = strtolower($k);
            if($k == 'type'){
                $this->setType($v);
            }else if($k == 'length'){
                $this->setLength($v);
            }else if($k == 'null'){
                $this->setNullable($v);
            }else if($k == 'unique'){
                $this->setUnique($v);
            }else if($k == 'comment'){
                $this->setComment($v);
            }else if($k == 'name'){
                $this->setName($v);
            }else if($k == 'form'){
                $this->setFormenable($v);
            }else if($k == 'ai'){
                $this->setAi($v);
            }else if($k == 'default'){
                $this->setDefault($v);
            }else if($k == 'format'){
                $this->setFormat($v);
            }else if($k == 'convert'){
                $this->setConvert($v);
            }else if($k == 'choices'){
                $this->setChoices($v);
            }else if($k == 'connection'){
                $this->setConnection($v);
            }else if($k == 'target'){
                $this->setTarget($v);
            }else if($k == 'order'){
                $vv = explode(' ',$v);
                if(count($vv) == 2){
                    $this->target_order = $vv[0];
                    $this->target_sort = $vv[1];
                }else{
                    $this->target_order = $v;
                }
            }else if($k == 'self'){
                $this->self_column = $v;
            }else{
                $trace = debug_backtrace();
                throw new \Exception('Not Found ModelColumn Option "'.$k.'" in '.$trace[1]['class']);
            }
        }
        if(!$this->type){
            throw new \Exception('Not Found ModelColumn Option "type" Key.');
        }
        if(!$this->isDBColumn()){
            if(!$this->target_name || !$this->target_column){
                throw new \Exception('Not Found ModelColumn Option "target" Key.');
            }else{
                if(!$this->self_column){
                    $this->self_column = 'id';
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getSelfColumn()
    {
        return $this->self_column;
    }

    /**
     * @return mixed
     */
    public function getTargetOrder()
    {
        return $this->target_order;
    }

    /**
     * @return string
     */
    public function getTargetSort()
    {
        return $this->target_sort;
    }

    /**
     * @return bool
     */
    public function isDBColumn()
    {
        return !($this->isCompareColumn());
    }

    /**
     * @return bool
     */
    public function isCompareColumn()
    {
        if(in_array($this->type,array(self::$TYPE_ONE,self::$TYPE_MANY))){
            return true;
        }
        return false;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = strtolower($name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    protected function setType($type)
    {
        $types = array(
            self::$TYPE_CHAR,
            self::$TYPE_VARCHAR,
            self::$TYPE_BINARY,
            self::$TYPE_VARBINARY,
            self::$TYPE_BOOLEAN,
            self::$TYPE_INT,
            self::$TYPE_BIGINT,
            self::$TYPE_SMALLINT,
            self::$TYPE_MEDIUM,
            self::$TYPE_TINYINT,
            self::$TYPE_FLOAT,
            self::$TYPE_DOUBLE,
            self::$TYPE_DATE,
            self::$TYPE_DATETIME,
            self::$TYPE_TIMESTAMP,
            self::$TYPE_TIME,
            self::$TYPE_YEAR,
            self::$TYPE_TINYBLOB,
            self::$TYPE_BLOB,
            self::$TYPE_MIDIUMBLOB,
            self::$TYPE_LONGBLOB,
            self::$TYPE_TINYTEXT,
            self::$TYPE_TEXT,
            self::$TYPE_MIDIUMTEXT,
            self::$TYPE_LONGTEXT,
            self::$TYPE_URL,
            self::$TYPE_ARRAY,
            self::$TYPE_DATA,
            self::$TYPE_MANY,
            self::$TYPE_ONE,
        );
        if(in_array($type,$types)){
            $this->type = $type;
            if(in_array($type,array(self::$TYPE_MANY))){
                $this->formenable = false;
            }
        }else{
            throw new \Exception('Not Found ModelColumn Type Key "'.$type.'"');
        }
    }

    /**
     * @param mixed $target_column
     */
    protected function setTarget($target)
    {
        if(preg_match('/^(.+)\.(.+)$/',$target,$matchs)){
            $this->target_name = $matchs[1];
            $this->target_column = $matchs[2];
        }else{
            throw new \Exception('Not Support ModelColumn target key "'.$target.'"');
        }
    }

    /**
     * @return mixed
     */
    public function getTargetColumn()
    {
        return $this->target_column;
    }

    /**
     * @return mixed
     */
    public function getTargetName()
    {
        return $this->target_name;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    protected function setLength($length)
    {
        if(is_int($length)){
            $this->length = $length;
        }else{
            throw new \Exception('Not Support ModelColumn Length Value');
        }
    }

    /**
     * @return boolean
     */
    public function getNullable()
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     */
    protected function setNullable($nullable)
    {
        $this->nullable = (bool)($nullable);
    }

    /**
     * @param bool $unique
     */
    protected function setUnique($unique)
    {
        $this->unique = (bool)$unique;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    protected function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param bool $formenable
     */
    protected function setFormenable($formenable)
    {
        $this->formenable = (bool)$formenable;
    }

    /**
     * @return mixed
     */
    public function getFormenable()
    {
        return $this->formenable;
    }

    /**
     * @return mixed
     */
    public function getAi()
    {
        return $this->ai;
    }

    /**
     * @param mixed $ai
     */
    protected function setAi($ai)
    {
        $this->ai = $ai;
    }

    /**
     * @param mixed $default
     */
    protected function setDefault($default)
    {
        $this->default_set = true;
        $this->default = $default;
    }

    /**
     * @param mixed $format
     */
    protected function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $convert
     */
    public function setConvert($convert)
    {
        $this->convert = $convert;
    }

    /**
     * @return mixed
     */
    public function getConvert()
    {
        return $this->convert;
    }

    /**
     * @param $v
     * @return array|string
     */
    public function getConvertValue($v)
    {
        if(is_array($v)){
            foreach($v as $kk => $vv){
                $v[$kk] = $this->getConvertValue($vv);
            }
        }else{
            if($this->convert & self::$CONVERT_NUMBER){
                $v = mb_convert_kana($v, 'sn');
            }
            if($this->convert & self::$CONVERT_JP){
                $v = mb_convert_kana($v, 'Hc');
            }
            if($this->convert & self::$CONVERT_KANA){
                $v = mb_convert_kana($v, 'KCV');
            }
            if($this->convert & self::$CONVERT_ALPHABET){
                $v = mb_convert_kana($v, 'sa');
            }
        }
        return $v;
    }

    /**
     * @param mixed $choices
     */
    protected function setChoices($choices)
    {
        $this->choices = $choices;
    }

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @return ModelConnectionColumn
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    protected function setConnection($connection)
    {
        if(is_array($connection)){
            $this->connection = new ModelConnectionColumn($connection);
        }else if($connection instanceof ModelConnectionColumn){
            $this->connection = $connection;
        }else{
            throw new \Exception('not support connection value.must be array().');
        }
    }

    /**
     * @return null
     */
    protected function getReference()
    {
        if($this->connection && !$this->reference){
            $reference = null;
            // create reference
            if($model_name = ModelManager::find($this->connection->getTargetName())){
                $model = new $model_name;
                $column_name = $this->connection->getTargetColumn();
                if($model && $model->isExists($column_name)){
                    $column = $model->getColumn($column_name);
                    $reference = new ModelReference($model,$column);
                    if($this->connection->getDelete()){
                        $reference->setDelete($this->connection->getDelete());
                    }
                    if($this->connection->getUpdate()){
                        $reference->setUpdate($this->connection->getUpdate());
                    }
                    $this->reference = $reference;
                }else{
                    throw new \Exception('not found connection target paramater.column name is "'.$this->connection->getTargetName().'"');
                }
            }else{
                throw new \Exception('not found connection target paramater.column name is "'.$this->connection->getTargetName().'"');
            }

        }
        return $this->reference;
    }

    /**
     * @return DatabaseColumn
     */
    public function getDBColumn()
    {
        $type = $this->type;
        $length = $this->length;
        if($type == self::$TYPE_URL){
            $type = self::$TYPE_VARCHAR;
            $length = 250;
        }else if($type == self::$TYPE_ARRAY){
            $type = self::$TYPE_LONGTEXT;
        }else if($type == self::$TYPE_DATA){
            $type = self::$TYPE_LONGTEXT;
        }
        $dbcolumn = array(
            'field' => $this->name,
            'type' => $type,
            'length' => $length,
            'null' => $this->nullable,
            'comment' => $this->comment,
            'ai' => $this->ai,
            'unique' => $this->unique,
            'reference' => $this->getReference(),
        );
        if($this->default_set){
            $dbcolumn['default'] = $this->default;
        }
        $db = new DatabaseColumn($dbcolumn);
        return $db;
    }
}
