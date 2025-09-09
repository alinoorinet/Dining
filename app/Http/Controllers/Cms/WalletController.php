<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        $wallet = Wallet::where('user_id', Auth::user()->id)
            ->orderBy('id','desc')
            ->first();

        $walletAmount = 0;
        if($wallet)
            $walletAmount = $wallet->amount;

        return view('cms.wallet.index',compact('walletAmount'));
    }
}
