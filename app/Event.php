<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 't_event';

    protected $fillable = [
        'name',
        'organizer_id',
        'guest_count',
        'from_date',
        'to_date',
        'organization',
        'guest_type',
        'max_user_reserve',
        'description',
        'active',
        'confirmed',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function organizer()
    {
        return $this->belongsTo('App\User','organizer_id');
    }

    public function user_groups()
    {
        return $this->belongsToMany('App\UserGroup','t_event_user_group','event_id','user_group_id');
    }

    public function prices()
    {
        return $this->belongsToMany('App\FoodPrice','price_event','event_id','price_id');
    }

    public function menu()
    {
        return $this->belongsToMany('App\Menu','menu_event','event_id','menu_id');
    }

    public function reserves()
    {
        return $this->belongsToMany('App\Reservation','reservation_event','event_id','reserve_id');
    }
}
