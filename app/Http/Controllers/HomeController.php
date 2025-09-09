<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Card;
use App\ChangeLog;
use App\ContactUs;
use App\Library\WeekBox;
use App\Menu;
use App\Dorm;
use App\Facades\Rbac;
use App\Library\jdf;
use App\Library\Resreport;
use App\Library\Weekcreator;
use App\Notification;
use App\Rest;
use App\Transaction;
use App\User;
use App\RestInfo;
use App\UserGroupUsers;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function test_webservice()
    {
        $jdf   = new jdf();
        $time  = time();
        $date  = $jdf->jdate('Y-m-d',$time + (4 * 86400));
        $day   = $jdf->jalali_day($date,'l');
        echo $day;
        exit();
    }

    public function users_compare()
    {
        exit();
        set_time_limit(0);
        $foodUsers = DB::connection('food')
            ->table('users')
            ->select('id','username')
            ->whereBetween('id',[6000,6500])
            ->orderBy('id')
            ->get();

        /*$foodUser = DB::connection('food')
            ->table('users')
            ->select('id','username','std_no')
            ->where('std_no','9814114515') //9814114515
            ->orderBy('id')
            ->first();*/
        foreach ($foodUsers as $foodUser) {
            $user = User::where('username', $foodUser->username)->first();

            /*$diningTransCount = Transaction::where('user_id',$user->id)->count();
            $foodTransCount = DB::connection('food')
                ->table('transaction')
                ->select('id','user_id')
                ->where('user_id',$foodUser->id)
                ->count();*/

            // food_wallet + good_dining_wallet - bad_dining_wallet
            $date = date('Y-m-d') . ' 00:00:00';
            $check_Duplicate = $user->wallet()
                ->where('_for', 'موجودی صحیح پس از حسابرسی دوره ای')
                ->first();
            if ($check_Duplicate)
                continue;
            $food_wallet = 0;
            $bad_dining_wallet = 0;
            $good_dining_wallet = 0;

            $foodWalletAmount = DB::connection('food')
                ->table('wallet')
                ->select('id', 'amount', 'user_id')
                ->where('user_id', $foodUser->id)
                ->orderBy('id', 'desc')
                ->first();
            $good_dining_wallet_amount = $user->wallet()
                ->orderBy('id', 'desc')
                ->first();
            $diningBadWallet = $user->wallet()
                ->where('created_at', '>=', '2020-06-16 00:00:00')
                ->where('created_at', '<=', '2020-06-16 23:59:59')
                ->orderBy('id', 'desc')
                ->first();

            if ($foodWalletAmount)
                $food_wallet = $foodWalletAmount->amount;
            if ($good_dining_wallet_amount)
                $good_dining_wallet = $good_dining_wallet_amount->amount;
            if ($diningBadWallet)
                $bad_dining_wallet = $diningBadWallet->amount;

            $new_wallet = $good_dining_wallet - $bad_dining_wallet + $food_wallet;

//            echo "Food &nbsp;&nbsp; user id=$foodUser->id  username=$foodUser->username,   food_Wallet_Amount=$food_wallet,   Food_Trans_Count:$foodTransCount <br><br>";
//            echo "Dining user: id=$user->id  username=$foodUser->username,    Bad_Wallet_Amount=$bad_dining_wallet,  Current_Wallet_Amount=$good_dining_wallet Dining_Trans_Count:$diningTransCount ,     <br><br>";
//            echo "New Wallet: $new_wallet";

            Wallet::create([
                'amount' => $new_wallet,
                'user_id' => $user->id,
                'value' => $new_wallet,
                '_for' => 'موجودی صحیح پس از حسابرسی دوره ای',
                'operation' => $new_wallet >= $good_dining_wallet ? 1 : 0,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            //remove bad wallet
            $diningBadWallets = $user->wallet()
                ->where('created_at', '>=', '2020-06-16 00:00:00')
                ->where('created_at', '<=', '2020-06-16 23:59:59')
                ->get();
            foreach ($diningBadWallets as $data)
                $data->delete();


            // get food transactions
            $foodTrans = DB::connection('food')
                ->table('transaction')
                ->where('user_id', $foodUser->id)
                ->get();
            foreach ($foodTrans as $foodTran) {
                $dining_trans = Transaction::find($foodTran->id);
                if ($dining_trans) {
                    $dining_trans->user_id = $user->id;
                    $dining_trans->update();
                }
                else {
                    Transaction::create([
                        'id' => $foodTran->id,
                        'amount' => $foodTran->amount,
                        'reference_id' => $foodTran->reference_id,
                        'invoiceNumber' => $foodTran->invoiceNumber,
                        'type' => $foodTran->type,
                        'user_id' => $user->id,
                        'callback_msg' => $foodTran->callback_msg,
                        'wallet_id' => null,
                    ]);
                }
            }
        }

        exit();
    }

    /*public function de_active_users()
    {
        $acts = Activity::de_active_by_gholami();
        foreach ($acts as $k=>$act){
            $user = User::find($act->ids);
            echo '<pre>';
            print_r($user->std_no.'-'.$user->name.'- active :'.$user->active);
        }
        exit();
    }*/

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user   = Auth::user();
        //$changeLogs = ChangeLog::where('audience','admin')->limit(20)->orderBy('id','desc')->get();

        $wallet       = Wallet::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();
        $walletAmount = isset($wallet->id) ? $wallet->amount : 0;

        $jdf = new jdf();

        $todayTimestamp     = time();
        $date               = $jdf->jdate('Y-m-d');
        $day                = $jdf->jdate('l');
        $dayToWeekBegin     = $jdf->jdate('w');
        $dayToWeekend       = 6 - $dayToWeekBegin;
        $weekendTimestamp   = ($dayToWeekend * 86400) + $todayTimestamp;
        $weekbeginTimestamp = $todayTimestamp - ($dayToWeekBegin * 86400);
        $weekendDate        = $jdf->jdate('Y-m-d', $weekendTimestamp);
        $weekbeginDate      = $jdf->jdate('Y-m-d', $weekbeginTimestamp);
        $week = [
            'day'                => $day,
            'date'               => $date,
            'dayToweekbegin'     => $dayToWeekBegin,
            'dayToWeekend'       => $dayToWeekend,
            'todayTimestamp'     => $todayTimestamp,
            'weekendTimestamp'   => $weekendTimestamp,
            'weekbeginTimestamp' => $weekbeginTimestamp,
            'weekendDate'        => $weekendDate,
            'weekbeginDate'      => $weekbeginDate,
        ];
        session()->put('currWeek', $week);
        session()->forget('userWeek');
        session()->forget('wb_user');
        session()->forget('selected_coll');
        session()->forget('selected_rest');

        $data  = new \stdClass();
        $data2 = new \stdClass();
        $data->req_type = 'wb';
        $data2->weekbeginTimestamp = $weekbeginTimestamp;

        $broadcasts = Notification::where('broadcast',1)->where('self',0)->where('active',1)->orderBy('id','desc')->get();

        $wb       = new WeekBox($data);
        $weekBox  = $wb->make_menu($data2);
        $userRole = Rbac::user_role();

        return view('cms.home', [
            'weekBox'       => $weekBox,
            'userRole'      => $userRole,
            'user'          => $user,
            'walletAmount'  => $walletAmount,
            'broadcasts'    => $broadcasts,
        ]);
    }

    public function check_device_status()
    {
        if(!Rbac::check_access('self_service','check_device_status') && Rbac::module_is_active('self_service'))
            return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);

        set_time_limit(0);

        /*$selfs   = Rest::where('active',1)->get();
        $selfTmp = [];
        foreach ($selfs as $self) {
            $ips = $self->info;
            $ipTmp   = [];
            foreach ($ips as $ip) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_URL            => "http://openport.ir/ping/@IlamD2019@/$ip->ip/",
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                ));
                $res = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                curl_close($curl);

                $status = "<i class='fa fa-times text-danger animated infinite pulse delay-2s fa-lg'></i>";
                $rtt    = '-';
                if(isset($res->rtt_avg) && $res->rtt_avg != null) {
                    $status = "<i class='fa fa-check text-success fa-lg'></i>";
                    $rtt    = $res->rtt_avg;
                }
                $ipTmp[] = (object)[
                    'id'      => $ip->id,
                    'status'  => $status,
                    'rtt'     => $rtt,
                ];
            }
            $selfTmp[] = (object)[
                'id'     => $self->id,
                'status' => $ipTmp,
            ];
        }*/

        $selfs   = Rest::where('active',1)->get();
        $selfTmp = [];
        foreach ($selfs as $self) {
            $ips   = $self->info;
            $ipTmp = [];
            foreach ($ips as $ip) {
                $ipTmp[] = (object)[
                    'id'      => $ip->id,
                    'status'  => $ip->status,
                    'rtt'     => $ip->avg_rtt,
                    'ping_at' => $ip->updated_at(),
                ];
            }
            $selfTmp[] = (object)[
                'id'     => $self->id,
                'status' => $ipTmp,
            ];
        }
        return response()->json(['status' => 200, 'res' => $selfTmp]);
    }
}
