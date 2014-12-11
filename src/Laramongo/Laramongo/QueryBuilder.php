<?php

namespace Laramongo\Laramongo;

use Laramongo\Mongocore\Basemongo;

class QueryBuilder {

    protected static $whereOp = array('>' => '$gt', '>=' => '$gte', '<' => '$lt', '<=' =>'$lte', '<>' => '$ne', '!=' => '$ne','in' => '$in','notIn','$nin','$exists' => '$exists');
    protected static $chains = array('where' => '$and');

    public static function where($args)
    {
        $where = array();

        if(count($args) == 1){
            foreach($args[0] as $field => $val){
                $where[$field] = $field == '_id' ? Basemongo::MongoId($val) : $val;
            }
        }

        elseif(count($args) == 2){
            $where[$args[0]] = $args[0] == '_id' ? Basemongo::MongoId($args[1]) : $args[1];
        }

        elseif(count($args) == 3 && $op = self::checkOp($args[1], 'where')){
            $where = array($args[0] => array(htmlentities($op) => self::opScopes($op, $args[2])));
        }

        return $where;
    }

    private static function checkOp($op, $type)
    {
        $type = $type.'Op';
        $ops = self::$$type;

        if(isset($ops[$op])){
            return $ops[$op];
        }else{
            $ops = array_flip(self::$$type);

            if(isset($ops[$op])){
                return $op;
            }
        }
    }

    private static function opScopes($op, $val)
    {
        switch($op){
            case '$in';
                return !is_array($val) ? array($val) : $val;
                break;

            case '$nin';
                return !is_array($val) ? array($val) : $val;
                break;
            default:
                return $val;
        }
    }

    public static function chain($where, $result, $method)
    {
        if($ch = self::$chains[strtolower($method)]){
            if(isset($where[$ch])){
                $where[$ch][] = $result;

                return $where;
            }

            $w[$ch][] = $result;
            $w[$ch][] = $where;

            return $w;
        }
    }
}