<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class WastefulSetting extends Model
{
    protected $table = "wasteful_setting";

    protected $fillable = [
        'ban_type',
        'sequence_type',
        'sequense_times',
        'decrease_wallet',
        'days_limit',
        'last_check',
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
