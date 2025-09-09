<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class RestInfo extends Model
{
    protected $table = "t_rest_info";

    protected $fillable = [
        'ip',
        'rest_id',
        'status',
        'avg_rtt',
        'description',
    ];

    public function updated_at()
    {
        $jdate = new jdf();
        return $jdate->getPersianDate($this->updated_at,'d F Y ساعت H:i:s');
    }

    public function rest()
    {
        return $this->belongsTo('App\Rest','rest_id');
    }
}
