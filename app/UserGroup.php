<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $table = 'user_group';

    protected $fillable = [
        'title',
        'kindid',
        'max_reserve_simultaneous',
        'max_discount',
        'max_reserve',
        'can_reserve',
        'parent_id',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function parent()
    {
        return $this->belongsTo('App\UserGroup','parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\UserGroup','parent_id');
    }

    public function events()
    {
        return $this->belongsToMany('App\Event','t_event_user_group','user_group_id','event_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User','user_group_users','user_group_id','user_id');
    }

    public function menu()
    {
        return $this->belongsToMany('App\Menu','menu_user_group','user_group_id','menu_id')->withPivot('max_res');
    }
}
