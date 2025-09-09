<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class StoreGoods extends Model
{
    protected $table = 't_store_goods';

    protected $fillable = [
        'goods_name',
        'brand',
        'amount_unit',
        'last_price',
        'last_amount',
        'nut_value',
        'store_id',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function good()
    {
        return $this->belongsTo('App\Store','store_id');
    }

    public function inventory()
    {
        return $this->hasMany('App\StoreInventory','goods_id');
    }
}
