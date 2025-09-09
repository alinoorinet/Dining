<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FoodStuffs extends Model
{
    protected $table = 'food_stuffs';

    protected $fillable = [
        'food_id',
        'stuff_name',
        'amount',
        'amount_unit',
        'nut_value',
        'store_goods_id',
    ];

    public function goods()
    {
        return $this->hasOne('App\StoreGoods','store_goods_id');
    }
}
