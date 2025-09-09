<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class UsersRests extends Model
{
    protected $table = "t_users_rests";

    protected $fillable = [
        'user_id',
        'rest_id',
        'active',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }
}
