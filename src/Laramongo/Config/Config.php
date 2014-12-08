<?php

namespace Laramongo\Config;

class Config {
    protected static $use;
    protected static $on = false;
    protected static $config;

    public function __construct()
    {
        include __DIR__ . '/../connections.php';

        self::$use = $use;
        self::$config = $config;
    }

    public static function getConnString()
    {
        $conn = self::$on ? self::$on : self::$use;

        return self::initString($conn);
    }

    public static function on($conn = false)
    {
        if(isset($conn[0])){
            self::$on = $conn[0];
        }
    }

    private static function initString($conn)
    {
        if(isset(self::$config[$conn])){
            $db = self::$config[$conn];

            $data['link'] = 'mongodb://' . $db['username'] . ':' . $db['password'] . '@' . $db['host'];
            $data['on'] = false;

            if(self::$on){
                $data['on'] = true;
                self::$on = false;
            }

            return $data;
        }

        return false;
    }

    public function __get($r)
    {
        exit(var_dump(func_get_args()));
    }
}