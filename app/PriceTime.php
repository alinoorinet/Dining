<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceTime extends Model
{
    protected $table = 'price_time';

    protected $fillable = [
        'from_time',
        'to_time',
        'price_id',
    ];

    public function price()
    {
        return $this->belongsTo('App\FoodPrice','price_id');
    }

    public $timestamps = false;
}
