<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 14/09/2016
 * Time: 06:27 PM
 */

namespace App\Http\ViewComposers;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NavComposer
{
    /*public function __construct()
    {
        $this->middleware(['auth']);
    }*/

    public function index(View $view)
    {
        /*if(!empty(Auth::user()->img))
            $uimg = 'data:image/jpeg;base64,'.base64_encode(file_get_contents(Auth::user()->img));
        else*/
        $uimg = '/img/prof-default.png';

        $view->with('uimg', $uimg);
    }

    public function wallet(View $view)
    {
        $walletAmount = 0;
        $wallet       = Wallet::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();
        if($wallet)
            $walletAmount = $wallet->amount;

        $view->with('wallet',$walletAmount);
    }
}