<?php

namespace lbs\command\app\models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'item';
    public $timestamps = false;
}