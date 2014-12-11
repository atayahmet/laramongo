<?php

namespace Laramongo\Exceptions;

class Laraexception extends \Exception{
    private static $msg = array(
        'query_not_found' => 'No results were found for query',
        'missing_parameter' => 'Incorrect/Missing parameter'
    );

    public static function fire($error)
    {
        exit(var_dump(self::$msg[$error['e']->getMessage()]));
    }
}