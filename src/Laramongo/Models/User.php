<?php

namespace Laramongo\Models;

use Laramongo\Laramongo\Eloquent;

class User extends Eloquent {
    protected $collection = 'users';
    public $timestamps = true;

    protected $fillable = array('firstname','username');
    protected $guarded = array('address');
    protected $dates = ['deleted_at'];
    protected $primaryKey = '_id';
}