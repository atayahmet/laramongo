<?php

namespace Laramongo\Laramongo;

interface EloquentInterface {
    public static function save();
    public static function lastId();
    public static function find($id = false);
    public static function findOrFail($id = false);
    public static function firstOrFail();
    public static function first();
    public static function take($limit);
    public static function whereRaw($raw);
    public static function update($data = false);
    public static function delete();
    public static function destroy($args);
    public static function touch();
    public static function insert($data = false);
    public static function create($data = false);
    public static function firstOrCreate($data = false);
    public static function firstOrNew($data = false);
    public static function get();
    public static function all();
    public static function count();
    public static function withTrashed();
    public static function onlyTrashed();
    public static function restore();
    public static function forceDelete();
    public static function trashed();
}