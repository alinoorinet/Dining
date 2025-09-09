<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservation';
    protected $fillable = [
        'food_title',
        'user_id',
        'menu_id',
        'foodprice_id',
        'food_type', // 0 = غذا، 1 = دسر
        'menu_type', // 0 = عادی، 1 = رویداد
        'eaten',
        'rest_id',
        'collection_id',
        'user_name',
        'sex',
        'creator_id',
        'dorm_id',
        'meal',
        'eaten_in',
        'eaten_at',
        'eaten_ip',
        'count',
        'discount_amount',
        'discount_count',
        'date',
        'day',
        'pay_amount',
        'sync_with_store',
        'real_price',
        'wallet_after',
        'half_reserve',
        'user_group_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function menu()
    {
        return $this->belongsTo('App\Menu');
    }

    public function rest()
    {
        return $this->belongsTo('App\Rest','rest_id');
    }

    public function price()
    {
        return $this->belongsTo('App\FoodPrice','foodprice_id');
    }

    public function events()
    {
        return $this->belongsToMany('App\Event','reservation_event','reserve_id','event_id');
    }

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function updatedAt()
    {
        $jdate = new jdf();
        if($this->updated_at == '')
            return '';
        return $jdate->getPersianDate($this->updated_at,'d F Y ساعت H:i:s');
    }
}
