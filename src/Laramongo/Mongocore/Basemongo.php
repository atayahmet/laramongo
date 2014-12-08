<?php

namespace Laramongo\Mongocore;

class Basemongo {

    public static function MongoCollection($db, $collection)
    {
        return new \MongoCollection($db, $collection);
    }

    public static function MongoClient($args)
    {
        return new \MongoClient($args);
    }

    public static function MongoId($id = null)
    {
        return new \MongoId($id);
    }
}
