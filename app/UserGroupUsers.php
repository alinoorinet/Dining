<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class UserGroupUsers extends Model
{
    protected $table = 'user_group_users';

    protected $fillable = [
        'user_id',
        'user_group_id',
        'priority',
        'is_primary',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public $timestamps = false;

    public function user_group()
    {
        return $this->belongsTo('App\UserGroup','user_group_id');
    }
}
