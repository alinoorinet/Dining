<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletTmp extends Model
{
    protected $table = 'wallet_tmp';
    protected $fillable = [
        'amount',
        'std_no',
        'active',
    ];
    public $timestamps = false;
}
