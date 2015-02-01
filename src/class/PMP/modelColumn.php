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
 * Class ModelColumn
 * @package PMP
 */
class ModelColumn{
    static $TYPE_CHAR = 'char';
    static $TYPE_VARCHAR = 'varchar';
    static $TYPE_BINARY = 'binary';
    static $TYPE_VARBINARY = 'varbinary';
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

    protected $name;
    protected $type;
    protected $length;
    protected $nullable;
    protected $default;
    protected $comment;
    protected $choices;
    protected $format;
    protected $formenable;
    protected $ai;
    protected $unique;

    protected $reference;
    protected $connection;

    /**
     * @param $field
     */
    function __construct($field){
        $this->type = null;
        $this->length = 0;
        $this->nullable = false;
        $this->formenable = true;
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
            }else if($k == 'choices'){
                $this->setChoices($v);
            }else if($k == 'connection'){
                $this->setConnection($v);
            }else{
                $trace = debug_backtrace();
                throw new \Exception('not found ModelColumn option "'.$k.'" in '.$trace[1]['class']);
            }
        }
        if(!$this->type){
            throw new \Exception('not found ModelColumn option "type" key.');
        }
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
        );
        if(in_array($type,$types)){
            $this->type = $type;
        }else{
            throw new \Exception('not found ModelColumn type key "'.$type.'"');
        }
    }

    /**
     * @param int $length
     */
    protected function setLength($length)
    {
        if(is_int($length)){
            $this->length = $length;
        }else{
            throw new \Exception('not support ModelColumn length value');
        }
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
     * @param mixed $connection
     */
    protected function setConnection($connection)
    {
        if(is_array($connection)){
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
            if(isset($this->connection['target'])){
                if(preg_match('/^(.+)\.(.+)$/',$this->connection['target'],$matchs) && ($model_name = ModelManager::find($matchs[1]))){
                    $model = new $model_name;
                    $column_name = $matchs[2];
                    if($model && $model->isExists($column_name)){
                        $column = $model->get($column_name);
                        $reference = new ModelReference($model,$column);
                        if(isset($this->connection['delete'])){
                            $reference->setDelete($this->connection['delete']);
                        }
                        if(isset($this->connection['update'])){
                            $reference->setUpdate($this->connection['update']);
                        }
                        $this->reference = $reference;
                    }else{
                        throw new \Exception('not found connection target paramater.column name is "'.$this->connection['target'].'"');
                    }
                }else{
                    throw new \Exception('not found connection target paramater.column name is "'.$this->connection['target'].'"');
                }
            }else{
                throw new \Exception('must be connection target paramater.');
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
            'default' => $this->default,
            'comment' => $this->comment,
            'ai' => $this->ai,
            'unique' => $this->unique,
            'reference' => $this->getReference(),
        );
        $db = new DatabaseColumn($dbcolumn);
        return $db;
    }
}
