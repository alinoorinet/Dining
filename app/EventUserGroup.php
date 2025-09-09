<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventUserGroup extends Model
{
    protected $table = 't_event_user_group';

    protected $fillable = [
        'event_id',
        'user_group_id',
        'max_reserve',
    ];

    public $timestamps = false;
}
