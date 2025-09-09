<?php

namespace App;
use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'title',
        'description',
        'active',
    ];

    public function action()
    {
        return $this->hasMany('App\ModuleAction','module_id');
    }

    public function GetCreateDate()
    {
        $jdate = new jdf();
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }
}
