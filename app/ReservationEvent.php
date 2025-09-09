<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationEvent extends Model
{
    protected $table = 'reservation_event';

    protected $fillable = [
        'reserve_id',
        'event_id',
    ];
}
