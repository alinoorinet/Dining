<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceEvent extends Model
{
    protected $table = 'price_event';

    protected $fillable = [
        'event_id',
        'price_id',
    ];
}
