<?php

namespace App\Http\Controllers\Cms;

use App\Card;
use App\Menu;
use App\Facades\Rbac;
use App\Food;
use App\FreeBillNumPrefix;
use App\FreeDdf;
use App\FreeDdo;
use App\FreeQueue;
use App\Library\jdf;
use App\Reservation;
use App\RestInfo;
use App\Role;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    public function define(Request $request)
    {
        $v = Validator::make($request->all(),[
            'credential' => 'required|string',
            'card_hex'   => 'required|string|unique:card,cardUid',
            'user_type'  => 'required|string|in:دانشجو,کارکنان,اساتید,مهمان,آزاد',
        ],[
            'credential.required' => 'مشخصات کاربر را وارد کنید',
            'credential.string'   => 'مشخصات کاربر نامعتبر است',
            'card_hex.required'   => 'شناسه کارت را وارد کنید',
            'card_hex.string'     => 'شناسه کارت نامعتبر است',
            'card_hex.unique'     => 'شناسه کارت تکراری است',
            'user_type.required'  => 'نوع کاربری را انتخاب کنید',
            'user_type.string'    => 'نوع کاربری نامعتبر است',
            'user_type.in'        => 'نوع کاربری نامعتبر است',
        ]);
        if ($v->fails())
            return response()->json(['status' => 101,'res' => $v->errors()]);


        $credential = $request->credential;
        $cardHex    = $request->card_hex;
        $userType   = $request->user_type;

        $user = DB::table('users')
            ->where('username',$credential)
            ->orWhere('std_no',$credential)
            ->orWhere('national_code',$credential)
            ->first();

        if(!$user)
            return response()->json(['status' => 101,'res' => ['credential' => ['مشخصات کاربر پیدا نشد']] ]);


        $existsCard = Card::where('username',$user->username)->first();
        if(!$existsCard) {
            $existsCard = Card::where('username',$user->std_no)->first();
            if(!$existsCard)
                $existsCard = Card::where('username',$user->national_code)->first();
        }

        $checkUniqueUid = Card::where('cardUid',$cardHex)->first();
        if(!isset($existsCard->id)) {
            if($checkUniqueUid)
                $res = "<i class='fa fa-check text-danger'></i> شناسه این کارت برای کاربر دیگیری با شناسه $checkUniqueUid->username ثبت شده است. ";
            else {
                $card = new Card();
                $card->username = $user->username;
                $card->cardUid = $cardHex;
                $card->cardNumber = "-";
                $card->type = $userType;
                $card->save();
                $res = "<i class='fa fa-check text-success'></i> ثبت شد";
            }
        }
        else {
            if($checkUniqueUid && $checkUniqueUid->username != $existsCard->username)
                $res = "<i class='fa fa-check text-danger'></i> شناسه این کارت برای کاربر دیگیری با شناسه $checkUniqueUid->username ثبت شده است. ";
            else {
                $existsCard->cardUid = $cardHex;
                $existsCard->type = $userType;
                $existsCard->update();
                $res = "<i class='fa fa-check text-warning'></i> بروزرسانی شد";
            }
        }
        return response()->json(['status' => 200,'res' => $res ]);
    }

    public function write(Request $request)
    {
        $cardNumber = $request->json()->get('cardNumber');
        $username   = $request->json()->get('username');
        $cardUid    = $request->json()->get('cardUid');
        $userType   = $request->json()->has('type') ? $request->json()->get('type') : 'دانشجو';

        if(!empty($cardNumber) && !empty($username) && !empty($cardUid)) {
            $userCard       = Card::where('username',$username)->first();
            $checkUniqueUid = Card::where('cardUid' ,$cardUid)->first();
            if($userCard) {
                if($checkUniqueUid && $checkUniqueUid->username != $userCard->username)
                    return response()->json([
                        'status' => false,
                        'i1'  => $cardNumber,
                        'i2'  => $username,
                        'i3'  => $cardUid,
                        'res' => "شناسه کارت تکراری و برای کاربر $checkUniqueUid->username ثبت شده است",
                    ]);
                $userCard->cardUid = $cardUid;
                $userCard->type    = $userType;
                $userCard->update();
                return response()->json([
                    'status' => true,
                    'i1' => $cardNumber,
                    'i2' => $username,
                    'i3' => $cardUid,
                    'res'    => 'اطلاعات کارت بروزرسانی شد',
                ]);
            }
            else {
                if($checkUniqueUid)
                    return response()->json([
                        'status' => false,
                        'i1'  => $cardNumber,
                        'i2'  => $username,
                        'i3'  => $cardUid,
                        'res' => "شناسه کارت تکراری و برای کاربر $checkUniqueUid->username ثبت شده است",
                    ]);
                $card = new Card();
                $card->username   = $username;
                $card->cardNumber = $cardNumber;
                $card->cardUid    = $cardUid;
                $card->type       = $userType;
                $card->save();
                return response()->json([
                    'status' => true,
                    'i1' => $cardNumber,
                    'i2' => $username,
                    'i3' => $cardUid,
                    'res'    => 'اطلاعات کارت ثبت شد',
                ]);
            }
        }
        return response()->json([
            'status' => false,
            'i1' => $cardNumber,
            'i2' => $username,
            'i3' => $cardUid,
            'res'    => 'مشخصات ارسالی ناقص است',
        ]);
    }

    public function checking(Request $request)
    {
        $cardNumber = trim($request->json()->get('cardNumber'));
        $cardUid    = trim($request->json()->get('cardUid'));
        $card       = Card::where('cardNumber',$cardNumber)->where('cardUid',$cardUid)->orderBy('id','desc')->first();
        if(!$card)
            return response()->json([
                'name'    => '-',
                'stdNo'   => '-',
                'uid'     => '-',
                'type'    => '-',
                'status'  => '<i class="fa fa-times text-danger"></i>',
                'details' => 'اطلاعات کارت در سیستم پیدا نشد.',
            ]);

        $user = DB::table('users')->where([['username', $card->username],['active', 1]])->orWhere([['std_no', $card->username],['active', 1]])->first();
        if (!$user)
            return response()->json([
                'name'    => '-',
                'stdNo'   => '-',
                'uid'     => '-',
                'type'    => $card->type == "دانشجو" ? "دانشجویی" : "کارکنان",
                'status'  => '<i class="fa fa-user text-warning"></i>',
                'details' => 'کارت در سیستم تعریف شده است اما اطلاعات کاربر پیدا نشد.کاربر بایستی حداقل یکبار با شناسه کاربری خود وارد سیستم تغذیه شود',
            ]);
        return response()->json([
            'name'    => $user->name.' '.$user->family,
            'stdNo'   => $user->std_no,
            'uid'     => $user->username,
            'type'    => $card->type == "دانشجو" ? "دانشجویی" : "کارکنان",
            'status'  => '<i class="fa fa-check text-success"></i>',
            'details' => 'کارت به طور صحیح در سیستم تعریف شده است',
        ]);
    }

    public function check_reserve(Request $request)
    {
        $cardNumber = trim($request->json()->get('cardNumber'));
        $cardUid    = trim($request->json()->get('cardUid'));
        $card       = Card::where('cardUid',$cardUid)->orderBy('created_at','desc')->first();
        if(!$card)
            return response()->json([
                'status' => 101,
                'name'   => '',
                'std_no' => '',
                'res'    => 'اطلاعات کارت پیدا نشد',
            ]);

        $jdf      = new jdf();
        $date     = $jdf->jdate('Y-m-d');
        $clock    = $jdf->jdate('H:i:s');
        $eatenAt  = Date('Y-m-d H:i:s');
        $eatenIp  = \Request::ip();
        $eatenIn  = null;
        $restInfo = RestInfo::where('ip',$eatenIp)->first();
        if($restInfo)
            $eatenIn = $restInfo->id;

        $meal = '';
        if('01:00:00' < $clock && $clock <= '09:30:00')
            $meal = 'صبحانه';
        elseif('09:30:00' < $clock && $clock <= '15:00:00')
            $meal = 'نهار';
        elseif('15:00:00' < $clock && $clock <= '23:00:00')
            $meal = 'شام';

        $user = DB::table('users')->where([['username', $card->username],['active', 1]])->orWhere([['std_no', $card->username],['active', 1]])->first();
        if (!$user)
            return response()->json([
                'status' => 102,
                'name'   => '',
                'std_no' => '',
                'res'    => 'اطلاعات کاربر پیدا نشد'
            ]);

        $img  = $user->img;
        $ddfs = Menu::where('date',$date)->where('meal',$meal)->get();
        if(!isset($ddfs[0]))
            return response()->json([
                'status' => 103,
                'name'   => $user->name,
                'std_no' => $user->std_no,
                'img'    => $img,
                'res'    => 'در این تاریخ وعده غذایی در سیستم تعریف نشده است'
            ]);

        /*if($user->dorm_id == 4)
            return response()->json([
                'status' => 103,
                'name'   => $user->name,
                'std_no' => $user->std_no,
                'img'    => $img,
                'res'    => 'خوابگاه مطهری لطفاً به طبقه بالای سلف مراجعه کنید'
            ]);*/

        foreach ($ddfs as $ddf) {
            $reserve = Reservation::where('user_id',$user->id)->where('menu_id',$ddf->id)->first();
            if($reserve) {
                $foodTitle = $ddf->food_title;
                if(isset($ddf->desserts[0]->id)) {
                    foreach ($ddf->desserts as $dessert)
                        $foodTitle .= ' | '.$dessert->title;
                }

                /*if($user->dorm_id == 4)
                    return response()->json([
                            'status' => 105,
                            'name' => $user->name,
                            'std_no' => $user->std_no,
                            'img' => $img,
                            'res' => 'رزرو داشته و قبلاً استفاده شده است'
                        ]);*/
                if ($reserve->eaten == 1) {
                    $date1 = new \DateTime($reserve->eaten_at);
                    $date2 = new \DateTime(Date('Y-m-d H:i:s'));
                    $totalInterval = $date2->getTimestamp() - $date1->getTimestamp();

                    if((int)$totalInterval < 10) {
                        return response()->json([
                            'status' => 200,
                            'name'   => $user->name,
                            'std_no' => $user->std_no,
                            'img'    => $img,
                            'food'   => $foodTitle,
                            'res'    => 'رزرو دارد'
                        ]);
                    }
                    else {
                        return response()->json([
                            'status' => 105,
                            'name' => $user->name,
                            'std_no' => $user->std_no,
                            'img'  => $img,
                            'food' => $foodTitle,
                            'res'  => $foodTitle.'  -   رزرو داشته و قبلاً استفاده شده است '
                        ]);
                    }
                }

                $reserve->eaten    = 1;
                $reserve->eaten_in = $eatenIn;
                $reserve->eaten_ip = $eatenIp;
                $reserve->eaten_at = $eatenAt;
                $reserve->update();
                return response()->json([
                    'status' => 200,
                    'name'   => $user->name,
                    'std_no' => $user->std_no,
                    'img'    => $img,
                    'food'   => $foodTitle,
                    'res'    => 'رزرو دارد'
                ]);
            }
        }
        return response()->json([
            'status' => 106,
            'name'   => $user->name,
            'std_no' => $user->std_no,
            'img'    => $img,
            'res' => 'رزرو ندارد'
        ]);
    }

    public function free_check_reserve(Request $request)
    {
        /*$trustedIps = [
            //
        ];
        $ip = \Request::ip();
        if(!in_array($ip,$trustedIps))
            return response()->json([
                'status' => 101,
                'name'   => '',
                'std_no' => '',
                'res'    => 'اطلاعات کارت پیدا نشد',
            ]);*/

        $cardNumber = trim($request->json()->get('cardNumber'));
        $cardUid    = trim($request->json()->get('cardUid'));
        $queueName  = $request->json()->has('queueName')? trim($request->json()->get('queueName')):0;
        $card       = Card::where('cardNumber',$cardNumber)->where('cardUid',$cardUid)->orderBy('id','desc')->first();

        $warnMsg = "<div class='card text-center'>
                        <div class='card-header text-center'> اخطار</div>
                        <div class='card-body'>";

        if(!$card) {
            $warnMsg .= "<strong>اطلاعات کارت پیدا نشد</strong></div></div>";
            return response()->json([
                'status' => 101,
                'res'    => $warnMsg,
            ]);
        }

        $jdf   = new jdf();
        $date  = $jdf->jdate('Y-m-d');
        $clock = $jdf->jdate('H:i:s');

        $meal = 0;
        if('01:00:00' < $clock && $clock <= '09:30:00')
            $meal = 'صبحانه';
        elseif('09:30:00' < $clock && $clock <= '16:00:00')
            $meal = 'نهار';
        elseif('16:00:00' < $clock && $clock <= '23:00:00')
            $meal = 'شام';

        $user = DB::table('users')->where([['username', $card->username],['active', 1]])->orWhere([['std_no', $card->username],['active', 1]])->first();
        if (!$user) {
            $warnMsg .= "<strong>کارت تعریف شده است اما کاربر تاکنون از اتوماسیون تغذیه استفاده نکرده است</strong></div></div>";
            return response()->json([
                'status' => 101,
                'res'    => $warnMsg
            ]);
        }

        $ddfs = Menu::where('date',$date)->where('meal',$meal)->get();
        if(!isset($ddfs[0]->id)) {
            $warnMsg .= "<p>نام: <strong>$user->name</strong></p>".
                        "<p>شماره دانشجویی: <strong>$user->std_no</strong></p>".
                        "<p>کد ملی: <strong>$user->national_code</strong></p>".
                        "<p><img src='$user->img' width='120' height='120'></p>".
                        "<strong>منو غذایی برای امروز در سیستم تعریف نشده است</strong>".
                        "</div>".
                        "</div>";
            return response()->json([
                'status' => 101,
                'res'    => $warnMsg
            ]);
        }

        $saleDayObj  = new SaledayController();
        $markAsEaten = 1;
        $order       = $saleDayObj->get_free_user_order($user,$ddfs,null,$date,$markAsEaten,$clock);

        $queue = FreeQueue::where('user_id',$user->id)->where('date',$date)->where('meal',$meal)->first();
        if(!$queue) {
            $billPrefix = FreeBillNumPrefix::where('date',$date)->first();
            if(!$billPrefix) {
                $newBillPrefix = new FreeBillNumPrefix();
                $prefix = $newBillPrefix->random_string(mt_rand(4,10));
                $newBillPrefix->date   = $date;
                $newBillPrefix->prefix = $prefix;
                $newBillPrefix->save();
            }
            else
                $prefix = $billPrefix->prefix;


            /*$lastQueue = FreeQueue::where('date',$date)->orderBy('id','desc')->first();
            if(!$lastQueue)
                $bill_number = $prefix.'-1';
            else {
                $bill_number = (string)((int)explode('-',$lastQueue->bill_number)[1] + 1);
                $bill_number = $prefix.'-'.$bill_number;
            }*/
            $bill_number = $prefix.'-'.time();


            $fq = new FreeQueue();
            $fq->queue_name  = $queueName;
            $fq->user_id     = $user->id;
            $fq->date        = $date;
            $fq->meal        = $meal;
            $fq->bill_number = $bill_number;
            $fq->save();

            $freeQ = FreeQueue::find($fq->id);
            $view  = $freeQ->prepared_view();
            $freeQ->orders = $view;
            $freeQ->update();
        }

        return response()->json([
            'status' => 200,
            'res'    => $order,
        ]);
    }

    public function check_reserve_barcode(Request $request)
    {
        $stdNumber = trim($request->json()->get('std_no'));

//        $card       = Card::where('cardNumber',$cardNumber)->orderBy('created_at','desc')->first();
//        if(!$card)
//            return response()->json([
//                'status' => 101,
//                'name'   => '',
//                'std_no' => '',
//                'res'    => 'اطلاعات کارت پیدا نشد',
//            ]);

        $jdf  = new jdf();
        $date  = $jdf->jdate('Y-m-d');
        $clock = $jdf->jdate('H:i:s');

        $meal = '';
        if('01:00:00' < $clock && $clock <= '08:30:00')
            $meal = 'صبحانه';
        elseif('08:30:00' < $clock && $clock <= '15:00:00')
            $meal = 'نهار';
        elseif('15:00:00' < $clock && $clock <= '23:00:00')
            $meal = 'شام';

        $user = User::where('std_no', $stdNumber)->first();
        if (!$user)
            return response()->json([
                'status' => 102,
                'name'   => '',
                'std_no' => '',
                'res'    => 'اطلاعات کاربر پیدا نشد'
            ]);

        $img = '';
        $curl = curl_init();
        $url = "https://auth.ilam.ac.ir/v2/info/976dafb0e37940029caf0a73da1b9c60/" . $user->username;
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => False,
        ));
        $res = json_decode(curl_exec($curl));
        curl_close($curl);
        if($res) {
            if ($res->return->message == 'User Found')
                $img = $res->student->img;
        }

        $ddfs = Menu::where('date',$date)->where('meal',$meal)->where('active',1)->get();
        if(!$ddfs)
            return response()->json([
                'status' => 103,
                'name'   => $user->name,
                'std_no' => $user->std_no,
                'img'    => $img,
                'res'    => 'در این تاریخ وعده غذایی در سیستم تعریف نشده است'
            ]);

        foreach ($ddfs as $ddf) {
            $reserve = Reservation::where('user_id',$user->id)->where('ddf_id',$ddf->id)->first();
            if($reserve) {
                if($reserve->expired == 1)
                    return response()->json([
                        'status' => 104,
                        'name'   => $user->name,
                        'std_no' => $user->std_no,
                        'img'    => $img,
                        'res'    => 'تاریخ مجاز استفاده از غذا به پایان رسیده است'
                    ]);
                $foodTitle = Food::find($ddf->food_id)->title;
                if ($reserve->eaten == 1) {
                    $date1 = new \DateTime($reserve->updated_at);
                    $date2 = new \DateTime(Date('Y-m-d H:i:s'));
                    $intervalDay   = (int)($date1->diff($date2)->format('%R%a'));
                    $intervalHour  = (int)($date1->diff($date2)->format('%R%h'));
                    $intervalMin   = (int)($date1->diff($date2)->format('%R%i'));
                    $intervalSec   = (int)($date1->diff($date2)->format('%R%s'));
                    $totalInterval = ($intervalDay * 1440 * 60)+ ($intervalHour * 60 * 60) + ($intervalMin * 60) + $intervalSec;
                    if((int)$totalInterval < 30) {
                        return response()->json([
                            'status' => 200,
                            'name'   => $user->name,
                            'std_no' => $user->std_no,
                            'img'    => $img,
                            'food'   => $foodTitle,
                            'res'    => 'رزرو دارد'
                        ]);
                    }
                    else {
                        return response()->json([
                            'status' => 105,
                            'name' => $user->name,
                            'std_no' => $user->std_no,
                            'img' => $img,
                            'res' => 'رزرو داشته و قبلاً استفاده شده است'
                        ]);
                    }
                }
                $reserve->eaten = 1;
                $reserve->update();
                return response()->json([
                    'status' => 200,
                    'name'   => $user->name,
                    'std_no' => $user->std_no,
                    'img'    => $img,
                    'food'   => $foodTitle,
                    'res'    => 'رزرو دارد'
                ]);
            }
        }
        return response()->json([
            'status' => 106,
            'name'   => $user->name,
            'std_no' => $user->std_no,
            'img'    => $img,
            'res' => 'رزرو ندارد'
        ]);
    }

    public function index()
    {
        if (!Rbac::check_access('card', 'card'))
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نیست');

        $cardCount = Card::count();
        $cards = Card::orderBy('id','desc')->paginate(30);
        return view('cms.card.index',compact('cardCount','cards'));
    }

    public function delete($id)
    {
        if (!Rbac::check_access('card', 'delete_card'))
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نیست');

        $v = Validator::make(['id' => $id,],[
            'id' => 'required|numeric|exists:card,id',
        ]);
        if($v->fails())
            return redirect()->back()->with('warningMsg','مشخصات نامعتبر است');

        $card = Card::find($id);
        $card->delete();
        return redirect()->back()->with('successMsg','مشخصات کارت از سیستم حذف شد');
    }

    public function search(Request $request)
    {
        if (!Rbac::check_access('card', 'جست و جو کارت'))
            return response()->json(['status' => 101,'res'=>'دسترسی شما به این بخش امکان پذیر نمی باشد']);

            $searchTxt = $request->get('std_or_uid');
            $validator = Validator::make($request->all(), [
                'std_or_uid' => 'required|string',
            ]);
            if ($validator->fails())
                return response()->json(['status' => 101, 'res' => 'اطلاعات ورودی نامعتبر است']);

            $cards = Card::where('username',$searchTxt)->get();
            if(!$cards) {
                $cards = Card::where('username',$searchTxt)->get();
            }

            $htmlView = "<div class=\"table-responsive\">
                <table class=\"table table-striped table-bordered table-sm\">
                    <thead>
                    <tr>
                        <th class=\"text-center\">#</th>
                        <th class=\"text-right\">نام کاربری</th>
                        <th class=\"text-right\">شماره اختصاصی</th>
                        <th class=\"text-right\">کد hex</th>
                        <th class=\"text-right\">نوع</th>
                        <th class=\"text-right\">ثبت</th>
                        <th class=\"text-right\">آخرین بروزرسانی</th>
                        <th class=\"text-center\"></th>
                    </tr>
                    </thead>
                    <tbody>";

            if(!isset($cards[0]->id)) {
                $htmlView .= "<tr>
                                <td colspan='8' class=\"text-center\">مشخصات کارت پیدا نشد</td>
                                </tr>";
                $htmlView .= "</tbody></table></div>";
                return response()->json(['status' => 200, 'res' => $htmlView]);
            }

            foreach ($cards as $i=>$card) {
                $createdAt = $card->created_at();
                $updatedAt = $card->updated_at();
                $htmlView .= "<tr>
                            <td class='text-center'>$i</td>
                            <td class='text-right'>$card->username</td>
                            <td class='text-right'>$card->cardNumber</td>
                            <td class='text-right'>$card->cardUid</td>
                            <td class='text-right'>$card->type</td>
                            <td class='text-right'>$createdAt</td>
                            <td class='text-right'>$updatedAt</td>
                            <td class='text-center'><a class='btn btn-light' href='/home/card/delete/$card->id'><i class='fa fa-trash'></i></a></td>
                        </tr>";
            }
        $htmlView .= "</tbody></table></div>";
        return response()->json(['status' => 200, 'res' => $htmlView]);
    }

    public function eatened_counter()
    {
        $jdf  = new jdf();
        $date  = $jdf->jdate('Y-m-d');
        $clock = $jdf->jdate('H:i:s');

        $meal = '';
        if('01:00:00' < $clock && $clock <= '09:30:00')
            $meal = 'صبحانه';
        elseif('09:30:00' < $clock && $clock <= '15:00:00')
            $meal = 'نهار';
        elseif('15:00:00' < $clock && $clock <= '23:00:00')
            $meal = 'شام';

        $reserves = Reservation::where('date', $date)
            ->where('meal', $meal)
            ->where('eaten', 1)
            ->get();
        $reserves = collect($reserves);

        $maleCounter   = $reserves->where('sex', 2)->count();
        $femaleCounter = count($reserves) - $maleCounter;

        return response()->json([
            'status' => 200,
            'male'   => $maleCounter,
            'female' => $femaleCounter,
        ]);
    }

    public function free_eatened_counter()
    {
        $jdf  = new jdf();
        $date  = $jdf->jdate('Y-m-d');
        $clock = $jdf->jdate('H:i:s');

        $meal = '';
        if('01:00:00' < $clock && $clock <= '09:30:00')
            $meal = 'صبحانه';
        elseif('09:30:00' < $clock && $clock <= '15:00:00')
            $meal = 'نهار';
        elseif('15:00:00' < $clock && $clock <= '23:00:00')
            $meal = 'شام';


        return response()->json([
            'status' => 200,
            'male'   => 0,
            'female' => 0,
        ]);
    }
}
