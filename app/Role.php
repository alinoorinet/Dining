<?php

namespace App;
use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'title',
        'description',
        'active',
        'priority'
    ];

    public function roleUser()
    {
        return $this->belongsToMany('App\User','users_roles','role_id','user_id');
    }

    public function roleAction()
    {
        return $this->belongsToMany('App\ModuleAction','modules_actions_roles','role_id','action_id')->withPivot('id');
    }

    public function GetCreateDate()
    {
        $jdate = new jdf();
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }
}
