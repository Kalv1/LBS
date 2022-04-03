<?php

namespace lbs\command\app\models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'commande';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $hidden = ['created_at', 'updated_at', 'token', 'status'];
}