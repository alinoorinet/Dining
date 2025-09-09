<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'food';

    protected $fillable = [
        'title',
        'caption',
        'pic',
        'type',
        'swf_code',
        'is_active',
    ];

    public function price()
    {
        return $this->hasMany('App\FoodPrice','foodmenu_id');
    }

    public function stuffs()
    {
        return $this->hasMany('App\FoodStuffs','food_id');
    }
}
