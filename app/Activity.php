<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Library\jdf;

class Activity extends Model
{
    protected $table = 'activity';
    protected $fillable = [
        'ip_address',
        'user_agent',
        'task',
        'description',
        'user_id',
        'ids',
    ];

    public function GetCreateDate()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function de_active_by_gholami()
    {
        $activity = Activity::where('task','de-active-user')->where('user_id', 4032)->where('description','عدم پرداخت شهریه خوابگاه')->get();
        return $activity;
    }
}
