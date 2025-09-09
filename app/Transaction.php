<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = "transaction";

    protected $fillable = [
        'amount',
        'reference_id',
        'invoiceNumber',
        'user_id',
        'callback_msg',
        'wallet_id',
        'token',
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

    public function GetCreateDate()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }

    public function reserve()
    {
        return $this->belongsTo('App\Reservation','reserve_id');
    }

    public function wallet()
    {
        return $this->hasOne('App\Wallet','wallet_id');
    }
}
