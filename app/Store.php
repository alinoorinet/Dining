<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 't_store';

    protected $fillable = [
        'name',
        'user_id',
        'active',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function goods()
    {
        return $this->hasMany('App\StoreGoods','store_id');
    }

    public function rests()
    {
        return $this->hasMany('App\Rest','store_id');
    }
}
