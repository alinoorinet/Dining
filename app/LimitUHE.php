<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class LimitUHE extends Model
{
    protected $table = 'limit_unpaied_housing_exception';
    protected $fillable = [
        'user_id',
        'term',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }
}
