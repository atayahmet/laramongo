<?php

namespace Laramongo;


class Laraexception extends \Exception{
    public static function fire($e)
    {
        exit(var_dump($e->getMessage()));
    }
}