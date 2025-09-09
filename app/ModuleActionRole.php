<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleActionRole extends Model
{
    protected $table = 'modules_actions_roles';
    protected $fillable = [
        'action_id',
        'role_id',
    ];
}
