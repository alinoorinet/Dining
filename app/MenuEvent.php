<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuEvent extends Model
{
    protected $table = 'menu_event';

    protected $fillable = [
        'menu_id',
        'event_id',
    ];
}
