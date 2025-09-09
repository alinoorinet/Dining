<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallet';
    protected $fillable = [
        'amount',
        'user_id',
        'value',
        '_for',
        'operation',
    ];

    public function tran()
    {
        return $this->hasOne('App\Transaction','wallet_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
