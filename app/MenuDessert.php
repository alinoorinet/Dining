<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuDessert extends Model
{
    protected $table = 'menu_dessert';

    protected $fillable = [
        'menu_id',
        'dessert_id',
    ];
}
