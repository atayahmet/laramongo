<?php

namespace Laramongo\Laramongo;

use Laramongo\Mongocore\Basemongo;
use Laramongo\ResultActions\ResultAction;
use Laramongo\Laramongo\QueryBuilder;
use Laramongo\Config\Config;

class Eloquent implements EloquentInterface {

    protected static $Instance;
    protected static $_client;
    protected static $_collection;
    protected static $_mongoId;
    protected static $mongoId;
    protected static $dbData = array();
    protected static $findResult;
    protected static $selectedDb;
    protected static $on = false;

    protected static $query;
    protected static $resultAction;
    protected static $config;

    public static $dbname = 'l5blog';

    protected static $where = array();
    protected static $limit;

    public function __construct()
    {
        self::$resultAction = new ResultAction;
        self::$query = new QueryBuilder();
        self::$config = new Config;
    }

    public static function save()
    {
        if(!is_array(self::$findResult)){
            return self::insert();
        }

        return self::update();
    }

    public static function lastId()
    {
        return self::$mongoId;
    }

    private static function _query($fail = false, $method = 'find', $setData = false)
    {
        self::createInstances();

        self::$findResult = self::$_collection->$method(self::$where);

        if(is_array(self::$findResult) || (!is_null(self::$findResult)) && self::$findResult->count() > 0) {
            if(is_numeric(self::$limit)){
                if(is_object(self::$findResult)) {
                    self::$findResult = self::$findResult->limit (self::$limit);
                }

                if($setData && self::$limit == 1) {
                    self::setData(self::$findResult);
                }
            }

            self::checkSetOn();

            return clone self::getInstance();
        }

        elseif($fail){
            exit('not found');
        }
    }

    private static function checkSetOn()
    {
        if(self::$on === true){
            self::$_client = false;
        }
    }

    private static function resetSqlVars()
    {
        self::$where = array();
        self::$limit = null;
        self::$dbData = array();
    }

    public static function find($id = false)
    {
        self::$limit = 1;

        return self::_find(false, $id, 'find');
    }

    public static function findOrFail($id = false)
    {
        self::$limit = 1;

        return self::_find(true, $id, 'find');
    }

    public static function firstOrFail()
    {
        self::$limit = 1;

        return self::lastOpr(self::_find(true, false, 'find'));
    }

    public static function first()
    {
        self::$limit = 1;

        return self::lastOpr(self::_find(false, false, 'find'));
    }

    public static function take($limit)
    {
        self::$limit = $limit;

        return self::getInstance();
    }

    public static function whereRaw($raw)
    {
        self::$where = $raw;

        return self::getInstance();
    }

    private static function lastOpr($result)
    {
        self::resetSqlVars();

        return $result;
    }

    private static function _find($fail, $id, $method = 'find')
    {
        if($id){
            self::createInstances();
            self::where(array(self::checkPrimaryKey() => $id));

            return self::_query($fail, 'findOne', true);
        }

        elseif(count(self::$where) > 0){
            return self::_query($fail, $method, true);
        }else{
            exit('not found');
        }

        return false;
    }

    public static function update($data = false)
    {
        try {
            if(is_array($data)){
                self::setData($data);
            }

            self::checkFillable();
            self::checkGuarded();
            self::checkTimestamps('updated');

            unset(self::$dbData['_id']);

            $result = self::$_collection->update(self::$where, self::$dbData);

            return self::checkResult($result);
        }

        catch(Catcher $e){
            //Catcher::fire($e);
        }

    }

    public static function delete()
    {
        $softResult = self::checkSoftDelete();

        if($softResult === false) {
            return self::checkResult(self::_delete());
        }

        return $softResult;
    }

    private static function _delete()
    {
        return self::lastOpr(self::$_collection->remove (self::$where));
    }

    public static function destroy($args)
    {
        $arg = func_get_args();

        if(count($arg) == 1 && is_array($arg[0])){
            $ids = $arg[0];
        }

        elseif(count($arg) == 1 && !is_array($arg[0])){
            $ids = array($arg[0]);
        }
        else{
            $ids = $arg;
        }

        self::createInstances();

        $result = array();

        foreach($ids as $id) {
            self::where (array(self::checkPrimaryKey () => $id));

            $result = self::checkResult(self::_delete());
        }

        return $result;
    }

    private static function checkResult($result)
    {
        if(isset($result) && is_array($result) && is_null($result['errmsg'])){
            return true;
        }

        return false;
    }

    public static function checkSoftDelete()
    {
        if(isset(self::getInstance()->dates) && is_array(self::getInstance()->dates)){
            $dateKey = array_search('deleted_at',self::getInstance()->dates);

            if($dateKey !== false) {
                $date = self::getInstance ()->dates[$dateKey];

                self::$dbData[$date] = date('Y-m-d H:i:s',time());

                return self::update();
            }
        }

        return false;
    }

    public static function insert($data = false)
    {
        try {
            if (is_array ($data)) {
                self::setData ($data);
            }

            if(count(self::$dbData) > 0) {
                self::createInstances();
                self::checkFillable();
                self::checkGuarded();
                self::checkTimestamps('created');

                unset(self::$dbData['_id']);

                $data = self::$dbData;
                $result = self::$_collection->insert($data);

                self::setInteractionResults($result, $data['_id'], 'insert');

                return self::lastId() ? true : false;
            }
        }

        catch(Catcher $e){
            Catcher::fire($e);
        }
    }

    public static function create($data = false)
    {
        self::insert($data);

        return clone self::getInstance();
    }

    public static function firstOrCreate($data = false)
    {
        $result = self::where(key($data),current($data))->first();

        if(!is_null($result) && count($result->toArray()) > 0){
            return $result;
        }

        return self::create($data);
    }

    public static function firstOrNew($data = false)
    {
        $result = self::where(key($data),current($data))->first();

        if(!is_null($result) && count($result->toArray()) > 0){
            return $result;
        }

        $newInstance = self::getInstance();
        $field = key($data);
        $newInstance->$field = current($data);

        return $newInstance;
    }

    public static function get()
    {
        self::createInstances();
        self::_query(false, 'find');

        return clone self::getInstance();
    }

    public static function all()
    {
        self::createInstances();
        self::_query(false, 'find');

        return clone self::getInstance();
    }

    public static function count()
    {
        self::createInstances();

        return self::$findResult = self::$_collection->count(self::$where);
    }

    private static function setInteractionResults($result, $mongo_id = false, $type)
    {
        $closure = function($result, $mongo_id, $type){
            if(is_array($result)) {
                if($type == 'insert'){
                    self::$dbData['id'] = $mongo_id->{'$id'};
                }
            }
        };

        $closure($result, $mongo_id, $type);
    }

    private static function getInstance()
    {
        if(!is_object(self::$Instance)){
            self::$Instance = new static;
        }

        return self::$Instance;
    }

    private static function getConn()
    {
        if($conn = self::$config->getConnString()){
            self::$on = $conn['on'];

            return $conn['link'];
        }

        exit('conn link not found');
    }

    private static function serviceClient()
    {
        if(!self::$_client) {
            self::$_client = Basemongo::MongoClient(self::getConn());
        }
    }

    private static function serviceCollection($db)
    {
        try {
            if (!self::$_collection) {
                self::$_collection = Basemongo::MongoCollection($db, self::getCollection());

                if (isset(self::$_collection->validate (true)['errmsg'])) {
                    throw new Catcher('hata var');
                }
            }
        }

        catch(Catcher $e){
            Catcher::fire($e);
        }
    }

    private static function getCollection()
    {
        if(!isset(self::getInstance()->collection)){
            $coll = preg_split('/\\\/',get_class(self::getInstance()));

            return strtolower(last($coll)).'s';
        }

        return self::getInstance()->collection;
    }

    public static function createMongoId()
    {
        try {
            if (!self::$_mongoId) {
                self::$_mongoId = Basemongo::mongoId();;
            }
        }

        catch(Catcher $e){
            Catcher::fire($e);
        }

        self::$mongoId = self::$_mongoId->{'$id'};
    }

    private static function createInstances()
    {
        self::getInstance();
        self::serviceClient();
        self::createMongoId();

        self::$selectedDb = self::$_client->selectDB(self::$dbname);
        self::serviceCollection(self::$selectedDb);
    }

    public static function mongoId($id)
    {
        if(!self::$_mongoId->isValid($id)){
            exit('invalid id');
        }

        return Basemongo::MongoId($id);
    }

    private static function checkTimestamps($type)
    {
        if(self::getInstance()->timestamps === true){
            self::$dbData[$type . '_at'] = date('Y-m-d H:i:s',time());
        }
    }

    private static function checkFillable()
    {
        $fillable = self::getInstance()->fillable;

        if(is_array($fillable) && count($fillable) > 0){
            $filter = array();

            foreach($fillable as $f){
                if(isset(self::$dbData[$f])){
                    $filter[$f] = self::$dbData[$f];
                }
            }

            self::$dbData = $filter;
        }
    }

    private static function checkGuarded()
    {
        $guarded = self::getInstance()->guarded;

        if(count($guarded) > 0) {
            foreach ($guarded as $g) {
                if (isset(self::$dbData[$g])){
                    unset(self::$dbData[$g]);
                }
            }
        }
    }

    private static function checkPrimaryKey()
    {
        if(isset(self::getInstance()->primaryKey) && !empty(self::getInstance()->primaryKey)){
            return self::getInstance()->primaryKey;
        }

        return '_id';
    }

    private static function setData($data)
    {
        self::$dbData = self::$resultAction->setData($data);
    }

    private static function _callMethod($method, $args)
    {
        if(method_exists(self::$resultAction, $method)){
            return self::$resultAction->injectDepend(array())->$method(self::$findResult);
        }

        elseif(method_exists(self::$query, $method)){
            $result = self::$query->$method($args);

            if(count(self::$$method) > 0){
                self::$$method = self::$query->chain(self::$$method, $result, $method);
            }else{
                self::$$method = self::$query->$method($args);
            }

            return self::getInstance();
        }

        elseif(method_exists(self::$config, $method)){
            self::$config->$method($args);

            return self::getInstance();
        }else{
            exit('dddd');
        }
    }

    public function __get($var)
    {
        if(isset(self::$dbData[$var])){
            return self::$dbData[$var];
        }
    }

    public function __set($field, $value)
    {
        if(count(func_get_args()) == 2){
            self::$dbData[$field] = $value;
            //self::setData(array($field => $value));
        }
    }

    public function __call($method, $args)
    {
        return self::_callMethod($method, $args);
    }

    public static function __callStatic($method, $args)
    {
        return self::_callMethod($method, $args);
    }
}