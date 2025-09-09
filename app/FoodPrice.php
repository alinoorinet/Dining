<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FoodPrice extends Model
{
    protected $table = 'food_price';

    protected $fillable = [
        'price',
        'foodmenu_id',
        'usergroup_id',
        'meal',
        'rest_id',
        'collection_id',
        'type',
        'discount',
        'discount_count',
    ];

    public function foodmenu()
    {
        return $this->belongsTo('App\Food','foodmenu_id');
    }

    public function usergroup()
    {
        return $this->belongsTo('App\UserGroup','usergroup_id');
    }

    public function events()
    {
        return $this->belongsToMany('App\Event','price_event','price_id','event_id');
    }

    public function times()
    {
        return $this->hasMany('App\PriceTime','price_id');
    }
}
