<?php

namespace Laramongo;

interface LaramongoInterface {
    public static function save();
    public static function lastId();
    public static function update($data = false);
    public static function insert($data = false);
    public static function get();
}