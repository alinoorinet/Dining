<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact_us';
    protected $fillable = [
        'request',
        'response',
        'receiver',
        'readed',
        'answered',
        'user_id',
        'name',
        'email',
        'mobile',
        'std_no',
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
}
