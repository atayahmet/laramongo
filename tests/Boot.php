<?php

require __DIR__ . '/../../../../vendor/autoload.php';

use Laramongo\Laramongo\Eloquent;
use Laramongo\Models\User;
use Laramongo\Mongocore\Basemongo;
use Laramongo\ResultActions\ResultAction;
use Laramongo\Laramongo\QueryBuilder;
use Laramongo\Config\Config;

class Boot extends \PHPUnit_Framework_TestCase {
    public static $instance = array();

    protected function setUp()
    {
        // create models instance
        self::$instance['user'] = new User;
    }
}