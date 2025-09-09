<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dorm extends Model
{
    protected $table = 'dorm';
    protected $fillable = [
        'title',
        'uid_dormid',
        'housing_id',
        'sex',
        'can_reserve',
    ];
}
