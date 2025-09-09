<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class StoreInventory extends Model
{
    protected $table = 't_store_inventory';

    protected $fillable = [
        'price',
        'amount',
        'goods_id',
        'operator',
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
        return $this->belongsTo('App\StoreGoods','goods_id');
    }
}
