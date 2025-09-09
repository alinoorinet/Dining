<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $table = 'change_log';
    protected $fillable = [
        'user_id',
        'content',
        'audience',
        'type',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
