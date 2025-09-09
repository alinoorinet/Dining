<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Wasteful extends Model
{
    protected $table = "wasteful";

    protected $fillable = [
        'last_date_check',
        'user_id',
        'none_eaten_csct',
        'none_eaten_none_csct',
        'banned',
        'ban_type',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i:s');
    }

    public function updated_at()
    {
        $jdate = new jdf();
        return $jdate->getPersianDate($this->updated_at,'d F Y ساعت H:i:s');
    }
}
