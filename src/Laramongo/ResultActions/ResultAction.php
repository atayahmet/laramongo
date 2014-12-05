<?php

namespace Laramongo\ResultActions;

class ResultAction {
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
}