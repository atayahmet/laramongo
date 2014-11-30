<?php

namespace Laramongo;

use Laramongo\Laraexception as Catcher;

class Laramongo implements LaramongoInterface {
    protected static $Instance;
    protected static $_client;
    protected static $_collection;
    protected static $mongoId;
    protected static $dbData = array();
    protected static $findResult;
    protected static $selectedDb;
    public static $dbname = 'l5blog';

    protected static $where = array();

    public static function save()
    {

        if(!is_array(self::$findResult)){
            return self::insert();
        }


        return self::update();

        exit(var_dump(self::$findResult['_id']->{'$id'}));
        exit(var_dump(self::$dbData));
        $dbName = self::$_client->selectDB(self::$dbname);

      //  $t = new \MongoCollection($x, self::getCollection());
        //echo '<pre>';
        $coll = self::collection($dbName);
       // exit(var_dump($coll));


        $result = $coll->insert(array('title' => 'te beyleeee'));
        //exit(var_dump($result));
        //$m = $db->selectCollection('l5blog','content');
        //exit(var_dump($m));
        //$db->createCollection(self::getInstance()->collection);
       //self::getCollection();
      //  last()
       // exit(var_dump(get_class(self::getInstance())));
       // $m->bal->insert(array('name' => 'test'));
        //exit(var_dump($m->insert(array('title' => 'testx'))));

    }

    public static function lastId()
    {
        return self::$mongoId;
    }

    public static function find($id = false)
    {
        if(count(func_get_args()) > 0){
            self::createInstances();

            self::$findResult = self::$_collection->findOne(array('_id' => new \MongoId($id)));

            if(self::$findResult) {
                self::where(array("_id" => self::mongoId(self::$findResult['_id']->{'$id'})));
                return new static;
            }
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

    public static function where($field = false, $compare = '=', $val = false)
    {
        try {
            $args = func_get_args();

            if(count($args) == 1){
                if(!is_array($args[0])){
                    throw new Catcher('hatalı parametre');
                }

                foreach($args[0] as $field => $val){
                    self::$where[$field] = $val;
                }
            }

            elseif(count($args) == 2){
                self::$where[$args[0]] = $args[1];
            }

            elseif(count($args) == 3){

            }
            else
            {
                throw new Catcher('hatalı parametre');
            }

            return self::getInstance();
        }

        catch(Catcher $e){
            Catcher::fire($e);
        }
    }

    public static function get()
    {

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
                    throw new ErrorCatcher('hata var');
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

    private static function createMongoId()
    {
        $m = new \MongoId();
        self::$mongoId = $m->{'$id'};
    }

    private static function createInstances()
    {
        self::getInstance();
        self::serviceClient();
        self::createMongoId();

        self::$selectedDb = self::$_client->selectDB(self::$dbname);
        self::serviceCollection(self::$selectedDb);
    }

    private static function mongoId($id)
    {
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
        foreach($data as $field => $val){
            self::$dbData[$field] = $val;
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
}