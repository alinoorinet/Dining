<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    protected $table = "t_rest";

    protected $fillable = [
        'active',
        'name',
        'type',
        'store_id',
        'collection_id',
        'contractor_id',
        'sex',
        'close_at',
    ];

    public function info()
    {
        return $this->hasMany('App\RestInfo','rest_id');
    }

    public function contractor()
    {
        return $this->belongsTo('App\User','contractor_id');
    }

    public function store()
    {
        return $this->belongsTo('App\Store','store_id');
    }

    public function collection()
    {
        return $this->belongsTo('App\Collection','collection_id');
    }

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function users()
    {
        return $this->belongsToMany('App\User','t_users_rests','rest_id','user_id')->withPivot('active','id');
    }

    public function reserves()
    {
        return $this->hasMany('App\Reservation','rest_id');
    }
}
