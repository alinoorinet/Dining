<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class DormException extends Model
{
    protected $table    = 'dorm_exception';
    protected $fillable = [
        'user_id',
        'creator_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User','creator_id');
    }

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }
}
