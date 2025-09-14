<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\Library\jdf;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TransactionsController extends Controller
{
    public function pay_gate()
    {
        if(Rbac::check_access('transactions','pay_gate')) {
            $time = time();
            $today = date('Y-m-d H:i:s');
            $thirtyDayAgo = date('Y-m-d H:i:s', $time - (30 * 86400));
            session()->put('beginDateTrans', $thirtyDayAgo);
            session()->put('endDateTrans', $today);
            $trans = Transaction::where('created_at', '>=', $thirtyDayAgo)->orderBy('created_at', 'desc')->get();
            $temp  = [];
            foreach ($trans as $tran) {
                $user = $tran->user;
                $temp[] = (object)[
                    'amount' => $tran->amount,
                    'reference_id' => $tran->reference_id,
                    'created_at' => $tran->GetCreateDate(),
                    'name' => $user->name,
                    'family' => $user->family,
                ];
            }
            return view('cms.transactions.pay_gate', ['trans' => $temp])->with('title', 'لیست تراکنش های درگاه پرداخت (30 روز گذشته)');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function get_by_date(Request $request)
    {
        if(Rbac::check_access('transactions','get_by_date')) {
            $jdf = new jdf();
            $beginDateTrans = $request->beginDateTrans;
            $endDateTrans = $request->endDateTrans;

            $beginDateTrans = explode('-', $beginDateTrans);
            $endDateTrans   = explode('-', $endDateTrans);

            $beginDateTrans = $jdf->jalali_to_gregorian($beginDateTrans[0], $beginDateTrans[1], $beginDateTrans[2]);
            $beginDateTrans = implode('-', $beginDateTrans);

            $endDateTrans = $jdf->jalali_to_gregorian($endDateTrans[0], $endDateTrans[1], $endDateTrans[2]);
            $endDateTrans = implode('-', $endDateTrans);

            session()->put('beginDateTrans', $beginDateTrans);
            session()->put('endDateTrans', $endDateTrans);

            $trans = DB::table('transaction')->where('reference_id','!=',null)->where('reference_id','!=','')->whereBetween('created_at', [$beginDateTrans . ' 00:00:00',$endDateTrans . ' 23:59:59'])->orderBy('id', 'desc')->get();
            $transSum = DB::table('transaction')->where('reference_id','!=',null)->where('reference_id','!=','')->whereBetween('created_at', [$beginDateTrans . ' 00:00:00',$endDateTrans . ' 23:59:59'])->orderBy('id', 'desc')->sum('amount');
            $temp = [];
            foreach ($trans as $tran) {
                $user = User::find($tran->user_id);
                $temp[] = (object)[
                    'amount'       => $tran->amount,
                    'reference_id' => $tran->reference_id,
                    'created_at'   => $jdf->getPersianDate($tran->created_at,'Y-m-d H:i:s'),
                    'name'         => $user->name,
                    'family'       => $user->family,
                ];
            }

            return response()->json([
                'status' => true,
                'res' => $temp,
                'sum1' => $transSum,
            ]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function print_all()
    {
        if(Rbac::check_access('transactions','print_all')) {
            if (!session()->has('beginDateTrans') || !session()->has('endDateTrans'))
                return redirect()->back();

            $jdf = new jdf();
            $trans = DB::table('transaction')->where('reference_id','!=',null)->where('reference_id','!=','')->whereBetween('created_at', [session('beginDateTrans') . ' 00:00:00',session('endDateTrans') . ' 23:59:59'])->orderBy('id', 'desc')->get();
            $transSum = DB::table('transaction')->where('reference_id','!=',null)->where('reference_id','!=','')->whereBetween('created_at', [session('beginDateTrans') . ' 00:00:00',session('endDateTrans') . ' 23:59:59'])->sum('amount');
            $temp = [];
            foreach ($trans as $tran) {
                $user = User::find($tran->user_id);
                $temp[] = (object)[
                    'amount'       => $tran->amount,
                    'reference_id' => $tran->reference_id,
                    'created_at'   => $jdf->getPersianDate($tran->created_at,'Y-m-d H:i:s'),
                    'name'         => $user->name,
                ];
            }


            return view('cms.prints.trans.all', [
                'trans' => $temp,
                'sum1' => $transSum,
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function verify()
    {
        if(Rbac::check_access('transactions','verify')) {
            return view('cms.transactions.verify.verify');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    protected $merchant = '-';

    public function inquiry(Request $request)
    {
        if(Rbac::check_access('transactions','verify')) {
            $type = $request->type;
            switch ($type) {
                case 1:
                    Validator::make($request->all(), [
                        'offset' => 'required|numeric',
                        'limit' => 'required|numeric',
                    ], [
                        'offset.required' => 'وارد کردن از شماره الزامی است',
                        'offset.numeric' => 'مقدار از شماره باید عدد باشد',
                        'limit.required' => 'وارد کردن تعداد الزامی است',
                        'limit.numeric' => 'مقدار تعداد باید عدد باشد',
                    ])->validate();

                    $context = stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ]
                    ]);
                    $client = new \SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version' => SOAP_1_1, 'stream_context' => $context));
                    $params['merchantId'] = $this->merchant;
                    $params['remoteIp']   = $_SERVER['SERVER_ADDR'];
                    $params['offset']     = $request->offset;
                    $params['limit']      = $request->limit;
                    $result = $client->__soapCall("getDailyTransaction", array($params));
                    return view('cms.transactions.verify.report', compact('type', 'result'));
                case 2:
                    Validator::make($request->all(), [
                        'fromDate' => 'required|numeric',
                        'toDate' => 'required|numeric',
                        'offset' => 'required|numeric',
                        'limit' => 'required|numeric',
                    ], [
                        'fromDate.required' => 'وارد کردن از تاریخ الزامی است',
                        'fromDate.numeric' => 'مقدار از تاریخ باید عدد باشد',
                        'toDate.required' => 'وارد کردن تا تاریخ الزامی است',
                        'toDate.numeric' => 'مقدار تا تاریخ باید عدد باشد',
                        'offset.required' => 'وارد کردن از شماره الزامی است',
                        'offset.numeric' => 'مقدار از شماره باید عدد باشد',
                        'limit.required' => 'وارد کردن تعداد الزامی است',
                        'limit.numeric' => 'مقدار تعداد باید عدد باشد',
                    ])->validate();

                    $context = stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ]
                    ]);
                    $client = new \SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version' => SOAP_1_1, 'stream_context' => $context));
                    $params['merchantId'] = $this->merchant;
                    $params['remoteIp']   = $_SERVER['SERVER_ADDR'];
                    $params['offset']     = $request->offset;
                    $params['limit']      = $request->limit;
                    $params['fromDate']   = $request->fromDate;
                    $params['toDate']     = $request->toDate;
                    $result = $client->__soapCall("getOfflineTransaction", array($params));
                    return view('cms.transactions.verify.report', compact('type', 'result'));
                    break;
                case 3:
                    Validator::make($request->all(), [
                        'invoiceNo' => 'required|numeric',
                        'referenceNo' => 'required|numeric',
                    ], [
                        'invoiceNo.required' => 'وارد کردن کد رهگیری الزامی است',
                        'invoiceNo.numeric' => 'مقدار کد رهگیری باید عدد باشد',
                        'referenceNo.required' => 'وارد کردن کد مرجع الزامی است',
                        'referenceNo.numeric' => 'مقدار کد مرجع باید عدد باشد',
                    ])->validate();

                    $context = stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ]
                    ]);
                    $client = new \SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version' => SOAP_1_1, 'stream_context' => $context));
                    $params['merchantId']  = $this->merchant;
                    $params['remoteIp']    = $_SERVER['SERVER_ADDR'];
                    $params['invoiceNo']   = $request->invoiceNo;
                    $params['referenceNo'] = $request->referenceNo;
                    $result = $client->__soapCall("getTransaction", array($params));
                    return view('cms.transactions.verify.report', compact('type', 'result'));
                    break;
                case 4:
                    Validator::make($request->all(), [
                        'invoiceNo' => 'required|numeric',
                        'amount' => 'required|numeric',
                    ], [
                        'invoiceNo.required' => 'وارد کردن کد رهگیری الزامی است',
                        'invoiceNo.numeric' => 'مقدار کد رهگیری باید عدد باشد',
                        'amount.required' => 'وارد کردن مبلغ الزامی است',
                        'amount.numeric' => 'مقدار مبلغ باید عدد باشد',
                    ])->validate();

                    $context = stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ]
                    ]);
                    $client = new \SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version' => SOAP_1_1, 'stream_context' => $context));
                    $params['merchantId'] = $this->merchant;
                    $params['remoteIp']   = $_SERVER['SERVER_ADDR'];
                    $params['invoiceNo']  = $request->invoiceNo;
                    $params['amount']     = $request->amount;
                    $result = $client->__soapCall("getLimitedTransacction", array($params));
                    return view('cms.transactions.verify.report', compact('type', 'result'));
            }
            return redirect()->back()->with('warningMsg', 'انجام فرآیند ناموفق بود');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
