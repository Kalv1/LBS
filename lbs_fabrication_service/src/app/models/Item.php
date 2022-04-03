<?php

namespace lbs\fab\app\models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'item';
    public $timestamps = false;
}