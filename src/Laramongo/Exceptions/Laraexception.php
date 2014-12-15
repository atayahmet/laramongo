<?php

namespace Laramongo\Exceptions;

class Laraexception extends \MongoException {
    private static $msg = array(
        'query_not_found' => 'No results were found for query',
        'missing_parameter' => 'Incorrect/Missing parameter'
    );

    public static function fire($error)
    {
        if(isset(self::$msg[$error['e']->getMessage()])){
            $msg = self::$msg[$error['e']->getMessage()];
        }else{
            $msg = $error['e']->getMessage();
        }

        exit(var_dump($msg));
    }
}