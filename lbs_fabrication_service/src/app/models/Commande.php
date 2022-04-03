<?php

namespace lbs\fab\app\models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'commande';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $hidden = ['updated_at','mail', 'montant', 'token'];
}