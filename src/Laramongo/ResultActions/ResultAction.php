<?php

namespace Laramongo\ResultActions;

use Laramongo\Laramongo\Eloquent;

class ResultAction {
    protected static $depends;

    public static function toArray($results)
    {
        if(is_array($results) == 1) return $results;

        $toArray = array();

        foreach($results as $result){
            $temp = array();

            foreach($result as $field => $val){
                $temp[$field] = $val;
            }

            $toArray[] = $temp;
        }

        return $toArray;
    }

    public static function setData($data, $dbData = array())
    {

        foreach($data as $f => $d){
            if(is_array($d)) {
                self::setData($d,$dbData);
            }else{
                $dbData[$f] = $d;
            }
        }

        return $dbData;
    }

    public static function injectDepend($depends)
    {
        self::$depends = $depends;

        return new static;
    }
}