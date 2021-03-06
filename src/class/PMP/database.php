<?php
namespace PMP;

/**
 * Class DatabaseException
 * @package PMP
 */
class DatabaseException extends \Exception
{
}

/**
 * Class DatabaseReferencesColumn
 * @package PMP
 */
class DatabaseReferencesColumn
{
    static $OPTION_RESTRICT = 'RESTRICT';
    static $OPTION_CASCADE = 'CASCADE';
    static $OPTION_SETNULL = 'SET NULL';
    static $OPTION_NOACTION = 'NO ACTION';

    private $name;
    private $table_name;
    private $column;
    private $update;
    private $delete;

    function  __construct($name,ModelReference $reference)
    {
        $this->name = $name;
        $this->table_name = $reference->getModel()->getTableName();
        $this->column = $reference->getColumn()->getName();
        $this->update = $reference->getUpdate();
        $this->delete = $reference->getDelete();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @return mixed
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
 * Class DatabaseColumn
 * @package PMP
 */
class DatabaseColumn{
    private $name;
    private $type;
    private $length;
    private $default_set;
    private $default;
    private $attribute;
    private $null;
    private $key;
    private $ai;
    private $comment;
    private $options;
    private $reference;

    function  __construct($field){
        $this->default_set = false;
        $this->default = null;
        $this->options = array();
        foreach($field as $k => $v){
            $k = strtolower($k);
            if($k == "field"){
                $this->name = $v;
            }else if($k == "type"){
                if(preg_match("/^(.+)\(([0-9]+)\) (.+)$/",$v,$matchs)){
                    $this->setType($matchs[1]);
                    $this->length = $matchs[2];
                    $this->attribute = $matchs[3];
                }else if(preg_match("/^(.+)\(([0-9]+)\)$/",$v,$matchs)){
                    $this->setType($matchs[1]);
                    $this->length = $matchs[2];
                }else{
                    $this->setType($v);
                }
            }else if($k == "length"){
                $this->length = $v;
            }else if($k == "attribute"){
                $this->attribute = $v;
            }else if($k == "null"){
                if(strtolower($v) == "yes"){
                    $this->null = true;
                }else if(strtolower($v) == "no"){
                    $this->null = false;
                }else{
                    $this->null = $v ? true : false;
                }
            }else if($k == "default"){
                $this->default = $v;
            }else if($k == "collation"){
                if($v == "unique"){
                    $this->key = 'unique';
                }
            }else if($k == "key"){
                $this->key = strtolower($v);
            }else if($k == "extra"){
                if($v == "auto_increment"){
                    $this->ai = true;
                }
            }else if($k == "comment"){
                $this->comment = $v;
            }else if($k == "unique"){
                if($v){
                    $this->key = 'unique';
                }
            }else if($k == "ai"){
                $this->ai = $v;
            }else if($k == "reference"){
            }else if(in_array($k,array('privileges'))){
            }else{
                throw new DatabaseException('Database Column not support '.$k);
            }
        }
        if($this->name == ''){
            throw new DatabaseException('Database Column not select name '.implode(',',$field));
        }else if($this->type == ''){
            throw new DatabaseException('Database Column not select type'.implode(',',$field));
        }
        if(($this->default !== NULL) || $this->null){
            $this->default_set = true;
            if($this->null && ($this->default == '')){
                $this->default = NULL;
            }
        }
        if(isset($field['reference'])){
            $name = strtolower($this->name).'_fk';
            if($field['reference'] instanceof ModelReference){
                $this->reference = new DatabaseReferencesColumn($name,$field['reference']);
            }else{
                throw new DatabaseException('Database Column references not value "reference" is not array. ');
            }
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \PMP\DatabaseReferencesColumn
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getNull(){
        return $this->null;
    }

    /**
     * @return mixed
     */
    public function getLength(){
        return $this->length;
    }
    /**
     * @return mixed
     */
    public function getKey(){
        return $this->key;
    }
    /**
     * @return mixed
     */
    public function getDefault(){
        return $this->default;
    }

    /**
     * @return boolean
     */
    public function getDefaultSet()
    {
        return $this->default_set;
    }

    /**
     * @return mixed
     */
    public function getComment(){
        return $this->comment;
    }
    /**
     * @return mixed
     */
    public function getAi(){
        return $this->ai;
    }
    /**
     * @return mixed
     */
    public function getAttribute(){
        return $this->attribute;
    }

    /**
     * @return bool
     */
    public function isString(){
        return in_array($this->type,array(
            "char",
            "varchar",
            "binary",
            "varbinary"
        ));
    }

    /**
     * @return bool
     */
    public function isInt(){
        return in_array($this->type,array(
            "boolean",
            "int",
            "bigint",
            "smallint",
            "medium",
            "tinyint"
        ));
    }

    /**
     * @return bool
     */
    public function isFloat(){
        return in_array($this->type,array(
            "float",
            "double"
        ));
    }

    /**
     * @return bool
     */
    public function isDate(){
        return in_array($this->type,array(
            "date",
            "datetime",
            "timestamp",
            "time",
            "year"
        ));
    }

    /**
     * @return bool
     */
    public function isText(){
        return in_array($this->type,array(
            "tinyblob",
            "blob",
            "midiumeblob",
            "longblob",
            "tinytext",
            "text",
            "midiumtext",
            "longtext"
        ));
    }

    /**
     * @return bool
     */
    public function isUnique(){
        return ($this->key == "unique") ? true : false;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getOption($key,$default=null){
        if(isset($this->options[$key])){
            return $this->options[$key];
        }
        return $default;
    }

    /**
     * @param ModelColumn $target
     * @return bool
     */
    public function isEqual(DatabaseColumn $target){
        if($this->name != $target->getName()){
            return false;
        }
        if($this->type != $target->gettype()){
            return false;
        }
        if($this->isString()){
            if($this->length != $target->getLength()){
                return false;
            }
        }
        if($this->ai != $target->getAi()){
            return false;
        }
        if($this->null != $target->getNull()){
            return false;
        }
        if($this->default != $target->getDefault()){
            return false;
        }
        if($this->comment != $target->getComment()){
            return false;
        }
        if($this->ai != $target->getAi()){
            return false;
        }
        return true;
    }
}

/**
 * Class Database
 * support mysql
 */
# TODO : PDO MODE
class Database{
    // MySQL Mode
    const MODE_MYSQL = 1;
    const MODE_MYSQLI = 2;
    const MODE_PDO = 3;
    // Query Mode
    const QUERY_BOTH = 1;
    const QUERY_ASSOC = 2;
    const QUERY_NUM = 3;
    // Engine
    const ENGINE_DEFAULT = "DEFAULT";
    const ENGINE_MYISAM = "MyISAM";
    const ENGINE_INNODB = "InnoDB";
    // connet
    private static $currentDB;
    // setting
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $linkId;
    private $charset;
    private $result;
    private $mode;
    private $logs;

    function  __construct()
    {
        $this->host = "localhost";
        $this->linkId = NULL;
        $this->charset = "utf8";
        $this->result = NULL;
        $this->logs = array();
        $this->setMode(self::MODE_MYSQLI);

        self::setCurrentDB($this);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return mixed
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param $message
     * @throws DatabaseException
     */
    private function throwError($message){
        throw new DatabaseException('Database : '.$message);
    }

    /**
     * @return int
     */
    static function upgradeManagementModel()
    {
        $change_column_num = 0;
        foreach(ModelManager::getModels() as $k => $v){
            $model = (new $v);
            $change_column_num += $model->upgrade();
        }
        foreach(ModelManager::getModels() as $k => $v){
            $model = (new $v);
            $change_column_num += $model->addReference();
        }
        return $change_column_num;
    }

    /**
     * @param __CLASS__ $db
     */
    static function setCurrentDB(Database $db){
        self::$currentDB = $db;
    }

    /**
     * @return Database
     */
    static public function getCurrentDB(){
        return self::$currentDB;
    }

    /**
     * @param $mode
     */
    public function setMode($mode){
        $this->mode = $mode;
        if($mode == self::MODE_MYSQLI){
            if (!function_exists("mysqli_connect")) {
                $this->mode = self::MODE_MYSQL;
            }
        }else if($mode == self::MODE_MYSQLI){
            if (!class_exists("PDO")) {
                $this->mode = self::MODE_MYSQL;
            }
        }
    }

    /**
     * @param $host
     * @param $user
     * @param $password
     * @return bool
     */
    public function connect($host, $user, $password){
        $this->host = $host;
        $this->username = $user;
        $this->password = $password;

        if ($this->mode == self::MODE_MYSQL) {
            $this->linkId = @mysql_connect($this->host, $this->username, $this->password);
            if(!$this->linkId){
                $this->throwError("Connect Error. mysql_connect() ");
            }
        } else if ($this->mode == self::MODE_MYSQLI) {
            $this->linkId = @mysqli_connect($this->host, $this->username, $this->password);
            if(!$this->linkId){
                $this->throwError("Connect Error. mysqli_connect() ");
            }
        } else if ($this->mode == self::MODE_PDO) {
            $this->linkId = new PDO($this->host, $this->username, $this->password);
            if(!$this->linkId){
                $this->throwError("Connect Error. pdo() ");
            }else{
                $this->linkId->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        }else{
            $this->throwError("Connect Error.unsupport connect mode. ");
        }
        return ($this->linkId ? true : false);
    }

    /**
     * @param $dbname
     * @return bool
     */
    public function selectDB($dbname)
    {
        $this->dbName = $dbname;
        if ($this->linkId) {
            if ($this->mode == self::MODE_MYSQL && function_exists('mysql_set_charset')) {
                if (mysql_set_charset($this->charset)) {
                    mysql_select_db($this->dbName, $this->linkId);
                    return true;
                }
            } else {
                if ($this->mode == self::MODE_MYSQL) {
                    mysql_query('SET NAMES ' . $this->charset);
                    mysql_select_db($this->dbName, $this->linkId);
                    return true;
                } else if ($this->mode == self::MODE_MYSQLI) {
                    mysqli_set_charset($this->linkId, $this->charset);
                    mysqli_select_db($this->linkId, $this->dbName);
                    return true;
                } else if ($this->mode == self::MODE_PDO) {
                }
            }
        }else{
            $this->throwError("Not Connected. select database Error. ");
        }
        return false;
    }

    /**
     * close db session
     */
    public function close()
    {
        if($this->linkId){
            if ($this->mode == self::MODE_MYSQL) {
                mysql_close($this->linkId);
            } else if ($this->mode == self::MODE_MYSQLI) {
                mysqli_close($this->linkId);
            }
            $this->linkId = NULL;
        }
    }

    /**
     * @param $sql
     * @return $this
     */
    public function query($sql)
    {
        $this->result = NULL;
        if ($this->mode == self::MODE_MYSQL) {
            $this->result = mysql_query($sql, $this->linkId);
        } else if ($this->mode == self::MODE_MYSQLI) {
            $this->result = mysqli_query($this->linkId, $sql);
        } else if ($this->mode == self::MODE_PDO) {
        }
        $this->logs[] = $sql;
        if(!$this->result){
            $this->throwError("SQL Error.\nError Message --> ".$this->error()."\nSQL --> \"".$sql."\"\n");
        }
        return $this;
    }

    /**
     * @return int
     */
    public function lastId()
    {
        $sql = "SELECT LAST_INSERT_ID() AS ".$this->escapeColumn("ID").";";
        if($result = $this->query($sql)){
            $lastID = $this->fetchArray();
            if(isset($lastID["ID"])){
                return $lastID["ID"];
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    public function numRows()
    {
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_num_rows($this->result);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_num_rows($this->result);
        } else if ($this->mode == self::MODE_PDO) {
        }
        return 0;
    }

    /**
     * @return int
     */
    public function affectedRows()
    {
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_affected_rows($this->linkId);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_affected_rows($this->linkId);
        } else if ($this->mode == self::MODE_PDO) {
        }
        return 0;
    }

    /**
     * @param int $result_type
     * @return array|null
     */
    private function fetchArray($result_type = self::QUERY_ASSOC)
    {
        if ($this->numRows() > 0) {
            if ($this->mode == self::MODE_MYSQL) {
                switch ($result_type) {
                    case self::QUERY_ASSOC:
                        $type = MYSQL_ASSOC;
                        break;
                    case self::QUERY_NUM:
                        $type = MYSQL_NUM;
                        break;
                    case self::QUERY_BOTH:
                    default:
                        $type = MYSQL_BOTH;
                        break;
                }
                $results = mysql_fetch_array($this->result, $type);
            } else if ($this->mode == self::MODE_MYSQLI) {
                switch ($result_type) {
                    case self::QUERY_ASSOC:
                        $type = MYSQLI_ASSOC;
                        break;
                    case self::QUERY_NUM:
                        $type = MYSQLI_NUM;
                        break;
                    case self::QUERY_BOTH:
                    default:
                        $type = MYSQLI_BOTH;
                        break;
                }
                $results = mysqli_fetch_array($this->result, $type);
            } else if ($this->mode == self::MODE_PDO) {
                throw new DatabaseException('Un Support PDO.');
                $results = null;
            }
            if(is_array($results)){
                if($this->charset == 'utf8'){
                    foreach($results as $key => $val){
                        $results[$key] = $this->utf8mb4_decode_numericentity($val);
                    }
                }
            }
            return $results;
        }
        return NULL;
    }

    /**
     * @return null|string
     */
    private function error()
    {
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_error($this->linkId);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_error($this->linkId);
        } else if ($this->mode == self::MODE_PDO){
        }
        return null;
    }

    /**
     * @param string $name
     * @param null $alias
     * @return SQL_Query
     */
    public function createQuery($name="",$alias=null){
        $query = (new SQL_Query($this));
        if($name != ""){
            $query->select($name,$alias);
        }
        return $query;
    }

    /**
     * @param $value
     * @param $prex
     * @param $replace
     * @return mixed
     */
    public function replacePrex($value,$prex,$replace)
    {
        if(!$prex){
            return $value;
        }
        $result = array();
        foreach($value as $key => $val){
            $k = preg_replace('/^'.$prex.'/',$replace,$key);
            $result[$k] = $val;
        }
        return $replace;
    }

    /**
     * @param null $prex
     * @param null $replace
     * @return array|null
     */
    public function getResults($prex=null,$replace=null){
        $list = null;
        if($this->result){
            $list = array();
            while ($value = $this->fetchArray(self::QUERY_ASSOC)) {
                $list[] = $this->replacePrex($value,$prex,$replace);
            }
        }
        return $list;
    }

    /**
     * @param null $prex
     * @param null $replace
     * @return array|null
     */
    public function getFirstResult($prex=null,$replace=null){
        if($this->result){
            if($this->numRows() > 0){
                $value = $this->fetchArray(self::QUERY_ASSOC);
                return $this->replacePrex($value,$prex,$replace);
            }
        }
        return null;
    }

    /**
     * @param null $prex
     * @param null $replace
     * @return array|null
     */
    public function getResult($prex=null,$replace=null){
        if($this->result){
            if($this->numRows() > 1){
                $this->throwError("getResult() Error. result num not one.");
            }
            $value = $this->fetchArray(self::QUERY_ASSOC);
            return $this->replacePrex($value,$prex,$replace);
        }
        return null;
    }

    /**
     * @param null $default
     * @return mixed|null
     */
    public function getResultOne($default=null)
    {
        if($this->result){
            if($this->numRows() > 1){
                $this->throwError("getResult() Error. result num not one.");
            }
            while($value = $this->fetchArray(self::QUERY_ASSOC)){
                $val = $default;
                foreach($value as $k => $v){
                    $val = $v;
                    break;
                }
                return $val;
            }
        }
        return $default;
    }

    /**
     * @param $offset
     * @return bool|null
     */
    public function seek($offset){
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_data_seek($this->linkId,$offset);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_data_seek($this->linkId,$offset);
        } else if ($this->mode == self::MODE_PDO){
        }
        return false;
    }
    /**
     * @param $str
     * @return string
     */
    public function escape($str)
    {
        if($this->charset == 'utf8'){
            $str = $this->utf8mb4_encode_numericentity($str);
        }
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_real_escape_string($str,$this->linkId);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_real_escape_string($this->linkId,$str);
        } else if ($this->mode == self::MODE_PDO) {
        }else{
            $this->throwError("escape() error.escape(".$str.") ");
        }
    }

    /**
     * @param $str
     * @return string
     */
    public function escapeColumn($str)
    {
        return "`".$str."`";
    }

    /**
     * @param $str
     * @return mixed
     */
    private function utf8mb4_encode_numericentity($str)
    {
        $re = '/[^\x{0}-\x{FFFF}]/u';
        return preg_replace_callback($re, function($m) {
            $char = $m[0];
            $x = ord($char[0]);
            $y = ord($char[1]);
            $z = ord($char[2]);
            $w = ord($char[3]);
            $cp = (($x & 0x7) << 18) | (($y & 0x3F) << 12) | (($z & 0x3F) << 6) | ($w & 0x3F);
            return sprintf("&#x%X;", $cp);
        }, $str);
    }

    /**
     * @param $str
     * @return mixed
     */
    private function utf8mb4_decode_numericentity($str)
    {
        $re = '/&#(x[0-9a-fA-F]{5,6}|\d{5,7});/';
        return preg_replace_callback($re, function($m) {
            return html_entity_decode($m[0]);
        }, $str);
    }

    /**
     * @param $str
     * @return string
     */
    private function convertColumn($val,DatabaseColumn $column){
        $result = $val;
        if($result){
            if($column->isString()){
                if($column->getLength() > 0){
                    $result = mb_substr($result,0,$column->getLength());
                }
            }
        }
        if(!$result && ($column->getDefaultSet())){
            $result = $column->getDefault();
        }
        if($result !== NULL){
            if($column->isString() || $column->isText() || $column->isDate()){
                return "'".$this->escape($result)."'";
            }else{
                if($column->isInt()){
                    $result = intval($result);
                }
                return $this->escape($result);
            }
        }
        return "NULL";
    }

    /**
     * @return $this
     */
    private function refresh()
    {
        if ($this->mode == self::MODE_MYSQL) {
            mysql_data_seek($this->result, 0);
        } else if ($this->mode == self::MODE_MYSQLI) {
            mysqli_data_seek($this->result, 0);
        } else if ($this->mode == self::MODE_PDO) {
        }
        return $this;
    }

    /**
     * @return int|null
     */
    private function fieldsCount()
    {
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_num_fields($this->linkId);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_field_count($this->linkId);
        } else if ($this->mode == self::MODE_PDO) {
        }
        return null;
    }

    /**
     * @param $result
     * @param $num
     * @return bool|null|object|string
     */
    private function fieldName($result, $num)
    {
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_field_name($result, $num);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_fetch_field_direct($result, $num);
        } else if ($this->mode == self::MODE_PDO) {
        }
        return null;
    }

    /**
     * @param $result
     * @param $num
     * @return bool|null|object|string
     */
    private function fieldType($result, $num)
    {
        if ($this->mode == self::MODE_MYSQL) {
            return mysql_field_type($result, $num);
        } else if ($this->mode == self::MODE_MYSQLI) {
            return mysqli_fetch_field_direct($result, $num);
        } else if ($this->mode == self::MODE_PDO) {
        }
        return null;
    }

    /**
     * @param $db_name
     * @return bool
     */
    public function createDB($db_name){
        // for PHP4
        if ($this->mode == self::MODE_MYSQL && function_exists('mysql_create_db')) {
            if (mysql_create_db($db_name, $this->linkId)) {
                return true;
            }
        } else {
            $sql = "CREATE DATABASE ".$this->escapeColumn(":DB").";";
            $query = $this->createQuery()->queryExecute($sql,array("DB" => $db_name));
            return ($query->getResult()) ? true : false;
        }
        return false;
    }

    /**
     * @param $db_name
     * @return bool
     */
    public function dropDB($db_name){
        // for PHP4
        if ($this->mode == self::MODE_MYSQL && function_exists('mysql_drop_db')) {
            if (mysql_drop_db($db_name, $this->linkId)) {
                return true;
            }
        } else {
            $sql = "DROP DATABASE ".$this->escapeColumn(":DB").";";
            $query = $this->createQuery()->queryExecute($sql,array("DB" => $db_name));
            return ($query->getResult()) ? true : false;
        }
        return false;
    }

    /**
     * @param $table_name
     * @return bool
     */
    public function checkTableExists($table_name){
        $sql = "SHOW TABLES FROM ".$this->escapeColumn(":DBNAME")." LIKE ':TABLE';";
        $query = $this->createQuery()->queryExecute($sql,array(
            "DBNAME" => $this->dbName,
            "TABLE" => $table_name
        ));
        return ($query->getResult()) ? true : false;
    }

    /**
     * @param $table_name
     * @return bool
     */
    public function getTableColumns($table_name){
        $sql = "SHOW FULL COLUMNS FROM ".$this->escapeColumn(":TABLE").";";
        $query = $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name
        ));
        $results = $query->getResults();
        if($results){
            $list = array();
            foreach($results as $value){
                $key = $value["Field"];
                if(isset($value["Key"])){
                    if($value["Key"] == "PRI"){
                        $value["Key"] = "primary";
                    }else if($value["Key"] == "UNI"){
                        $value["Key"] = "unique";
                    }
                }
                if(isset($value["Default"])){
                    if($value["Default"] == ""){
                        $value["Default"] = NULL;
                    }
                }
                $list[$key] = new DatabaseColumn($value);
            }
            return $list;
        }
        return false;
    }

    /**
     * @param $table_name
     * @return bool
     */
    public function getTableIndex($table_name){
        $sql = "SHOW INDEX FROM ".$this->escapeColumn(":TABLE").";";
        $query = $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name
        ));
        $results = $query->getResults();
        if($results){
            $list = array();
            foreach($results as $value){
                if(isset($value["Key_name"])){
                    if($value["Non_unique"] == 0){
                        continue;
                    }
                    $key = $value["Key_name"];
                    $list[$key] = $key;
                }
            }
            return $list;
        }
        return false;
    }

    /**
     * @param $table_name
     * @return array|bool
     */
    public function getTableForeignKey($table_name){
        $sql = "SHOW CREATE TABLE ".$this->escapeColumn(":TABLE").";";
        $query = $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name
        ));
        $result = $query->getResult();
        if($result && isset($result['Create Table'])){

            if(preg_match_all('/CONSTRAINT\s`(.+?)`/',$result['Create Table'],$matchs)){
                return $matchs[1];
            }
            return array();
        }
        return false;
    }

    /**
     * @param $table_name
     * @param $columns
     * @param array $table_options
     * @return $this
     */
    public function createTable($table_name,$columns,$table_options = array()){
        // create table
        $columns_list = array();
        foreach($columns as $key => $val){
            $columns_list[] = "`".$key."` ".$this->makeColumnsQuery($val);
        }
        $primary_keys = $this->getPrimaryKey($columns);
        $sql = "";
        $sql .= "CREATE TABLE IF NOT EXISTS ".$this->escapeColumn(":TABLE")." ( ";
        $sql .= implode(",\n",$columns_list);
        if(count($primary_keys) > 0){
            $sql .= ",PRIMARY KEY(".implode(",",$primary_keys).") ";
        }
        $sql .= ") ";
        $opts = array();
        foreach($table_options as $k => $v){
            if($k == "engine"){
                if($v != self::ENGINE_DEFAULT){
                    $opts[] = "ENGINE ".$v;
                }
            }else if($k == "charset"){
                $opts[] = "DEFAULT CHARSET=".$v;
            }
        }
        $sql .= implode(" ",$opts);

        return $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name
        ));
    }

    /**
     * @param $table_name
     * @param $old_name
     * @return $this
     */
    private function rename($table_name,$old_name){
        $sql = "ALTER TABLE ".$this->escapeColumn(":TABLE")." RENAME TO :OLD ";
        return $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name,
            "OLD" => $old_name
        ));
    }

    /**
     * @param $action
     * @param $table_name
     * @param $name
     * @param array $options
     * @param string $after
     * @return Database
     */
    private function alterTable($action,$table_name,$name,$options=array(),$after="")
    {
        $columns = (count($options) > 0) ? $this->makeColumnsQuery($options) : "";
        $sql = "ALTER TABLE ".$this->escapeColumn(":TABLE")." ".$action." ".$this->escapeColumn(":FIELD")." ".$columns.($after != "" ? " ".$after : "");
        return $this->createQuery()
            ->queryExecute($sql,array(
                "TABLE" => $table_name,
                "FIELD" => $name
            ));
    }

    /**
     * @param $table_name
     * @param $name
     * @param $column_name
     * @param $target_table
     * @param $target_name
     * @param null $ondelete
     * @param null $onupdate
     * @return Database
     */
    public function addForeignKey($table_name,$name,$column_name,$target_table,$target_name,$ondelete=null,$onupdate=null)
    {
        $sql = sprintf(
            "ALTER TABLE %s ADD %s FOREIGN KEY (%s) REFERENCES %s(%s) %s %s ",
            $this->escapeColumn(":TABLE"),
            ($name ? "CONSTRAINT ".$this->escapeColumn($name) : ""),
            $this->escapeColumn($column_name),
            $this->escapeColumn($target_table),
            $this->escapeColumn($target_name),
            ($ondelete ? "ON DELETE ".$ondelete : ""),
            ($onupdate ? "ON UPDATE ".$onupdate : "")
        );
        return $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name
        ));
    }

    /**
     * @param $table_name
     * @param $name
     * @return Database
     */
    public function dropForeignKey($table_name,$name)
    {
        $sql = sprintf("ALTER TABLE %s DROP FOREIGN KEY %s",$this->escapeColumn(":TABLE"),$this->escapeColumn(":NAME"));
        return $this->createQuery()->queryExecute($sql,array(
            "TABLE" => $table_name,
            "NAME" => $name
        ));
    }

    /**
     * @param $table_name
     * @param $fields
     * @param $columns
     * @return Database|null
     */
    public function insert($table_name,$fields,$columns){
        if(count($fields) > 0){
            $i = 0;
            $sql_keys = array();
            $sql_vals = array();
            $param = array("TABLE" => $this->escapeColumn($table_name));
            foreach($fields as $k => $v){
                if(!isset($columns[$k])){
                    throw new DatabaseException(sprintf('insert() Not Found Column %s',$k));
                }
                $f = $columns[$k];
                if(!$v && ($f->getAi() || $f->getDefault())){
                    continue;
                }
                $key = "TABKE_KEY_".$i;
                $sql_keys[] = $this->escapeColumn(":".$key);
                $param[$key] = $k;
                $sql_vals[] = $this->convertColumn($v,$f);
                $i ++;
            }
            $sql = "INSERT INTO :TABLE (".implode(",",$sql_keys).") VALUES (".implode(",",$sql_vals).");";
            return $this->createQuery()
                ->queryExecute($sql,$param);
        }
        return null;
    }

    /**
     * @param $table_name
     * @param $fields
     */
    /*
    public function find($table_name,$fields,$wheres,$order=array(),$limit=0,$offset=0){
        $select_fields = array();
        $order_fields = array();
        $params = array();
        foreach($fields as $k => $v){
            $select_fields[$k] = "`".$k."`";
        }
        if(count($order) > 0){
            foreach($order as $k => $v){
                if(isset($select_fields[$k])){
                    $key = $select_fields[$k];
                    $order_fields[$k] = $key." ".$v;
                }
            }
        }
        $params["TABLE"] = $table_name;

        $sql = "SELECT SQL_CALC_FOUND_ROWS ".implode(",",$select_fields)." ";
        $sql .= "FROM `:TABLE` ";
        if(count($wheres) > 0){
            $w = array();
            foreach($wheres as $k => $v){
                $key = "WHERE-".$k;
                $w[] = "`".$k."`=:".$key."";
                $params[$key] = $v;
            }
            if(count($w) > 0){
                $sql .= "WHERE ".implode(" AND ",$w)." ";
            }
        }
        if(count($order_fields) > 0){
            $sql .= "ORDER BY ".implode(",",$order_fields)." ";
        }
        if($limit > 0){
            $sql .= "LIMIT ".$offset.",".$limit." ";
        }
        $sql .= ";";
        return $this->createQuery()->queryExecute($sql,$params)->getResults();
    }*/

    /**
     * @param $table_name
     * @param $fields
     * @return null
     *//*
    public function findByOne($table_name,$fields,$wheres){
        $res = $this->find($table_name,$fields,$wheres,array(),1,0);
        if($res){
            return $res[0];
        }
        return null;
    }*/

    /**
     * @return int
     */
    public function findRow(){
        $result = $this->createQuery()
            ->queryExecute("SELECT FOUND_ROWS() AS ".$this->escapeColumn("ROWS").";")
            ->getResult();
        if($result){
            return $result["ROWS"];
        }
        return 0;
    }

    /**
     * @param $wheres
     * @return string
     */
    protected function makeWhere($wheres){
        $sql = "";
        if(count($wheres) > 0){
            $w = array();
            $params = array();
            foreach($wheres as $k => $v){
                $key = "WHERE-".$k;
                $w[] = $this->escapeColumn($k)."=:".$key."";
                $params[$key] = $v;
            }
            if(count($w) > 0){
                $sql = $this->createQuery()->formatSQL(implode(" AND ",$w)." ",$params);
            }
        }
        return $sql;
    }

    /**
     * @param $table_name
     * @param $fields
     * @param $columns
     * @param $wheres
     * @return null
     */
    public function update($table_name,$fields,$columns,$wheres){
        if(count($fields) > 0){
            $sql_vals = array();
            $params = array("TABLE" => $table_name);
            foreach($fields as $k => $v){
                //if(!$v && !$columns[$k]->getAi() && !$columns[$k]->getDefault()){
                //continue;
                //}
                $sql_vals[] = $this->escapeColumn($k)."=".$this->convertColumn($v,$columns[$k])."";
            }
            $sql = "UPDATE ".$this->escapeColumn(":TABLE")." SET ".implode(",",$sql_vals)." ";
            $sql .= "WHERE ".$this->makeWhere($wheres).";";
            return $this->createQuery()->queryExecute($sql,$params);
        }
        return null;
    }

    /**
     * @param $table_name
     * @param $wheres
     * @return Database|null
     */
    public function delete($table_name,$wheres){
        $sql_vals = array();
        $params = array("TABLE" => $table_name);

        $query = $this->createQuery()->delete(":TABLE");
        if(count($wheres) > 0){
            $query = $query->where($this->makeWhere($wheres));
        }
        return $query->execute($params);
    }

    /**
     * @param $table_name
     * @param $name
     * @param $options
     * @param string $after
     * @return Database
     */
    public function alterTableAdd($table_name,$name,$options,$after=""){
        return $this->alterTable("ADD",$table_name,$name,$options);
    }

    /**
     * @param $table_name
     * @param $columns
     * @return mixed
     */
    public function alterTableAddPrimaryKey($table_name,$columns){
        return $this->createQuery()->queryExecute(
            "ALTER TABLE :TABLE ADD PRIMARY KEY (".implode(",",$columns).");",
            array("TABLE" => $table_name)
        );
    }

    /**
     * @param $table_name
     * @param $name
     * @param $options
     * @return mixed
     */
    public function alterTableChange($table_name,$name,$options,$old_name = false)
    {
        $old_name = (!$old_name) ? $old_name : $name;
        $action = ($old_name != $name) ? "CHANGE COLUMN ".$old_name : "MODIFY COLUMN";
        return $this->alterTable($action,$table_name,$name,$options);
    }

    /**
     * @param $table_name
     * @param $name
     * @return mixed
     */
    public function alterTableDrop($table_name,$name){
        return $this->alterTable("DROP",$table_name,$name);
    }

    /**
     * @param $table_name
     * @param $name
     * @param $columns
     * @return Database
     */
    public function alterTableAddIndex($table_name,$name,$columns){
        $clms = array();
        foreach($columns as $c){
            $clms[] = $this->escapeColumn($c);
        }
        return $this->alterTable("ADD INDEX",$table_name,$name,null,'('.implode(',',$clms).')');
    }

    /**
     * @param $table_name
     * @param $name
     * @return mixed
     */
    public function alterTableDropIndex($table_name,$name){
        return $this->alterTable("DROP INDEX",$table_name,$name);
    }

    /**
     * @param $name
     * @param array $fields
     * @return string
     */
    private function makeColumnsQuery(DatabaseColumn $field){
        if(!($field instanceof DatabaseColumn)){
            $this->throwError("CreateTable Columns Error.");
        }
        $attr = array();
        if($field->getAttribute() == "unsigned"){
            $attr[] = "UNSIGNED";
        }else if($field->getAttribute() == "zerofill"){
            $attr[] = "ZEROFILL";
        }
        if($field->getNull()){
            $attr[] = "NULL";
        }else{
            $attr[] = "NOT NULL";
        }
        if($field->getAi()){
            $attr[] = "AUTO_INCREMENT";
        }
        if($field->getDefaultSet()){
            if(($field->getDefault() === NULL) && $field->getNull()){
                $attr[] = "DEFAULT NULL";
            }else if($field->isDate() || $field->isString() || $field->isText()){
                $attr[] = "DEFAULT '".$field->getDefault()."'";
            }else if($field->isInt()){
                $attr[] = "DEFAULT ".intval($field->getDefault())."";
            }else{
                $attr[] = "DEFAULT ".$field->getDefault()."";
            }
        }else if($field->getNull()){
            $attr[] = "DEFAULT NULL";
        }
        if($field->getKey() == "unique"){
            $attr[] = "UNIQUE";
        }
        if($field->getComment()){
            $attr[] = "COMMENT '".$field->getComment()."'";
        }

        $sql = "";
        $sql .= $field->getType();
        if($field->getLength()){
            $sql .= "(".$field->getLength().") ";
        }
        $sql .= " ".implode(" ",$attr);
        return $sql;
    }

    /**
     * @param $columns
     * @return array
     */
    private function getPrimaryKey($columns){
        $list = array();
        foreach($columns as $k => $v){
            if($v->getAi()){
                $list[$k] = $k;
            }
        }
        return $list;
    }

    /**
     * @return null
     */
    public function getDefaultEngine(){
        $result = $this->createQuery()->queryExecute("SHOW ENGINES;")->getResults();
        foreach($result as $key => $val){
            if(strtoupper($val["Support"]) == "DEFAULT"){
                return $val["Engine"];
            }
        }
        return null;
    }

    /**
     * @param $name
     */
    public function getModel($name){
        if($name != __CLASS__){
            $m = (new $name);
            if($m instanceof Model){
                $m->setDatabase($this);
                return $m;
            }else{
                $this->throwError("getMode() argment is not Model.");
            }
        }else{
            $this->throwError("getMode() argment is not Model.");
        }
    }
}

/**
 * Class SQL_QueryBase
 */
class SQL_QueryBase
{
    protected $table_name;
    protected $table_alias;
    protected $where;

    /**
     * @param $table_name
     * @param null $alias
     */
    protected function setTableName($table_name,$alias=null)
    {
        $this->table_name = $table_name;
        $this->table_alias = $alias;
        $this->where = null;
    }

    /**
     * @return $this
     */
    public function where()
    {
        $this->where = $this->convertArgsSQL(func_get_args());
        return $this;
    }

    /**
     * @return $this
     */
    public function orWhere()
    {
        $where = $this->convertArgsSQL(func_get_args());
        $this->where .= ($this->where != "" ? " OR " : "").$where;
        return $this;
    }

    /**
     * @return $this
     */
    public function andWhere()
    {
        $where = $this->convertArgsSQL(func_get_args());
        $this->where .= ($this->where != "" ? " AND " : "").$where;
        return $this;
    }

    /**
     * @param $args
     * @return mixed
     * @throws DatabaseException
     */
    protected function convertArgsSQL($args)
    {
        if(count($args) > 1){
            return call_user_func_array("sprintf", $args);
        }else if(count($args) > 0 && is_string($args[0])){
            return $args[0];
        }else{
            throw new DatabaseException('error args parse');
        }
    }

}

/**
 * Class SQL_SubQuery
 */
class SQL_SubQuery extends SQL_QueryBase
{
    protected $join_mode;

    function __construct($join_mode)
    {
        $this->join_mode = $join_mode;
    }

    /**
     * @return string
     */
    public function getSubQuery()
    {
        $sql = $this->join_mode.' '.$this->table_name;
        if($this->table_alias){
            $sql .= ' AS '.$this->table_alias;
        }
        if($this->where){
            $sql .= ' ON '.$this->where;
        }
        return $sql;
    }
}

/**
 * Class SQL_Query
 */
class SQL_Query extends SQL_QueryBase
{
    const MODE_SELECT = 1;
    const MODE_DELETE = 3;

    private $mode;
    private $action;
    private $database;
    /**
     * @var SQL_SubQuery[]
     */
    private $subquerys;
    private $find;
    private $order;
    private $group;
    private $start;
    private $limit;

    function  __construct(Database &$database){
        $this->database = $database;
        $this->resetConfig();
    }

    /**
     *
     */
    private function resetConfig()
    {
        $this->find = "";
        $this->subquerys = array();
        $this->where = "";
        $this->order = "";
        $this->group = "";
        $this->start = 0;
        $this->limit = 0;
    }

    /**
     * @param $table_name
     * @param null $alias
     * @return $this
     */
    public function select($table_name,$alias=null)
    {
        $this->mode = self::MODE_SELECT;
        $this->action = "SELECT";
        $this->setTableName($table_name,$alias);
        return $this;
    }

    /**
     * @param $action
     * @return $this
     */
    public function delete($table_name)
    {
        $this->mode = self::MODE_DELETE;
        $this->action = "DELETE";
        $this->setTableName($table_name);
        return $this;
    }

    /**
     * @param $table_name
     * @param $where
     * @return $this
     */
    public function leftJoin($table_name,$where)
    {
        $q = new SQL_SubQuery("LEFT JOIN");
        $q->setTableName($table_name);
        $q->where($where);
        $this->subquerys[] = $q;
        return $this;
    }

    /**
     * @param $table_name
     * @param $where
     * @return $this
     */
    public function rightJoin($table_name,$where)
    {
        $q = new SQL_SubQuery("RIGHT JOIN");
        $q->setTableName($table_name);
        $q->where($where);
        $this->subquerys[] = $q;
        return $this;
    }

    /**
     * @param $table_name
     * @param $where
     * @return $this
     */
    public function innerJoin($table_name,$where)
    {
        $q = new SQL_SubQuery("RIGHT JOIN");
        $q->setTableName($table_name);
        $q->where($where);
        $this->subquerys[] = $q;
        return $this;
    }

    /**
     * @param $fields
     * @return $this
     * @throws PMPException
     */
    public function find($fields)
    {
        if(is_string($fields)){
            $this->find = $fields;
        }else if(is_array($fields)){
            $find = array();
            foreach($fields as $k => $v){
                //$find[] = $this->database->escapeColumn($v);
                $find[] = $v;
            }
            $this->find = implode(",",$find);
        }else{
            throw new DatabaseException(__FUNCTION__."() error argment.");
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function order()
    {
        $order = $this->convertArgsSQL(func_get_args());
        $this->order = $order;
        return $this;
    }

    /**
     * @return $this
     */
    public function group()
    {
        $group = $this->convertArgsSQL(func_get_args());
        $this->group = $group;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function limit()
    {
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
     * @return string
     */
    public function getSQL($param=array()){
        $sql = "";
        if($this->mode == self::MODE_SELECT){
            $sql = $this->action." SQL_CALC_FOUND_ROWS";
            if($this->find != ""){
                $sql .= " ".$this->find;
            }else{
                $sql .= " *";
            }
            $sql .= " FROM ".$this->database->escapeColumn($this->table_name)." ";
            if($this->table_alias){
                $sql .= "AS `".$this->table_alias."` ";
            }
            foreach($this->subquerys as $q){
                $sql .= $q->getSubQuery().' ';
            }
            if($this->where != ""){
                $sql .= " WHERE (".$this->where.")";
            }
            if($this->group != ""){
                $sql .= " GROUP BY ".$this->group."";
            }
            if($this->order != ""){
                $sql .= " ORDER BY ".$this->order."";
            }
            if($this->start > 0 || $this->limit){
                $sql .= " LIMIT ".$this->start.",".$this->limit.";";
            }
        }else if($this->mode == self::MODE_DELETE){
            $sql = $this->action." FROM ".$this->database->escapeColumn($this->table_name)."";
            if($this->where != ""){
                $sql .= " WHERE ".$this->where;
            }
            $sql .= ";";
        }
        if(count($param) > 0){
            $sql = $this->formatSQL($sql,$param);
        }
        return $sql;
    }

    /**
     * @param $sql
     * @param array $param
     * @return mixed
     */
    public function formatSQL($sql,$param=array()){
        if(count($param) > 0){
            $keys = array_keys($param);
            usort($keys, create_function('$a,$b', 'return strlen($b) - strlen($a);'));
            foreach($keys as $key){
                $val = $param[$key];

                if(is_null($val)){
                    $val = "NULL";
                }
                $sql = preg_replace("/:".preg_quote($key,"/")."/",$this->database->escape($val),$sql);
            }
        }
        return $sql;
    }

    /**
     * @return mixed
     * @throws PMPException
     */
    public function escapeSQL(){
        if(func_num_args() > 0){
            $sql = func_get_arg(0);
            $replaces = array();
            $args = array();
            foreach(func_get_args() as $k => $v){
                if($k == 0){
                    continue;
                }
                $replaces[] = ":".$k;
                $args[] = $this->database->escape($v);
            }
            return str_replace($replaces,$args,$sql);
        }else{
            throw new DatabaseException(__FUNCTION__."() error argment.");
        }
    }

    /**
     * @param $sql
     * @param array $param
     * @return Database
     */
    public function queryExecute($sql,$param=array())
    {
        return $this->database->query($this->formatSQL($sql,$param));
    }

    /**
     * @param array $param
     * @return Database
     */
    public function execute($param=array())
    {
        return $this->queryExecute($this->getSQL($param));
    }
}
