<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'day',
        'date',
        'meal',
        'active',
        'food_id',
        'food_type', // 0 = غذا، 1 = دسر
        'menu_type', // 0 = عادی، 1 = رویداد، 2 = هردو
        'dessert_type', // 0 = آزاد، 1 = وابسته
        'parent_id',
        'pors',
        'event_id',
        'rest_id',
        'collection_id',
        'max_reserve_user',
        'max_reserve_total',
        'half_reserve',
        'food_title',
        'close_at',
        'has_garnish',
    ];

    public function food_menu()
    {
        return $this->belongsTo('App\Food','food_id');
    }

    public function reservation()
    {
        return $this->hasMany('App\Reservation','menu_id');
    }

    public function user_groups()
    {
        return $this->belongsToMany('App\UserGroup','menu_user_group','menu_id','user_group_id')->withPivot('max_res');
    }

    public function events()
    {
        return $this->belongsToMany('App\Event','menu_event','menu_id','event_id');
    }

    public function desserts()
    {
        return $this->belongsToMany('App\Food','menu_dessert','menu_id','dessert_id');
    }
}
