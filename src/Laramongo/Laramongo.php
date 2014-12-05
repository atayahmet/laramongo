<?php

namespace Laramongo;

use Laramongo\ResultActions\ResultAction;
use Laramongo\QueryBuilder;

class Laramongo implements LaramongoInterface {
    protected static $Instance;
    protected static $_client;
    protected static $_collection;
    protected static $_mongoId;
    protected static $mongoId;
    protected static $dbData = array();
    protected static $findResult;
    protected static $selectedDb;
    protected static $query;
    protected static $resultAction;
    public static $dbname = 'l5blog';

    protected static $where = array();
    protected static $limit;

    public function __construct()
    {
        self::$query = new QueryBuilder;
        self::$resultAction = new ResultAction;
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
       // echo '<pre>';
       // var_dump(self::$where);
        //exit(var_dump(self::$findResult));
        if(is_array(self::$findResult) || self::$findResult->count() > 0) {
            if(is_numeric(self::$limit)){
                if(is_object(self::$findResult)) {
                    self::$findResult = self::$findResult->limit (self::$limit);
                }

                if($setData && self::$limit == 1) {
                    self::setData (self::$findResult);
                }
            }

            return self::getInstance();
        }

        elseif($fail){
            exit('not found');
        }
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

        return self::_find(true, false, 'find');
    }

    public static function first()
    {
        self::$limit = 1;

        return self::_find(false, false, 'find');
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

    private static function _find($fail, $id, $method = 'find')
    {
        if($id){
            self::createInstances();
            self::where(array('_id' => $id));

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

            self::checkTimestamps('updated');

            $result = self::$_collection->findAndModify(self::$where, self::$dbData);

            if(is_array($result)){
                self::$mongoId = self::$findResult['_id']->{'$id'};

                return true;
            }

            return false;
        }

        catch(Catcher $e){
            //Catcher::fire($e);
        }

    }

    public static function insert($data = false)
    {
        try {
            if (is_array ($data)) {
                self::setData ($data);
            }

            if(count(self::$dbData) > 0) {
                self::createInstances ();
                self::checkTimestamps ('created');

                unset(self::$dbData['_id']);
                $data = self::$dbData;
                $result = self::$_collection->insert($data);

                return self::setInteractionResults($result, $data['_id'], 'insert');
            }
        }

        catch(Catcher $e){
            Catcher::fire($e);
        }
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

        return $closure($result, $mongo_id, $type);
    }

    private static function getInstance()
    {
        if(!is_object(self::$Instance)){
            self::$Instance = new static;
        }

        return self::$Instance;
    }

    private static function serviceClient()
    {
        if(!self::$_client) {
            self::$_client = new \MongoClient();
        }
    }

    private static function serviceCollection($db)
    {
        try {
            if (!self::$_collection) {
                self::$_collection = new \MongoCollection($db, self::getCollection ());

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
                self::$_mongoId = new \MongoId();
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

        return new \MongoId($id);
    }

    private static function checkTimestamps($type)
    {
        if(self::getInstance()->timestamps === true){
            self::$dbData[$type . '_at'] = date('Y-m-d H:i:s',time());
        }
    }

    private static function setData($data)
    {
        if(is_array($data)){
            foreach($data as $field => $val){
                self::$dbData[$field] = $val;
            }
        }else{
            foreach($data as $d){
                foreach($d as $field => $val) {
                    self::$dbData[$field] = $val;
                }
            }
        }
    }

    private static function _callMethod($method, $args)
    {
        if(method_exists(self::$resultAction, $method)){
            return ResultAction::$method(self::$findResult);
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
            self::setData(array($field => $value));
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