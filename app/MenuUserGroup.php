<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuUserGroup extends Model
{
    protected $table = 'menu_user_group';

    protected $fillable = [
        'menu_id',
        'user_group_id',
        'max_res',
    ];
}
