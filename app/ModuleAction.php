<?php

namespace App;
use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class ModuleAction extends Model
{
    protected $table = 'modules_actions';
    protected $fillable = [
        'title',
        'description',
        'module_id',
        'active',
    ];
    public function module()
    {
        return $this->belongsTo('App\Module','module_id');
    }
    public function actionRole()
    {
        return $this->belongsToMany('App\Role','modules_actions_roles','action_id','role_id');
    }

    public function GetCreateDate()
    {
        $jdate = new jdf();
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }
}
