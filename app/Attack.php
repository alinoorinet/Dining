<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attack extends Model
{
    protected $table = 'attack';
    protected $fillable = [
        'attack',
        'controller',
        'action',
        'fullpath',
        'ip_address',
        'user_agent',
        'user_id',
    ];
}
