<?php

namespace Laramongo\Models;

use Laramongo\Laramongo\Eloquent;

class User extends Eloquent {
    protected $collection = 'users';
    public $timestamps = true;

    protected $dates = ['deleted_at'];
    protected $fillable = array();
    protected $guarded = array();
    protected $primaryKey = '_id';
}