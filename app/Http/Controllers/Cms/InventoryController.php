<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\Transaction;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(Rbac::check_access('inventory','index')) {
            return view('cms.inventory.index');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
    public function search(Request $request)
    {
        if(Rbac::check_access('inventory','search')) {
            $identify = trim($request->json()->get('identify'));
            $validator = Validator::make(['identify' => $identify], [
                'identify' => 'required|string',
            ]);
            if ($validator->fails())
                return response()->json(['status' => 101, 'res' => 'اطلاعات ورودی نامعتبر است']);
            $user = User::where('username', $identify)->where('active', 1)->first();
            if (!$user) {
                $user = User::where('std_no', $identify)->where('active', 1)->first();
                if (!$user)
                    return response()->json(['status' => 102, 'res' => 'اطلاعات کاربر پیدا نشد']);
            }

            $wallet = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $tbl = '<div class="table-responsive">
                <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th class="text-center">نام</th>
                    <th class="text-center">شماره دانشجویی</th>
                    <th class="text-center">موجودی</th>
                </tr>
                </thead>
                <tbody>';
            $tbl .= '<tr>
                    <td  class="text-center">' . $user->name . '</td><td class="text-center">' . $user->std_no . '</td>';
            if (!$wallet) {
                $tbl .= '<td  class="text-center ltr" id="tblAmount">0</td></tr></tbody></table>';
                return response()->json(['status' => 103, 'res' => $tbl, 'userId' => $user->id]);
            }
            $tbl .= '<td class="text-center ltr" id="tblAmount">' . $wallet->amount . '</td></tr></tbody></table></div>';
            $pgTbl = '<div class="table-responsive">
                          <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">id</th>
                                <th class="text-center">مبلغ</th>
                                <th class="text-center">کد مرجع فرعی</th>
                                <th class="text-center">شماره فاکتور</th>
                                <th class="text-center">تاریخ</th>
                            </tr>
                            </thead>
                            <tbody>';
            $trans = Transaction::where('user_id',$user->id)->get();
            foreach ($trans as $i=>$tran) {
                $pgTbl .= '<tr>
                               <td class="text-center">'.$i.'</td>
                               <td class="text-center">'.$tran->id.'</td>
                               <td class="text-center">'.$tran->amount.'</td>
                               <td class="text-center">'.$tran->reference_id.'</td>
                               <td class="text-center">'.$tran->invoiceNumber.'</td>
                               <td class="text-center">'.$tran->GetCreateDate().'</td>
                           </tr>';
            }
            $pgTbl .= '</tbody></table></div>';

            return response()->json([
                'status' => 104,
                'res' => $tbl,
                'userId' => $user->id,
                'trans' => $pgTbl,
            ]);
        }
        return response()->json(['status' => 105, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function add_wallet_amount(Request $request)
    {
        if(Rbac::check_access('inventory','add_wallet_amount')) {
            $amount    = $request->json()->get('amount');
            $billId    = $request->json()->get('billId');
            $trackCode = $request->json()->get('trackCode');
            $tId       = $request->json()->get('tId');
            $desc      = $request->json()->get('desc');
            $userId    = $request->json()->get('userId');

            $validator = Validator::make([
                'amount'    => $amount,
                'billId'    => $billId,
                'trackCode' => $trackCode,
                'tId'       => $tId,
                'desc'      => $desc,
                'userId'    => $userId,
            ], [
                'amount'    => 'required|numeric',
                'billId'    => 'nullable|string',
                'trackCode' => 'nullable|numeric',
                'tId'       => 'nullable|numeric|exists:transaction,id',
                'desc'      => 'required|string',
                'userId'    => 'required|numeric|exists:users,id',
            ]);
            if ($validator->fails())
                return response()->json(['status' => 101, 'errors' => $validator->errors()]);

            if(is_numeric($tId)) {
                $transaction = Transaction::where('reference_id', $trackCode)->first();
                if ($transaction)
                    return response()->json(['status' => 105, 'res' => 'کد مرجع قبلاً ثبت شده است']);

                $transaction = Transaction::where('id', $tId)->where('user_id', $userId)->first();
                if (!$transaction)
                    return response()->json(['status' => 105, 'res' => 'اطلاعات تراکنش نامعتبر است']);
                $transaction->reference_id = $trackCode;
                $transaction->update();
            }

            $wallet = Wallet::where('user_id', $userId)->orderBy('id', 'desc')->first();
            $prevAmount = 0;
            if($wallet)
                $prevAmount = $wallet->amount;

            if(!empty($billId))
                $desc .= " شناسه قبض بانکی: $billId ";

            $wl = new Wallet();
            $wl->amount    = $prevAmount + $amount;
            $wl->value     = $amount;
            $wl->_for      = $desc;
            $wl->operation = 1;
            $wl->user_id   = $userId;
            $wl->save();
            if(isset($transaction)) {
                $transaction->wallet_id = $wl->id;
                $transaction->update();
            }

            return response()->json([
                'status' => 200,
                'res'    => 'افزایش موجودی با موفقیت ثبت شد',
                'amount' => $wl->amount
            ]);
        }
        return response()->json(['status' => 105, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function sub_wallet_amount(Request $request)
    {
        if (Rbac::check_access('inventory', 'add_wallet_amount')) {
            $amount = $request->json()->get('amount');
            $desc = $request->json()->get('desc');
            $userId = $request->json()->get('userId');

            $validator = Validator::make([
                'amount' => $amount,
                'desc' => $desc,
                'userId' => $userId,
            ], [
                'amount' => 'required|numeric',
                'desc' => 'required|string',
                'userId' => 'required|numeric|exists:users,id',
            ]);
            if ($validator->fails())
                return response()->json(['status' => 101, 'errors' => $validator->errors()]);

            $wallet = Wallet::where('user_id', $userId)->orderBy('id', 'desc')->first();

            $prevAmount = $wallet->amount;
            $wl = new Wallet();
            $wl->amount    = ($prevAmount) - ($amount);
            $wl->value     = $amount;
            $wl->_for      = $desc;
            $wl->operation = 0;
            $wl->user_id   = $userId;
            $wl->save();

            return response()->json(['status' => 200, 'res' => 'کسر از موجودی با موفقیت ثبت شد', 'amount' => $prevAmount - $amount]);
        }
        return response()->json(['status' => 105, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }
}
