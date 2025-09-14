<?php

namespace App\Http\Controllers\Cms;

use App\Library\WeekBox;
use App\DdfFoodPrice;
use App\Facades\Activity;
use App\Facades\Rbac;
use App\FreeDdf;
use App\FreeDdo;
use App\FreeFoodMenu;
use App\FreeReservation;
use App\FreeReservationOpt;
use App\Library\Transaction_Handler;
use App\Library\Weekcreator;
use App\Transaction;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['Filter']);
    }

    public function make_order_modal(Request $request)
    {
        $data = new \stdClass();
        $data->req_type = 'md';
        $data->request  = $request->json()->all();
        $wb    = new WeekBox($data);
        $result = $wb->make_menu();

        return response()->json(['status' => $result['status'], 'res' => $result['res']]);
    }

    static private function price_exception()
    {
        return [
            'صبحانه' => [
            ],
            'نهار' => [
            ],
            'شام'  => [
            ],
        ];
    }

    public function set(Request $request)
    {
        if (Rbac::check_access('reservation', 'set_unset')) {
            $data = new \stdClass();
            $data->req_type = 'set';
            $data->request  = $request;

            $wb = new WeekBox($data);
            $result = $wb->set_reserve();
            return response()->json($result);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);
    }

    public function cancel(Request $request)
    {
        if (Rbac::check_access('reservation', 'set_unset')) {
            $data = new \stdClass();
            $data->req_type = 'unset';
            $data->request  = $request;

            $wb = new WeekBox($data);
            $result = $wb->unset_reserve();
            return response()->json($result);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);
    }


    protected $MerchantId = '-';
    protected $sha1Key = '-';

    public function pay(Request $request)
    {
        if(Rbac::check_access('reservation','pay')) {
            Validator::make($request->all(), [
                'amount' => 'required|numeric|digits_between:4,7',
            ], [
                'amount.required'       => 'مبلغ افزایش اعتبار را وارد کنید',
                'amount.numeric'        => 'مبلغ افزایش اعتبار باید رقم باشد',
                'amount.digits_between' => 'حداقل افزایش اعتبار 1000 ریال است',
            ])->validate();

            $th = new Transaction_Handler();

            try {
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                    ]
                ]);
                $client = new \SoapClient('https://ikc.shaparak.ir/XToken/Tokens.xml', array('soap_version' => SOAP_1_1,'cache_wsdl' => WSDL_CACHE_NONE, 'stream_context' => $context));
                if (!$client)
                    throw new \Exception;
            }
            catch (\Exception $e) {
                Activity::save_log('soapFailed1','فراخوانی وب سرویس در مرحله اول ناموفق بود');
                $res = $th->user_transaction_result(101);
                return redirect()->back()->with('payResult', $res);
            }

            /*
             * XXX
             * */
            $lastTrans = Transaction::orderBy('id','desc')->first();
            if(!$lastTrans)
                $invoiceNumber    = '20000001';//20,000,001 - 200,000,000
            else
                $invoiceNumber = (string)((int)$lastTrans->invoiceNumber + 1);

            $amount = $request->amount;
            $trans = new Transaction();
            $trans->amount           = $amount;
            $trans->reference_id     = null;
            $trans->invoiceNumber    = $invoiceNumber;
            $trans->user_id          = Auth::user()->id;
            $trans->save();

            $revertURL = config('app.url').'/home/callback';

            $params['amount']           = $amount;
            $params['merchantId']       = $this->MerchantId;
            $params['invoiceNo']        = $invoiceNumber;
            $params['paymentId']        = $trans->id;
            $params['specialPaymentId'] = 'dining'.$invoiceNumber;
            $params['revertURL']        = $revertURL;
            $params['description']      = "افزایش اعتبار سامانه تغذیه -";
            try {
                $result = $client->__soapCall("MakeToken", array($params));
                $token  = $result->MakeTokenResult->token;
                if(!$token)
                    throw new \Exception;
            }
            catch(\Exception $e)
            {
                try {
                    $result = $client->__soapCall("MakeToken", array($params));
                    $token  = $result->MakeTokenResult->token;
                    if(!$token)
                        throw new \Exception;
                }
                catch(\Exception $e)
                {
                    Activity::save_log('makeToken1Failed',$amount,null);
                    $res = $th->user_transaction_result(101);
                    return redirect()->back()->with('payResult', $res);
                }
            }
            $trans->token = $token;
            $trans->save();

            $data['token']      = $token;
            $data['merchantId'] = $this->MerchantId;
            Activity::save_log('tryingPay',$amount,'آماده رفتن به صفحه پرداخت');

            return view('pay.calling_paygate', [
                'data' => $data,
                'url'  => 'https://ikc.shaparak.ir/TPayment/Payment/index',
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    static public function messeg2($result)
    {
        switch ($result)
        {
            case '-20':
                return "در درخواست کارکتر های غیر مجاز وجود دارد";
                break;
            case '-30':
                return " تراکنش قبلا برگشت خورده است";
                break;
            case '-50':
                return " طول رشته درخواست غیر مجاز است";
                break;
            case '-51':
                return " در در خواست خطا وجود دارد";
                break;
            case '-80':
                return " تراکنش مورد نظر یافت نشد";
                break;
            case '-81':
                return " خطای داخلی بانک";
                break;
            case '-90':
                return " تراکنش قبلا تایید شده است";
                break;
            case '-91':
                return "تراکنش قبلا تایید شده / مدت زمان انتضار برای تایید  به پایان رسیده است";
                break;
        }
    }

    static public function messeg($resultCode)
    {
        switch ($resultCode)
        {
            case 110:
                return " انصراف دارنده کارت";
                break;
            case 120:
                return"موجودی حساب کافی نمی باشد";
                break;
            case 121:
                return"مبلغ تراکنش های کارت بیش از حد مجاز است";
                break;
            case 130:
            case 131:
            case 160:
                return"اطلاعات کارت اشتباه است";
                break;
            case 132:
            case 133:
                return"   کارت مسدود یا منقضی می باشد";
                break;
            case 140:
                return" زمان مورد نظر به پایان رسیده است";
                break;
            case 200:
            case 201:
            case 202:
                return" مبلغ بیش از سقف مجاز";
                break;
            case 166:
                return" بانک صادر کننده مجوز انجام  تراکنش را صادر نکرده";
                break;
            case 150:
            default:
                return " خطا بانک  $resultCode";
                break;
        }
    }

    public function callBack(Request $request)
    {
        $th = new Transaction_Handler();
        $callbackToken = $request->token;
        $referenceId = isset($request->referenceId) ? $request->referenceId : 0;

        $timestamp = time();
        $dateMin = date('Y-m-d H:i:s', $timestamp - 600);
        $dateMax = date('Y-m-d H:i:s');

        $transaction = Transaction::where('token', $callbackToken)
            ->where('created_at', '>=', $dateMin)
            ->where('created_at', '<=', $dateMax)
            ->first();
        if (!$transaction) {
            //Activity::save_log('transNotFound',(string)$referenceId, $callbackToken ?:0);
            $res = $th->user_transaction_result(107);
            return redirect('/home')->with('payResult', $res);
        }
        $amount = $transaction->amount;
        $invNum = $transaction->invoiceNumber;
        $paymentId = $transaction->id;
        $user = $transaction->user;
        Auth::loginUsingId($user->id);
        session()->put('origin_ip', \Request::ip());

        if ($transaction->reference_id){
            Activity::save_log('verified-before', (string)$referenceId, $callbackToken ?: 0);
            $res = $th->user_transaction_result(201);
            return redirect('/home')->with('payResult', $res);
        }

        Activity::save_log('comeBackToDining',(string)$referenceId, $callbackToken ?:0);
        if (isset($request->resultCode) && $request->resultCode == '100') {
            try {
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer'      => true,
                        'verify_peer_name' => true,
                    ]
                ]);
                $client = new \SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version' => SOAP_1_1,'cache_wsdl' => WSDL_CACHE_NONE, 'stream_context' => $context));
                if (!$client)
                    throw new \Exception;
            }
            catch (\Exception $e) {
                Activity::save_log('soapFailed2',(string)$referenceId.'-'.$amount.'-'.$invNum, $paymentId);
                $res = $th->user_transaction_result(102);
                $transaction->callback_msg = 'قطعی شبکه بانکی';
                $transaction->update();
                return redirect('/home')->with('payResult', $res);
            }
            $params['token']            = $callbackToken;
            $params['merchantId']       = $this->MerchantId;
            $params['referenceNumber']  = $referenceId;
            $params['sha1Key']          = $this->sha1Key;
            try {
                $result = $client->__soapCall("KicccPaymentsVerification", array($params));
                if(!$result)
                    throw new \Exception;
            }
            catch(\Exception $e) {
                try {
                    $result = $client->__soapCall("KicccPaymentsVerification", array($params));
                    if(!$result)
                        throw new \Exception;
                }
                catch(\Exception $e) {
                    Activity::save_log('PaymentsVerificationFailed',$amount,$paymentId);
                    $res = $th->user_transaction_result(106);
                    $transaction->callback_msg = 'قطعی شبکه بانکی-تایید تراکنش ناموفق-برگشت مبلغ به حساب';
                    $transaction->update();
                    return redirect('/home')->with('payResult', $res);
                }
            }
            $result = ($result->KicccPaymentsVerificationResult);
            if (floatval($result) > 0 && (floatval($result) == floatval($amount))) {
                //Payment verified and OK !
                Activity::save_log('verifySuccess',(string)$referenceId.'-'.$amount.'-'.$invNum, $paymentId);

                $wl = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
                if ($wl)
                    $newAmount = $wl->amount + (int)$amount;
                else
                    $newAmount = (int)$amount;

                $wallet = new Wallet();
                $wallet->amount         = $newAmount;
                $wallet->user_id        = $user->id;
                $wallet->_for           = 'افزایش اعتبار تغذیه';
                $wallet->value          = (string)$amount;
                $wallet->operation      = 1;
                $wallet->save();

                $transaction->reference_id = (string)$referenceId;
                $transaction->callback_msg = 'تراکنش موفق';
                $transaction->wallet_id    = $wallet->id;
                $transaction->update();

                $res = $th->user_transaction_result(200,$invNum,$referenceId);
                return redirect('/home')->with('payResult', $res);
            }
            else {
                $msg = self::messeg2($result);
                if(floatval($result) == -90) {
                    Activity::save_log('-90',(string)$referenceId.'-'.$amount.'-'.$invNum, $paymentId);
                    $res = $th->user_transaction_result(200,$invNum,$referenceId);
                    return redirect('/home')->with('payResult', $res);
                }
                elseif (floatval($result) == -51) {
                    Activity::save_log('-51',$msg,(string)$referenceId.'-'.$amount.'-'.$invNum.'-'.$paymentId);
                    $params['merchantId']  = $this->MerchantId;
                    $params['remoteIp']    = $_SERVER['SERVER_ADDR'];
                    $params['invoiceNo']   = $invNum;
                    $params['referenceNo'] = $referenceId;
                    try {
                        $result = $client->__soapCall("getTransaction", array($params));
                        if (!$result)
                            throw new \Exception;
                        $resultCode = isset($result->getTransactionResult->RESULTCODE)?$result->getTransactionResult->RESULTCODE:-80;
                        if($resultCode == 100) {
                            if($transaction->reference_id == null) {
                                try {
                                    $wl = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
                                    if ($wl)
                                        $newAmount = $wl->amount + (int)$amount;
                                    else
                                        $newAmount = (int)$amount;

                                    $wallet = new Wallet();
                                    $wallet->amount    = $newAmount;
                                    $wallet->user_id   = $user->id;
                                    $wallet->_for      = 'افزایش اعتبار تغذیه';
                                    $wallet->value     = (string)$amount;
                                    $wallet->operation = 1;
                                    $wallet->save();

                                    $transaction->reference_id = (string)$referenceId;
                                    $transaction->callback_msg = 'تراکنش موفق';
                                    $transaction->wallet_id    = $wallet->id;
                                    if(!$transaction->update())
                                        throw new \Exception;
                                }
                                catch (\Exception $e) {
                                    Activity::save_log('-51transFailed',(string)$referenceId.'-'.$amount.'-'.$invNum,$paymentId);
                                }
                            }
                            $res = $th->user_transaction_result(200, $invNum, $referenceId);
                            return redirect('/home')->with('payResult', $res);
                        }
                        else {
                            if($transaction->reference_id != null) {
                                $transaction->reference_id = null;
                                $transaction->callback_msg = $msg;
                                $transaction->update();

                                $wallet = $transaction->wallet;
                                if($wallet)
                                    $wallet->delete();
                            }
                            $res = $th->user_transaction_result(103);
                            return redirect('/home')->with('payResult', $res);
                        }
                    }
                    catch (\Exception $e) {
                        Activity::save_log('reVerify-50Failed',$msg,(string)$referenceId.'-'.$amount.'-'.$invNum.'-'.$paymentId);
                        $res = $th->user_transaction_result(104);
                        return redirect('/home')->with('payResult', $res);
                    }
                }
                Activity::save_log('verifyFailed',$msg,(string)$referenceId.'-'.$amount.'-'.$invNum.'-'.$paymentId);
                $res = $th->user_transaction_result(105,null,null,$msg);
                return redirect('/home')->with('payResult', $res);
            }
        }
        else {
            $msg = self::messeg($request->resultCode);
            Activity::save_log('callbackFailed',$msg,(string)$referenceId.'-'.$amount.'-'.$invNum.'-'.$paymentId);
            $res = $th->user_transaction_result(105,$invNum,null,$msg);
            $transaction->callback_msg = $msg;
            $transaction->update();
            return redirect('/home')->with('payResult', $res);
        }
    }

    public function week_play(Request $request)
    {
        if (Rbac::check_access('reservation', 'nextweek')) {
            $v = Validator::make($request->json()->all(),[
                'id' => 'required|in:nextWeek,currWeek,prevWeek'
            ]);
            if($v->fails())
                return response()->json(['status' => 101, 'res' => 'مشخصات نامعتبر است']);
            $type = $request->json()->get('id');

            if (!session()->has('currWeek'))
                return response()->json(['status' => 101, 'res' => 'صفحه ای که در آن قرار دارید به علت عدم استفاده منقضی شده است']);


            switch ($type) {
                case 'nextWeek':
                    if (!session()->has('userWeek')) {
                        $currWeek = session('currWeek');
                        $currWeekendTime   = $currWeek['weekendTimestamp'];
                    }
                    else {
                        $currWeek = session('userWeek');
                        $currWeekendTime = $currWeek['weekendTime'];
                    }
                    $userWeek = [
                        'weekbeginTime' => $currWeekendTime + 86400,
                        'weekendTime'   => $currWeekendTime + (7 * 86400),
                    ];
                    break;
                case 'currWeek':
                    $currWeek = session('currWeek');
                    $currWeekbeginTime = $currWeek['weekbeginTimestamp'];
                    $userWeek = [
                        'weekbeginTime' => $currWeekbeginTime,
                        'weekendTime'   => $currWeekbeginTime + (6 * 86400),
                    ];
                    break;
                default:
                    if (!session()->has('userWeek')) {
                        $currWeek = session('currWeek');
                        $currWeekbeginTime = $currWeek['weekbeginTimestamp'];
                    }
                    else {
                        $currWeek = session('userWeek');
                        $currWeekbeginTime = $currWeek['weekbeginTime'];
                    }
                    $userWeek = [
                        'weekbeginTime' => $currWeekbeginTime - (7 * 86400),
                        'weekendTime'   => $currWeekbeginTime - 86400,
                    ];
                    break;
            }

            session()->put('userWeek', $userWeek);


            $data = new \stdClass();
            $data->req_type = 'wb';

            $data2 = new \stdClass();
            $data2->weekbeginTimestamp = $userWeek['weekbeginTime'];

            $wb      = new WeekBox($data);
            $weekBox = $wb->make_menu($data2);

            return response()->json([
                'status' => 200,
                'res'    => $weekBox
            ]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست!!!']);
    }

    public function change_current_user(Request $request)
    {
        //if (Rbac::check_access('reservation', 'nextweek')) {
        //Must be admin
            $v = Validator::make($request->all(), [
                'username' => 'required|string'
            ]);
            if ($v->fails())
                return response()->json(['status' => 101, 'res' => 'مشخصات وارد شده نامعتبر است']);

            $user = User::where('username',$request->username)->orWhere('std_no',$request->username)->first();
            if(!$user)
                return response()->json(['status' => 101, 'res' => 'مشخصات کاربر پیدا نشد']);

            $data = new \stdClass();
            $data->user     = $user;
            $data->req_type = 'change-user';

            $wb = new WeekBox($data);
            $result = $wb->change_user($user);
            return response()->json($result);
        //}
    }

    public function self_change(Request $request)
    {
        $v = Validator::make($request->json()->all(), [
            'type'   => 'required|string|in:col,rest',
            'colId'  => 'nullable|numeric|exists:t_collection,id',
            'restId' => 'nullable|numeric|exists:t_rest,id',
        ]);
        if ($v->fails())
            return response()->json(['status' => 101, 'res' => 'مشخصات وارد شده نامعتبر است']);

        $data = new \stdClass();
        $data->req_type = 'change-self';
        $data->request  = $request;

        $wb = new WeekBox($data);
        $result = $wb->change_self();
        return response()->json($result);
    }
}
