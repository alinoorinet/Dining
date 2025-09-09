<?php

namespace App\Http\Controllers\Cms;

use App\Card;
use App\Library\WeekBox;
use App\Menu;
use App\Facades\Activity;
use App\Food;
use App\Dorm;
use App\Facades\Rbac;
use App\FreeBillNumPrefix;
use App\FreeDdf;
use App\FreeDdo;
use App\FreeFoodPrice;
use App\FreeQueue;
use App\FreeReservation;
use App\FreeReservationOpt;
use App\FreeUserGroup;
use App\Library\jdf;
use App\Library\Resreport;
use App\Notification;
use App\Reservation;
use App\Rest;
use App\RestInfo;
use App\Setting;
use App\User;
use App\UserGroup;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    static public function reserves_computing($beginDate, $endDate)
    {
        $jdf   = new jdf();
        $dates = $jdf->date_interval($beginDate,$endDate);
        $today = $jdf->jdate('d F Y ساعت H:i');

        $meals   = config('app.meals');
        $tmpView = "";

        foreach ($dates as $date => $day) {
            $backImg = "'/img/brand/logo.png'";
            $tmpView .= "<div class='row'>
                            <div class='col-sm-12'>
                                <div class='card mb-3 text-center'>
                                    <div class='card-body' style='background-image: $backImg'>
                                        <div class='card-title bg-light text-dark'><b>$date - $day</b></div>";
            foreach ($meals as $meal) {
                $ddfs       = Menu::where('date', $date)->where('meal', $meal)->get();
                $collectDDF = collect($ddfs);
                $ddfsId     = $collectDDF->map(function ($data){
                    return $data->id;
                });
                if(count($ddfsId) < 1)
                    continue;

                $tmpView .= "<div class='row'>
                                 <div class='col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                 <div class='card-deck mb-5'>
                                     <div class='card'>
                                         <div class='card-header pt-1 pb-1'>
                                             <span class='float-right'>وعده: <b>$meal</b></span>
                                             <div class='badge badge-light float-left p-2'>تاریخ گزارش گیری : $today</div>
                                             <button class='btn btn-secondary btn-sm pt-1 mb-0 mr-1 ml-1 print-btn float-left'><i class='fa fa-print'></i></button>
                                             <button class='btn btn-sm pt-1 mb-0 mr-1 ml-1 dorm-btn float-left' style='background: #4cd4ea' date='$date' meal='$meal' >آمار خوابگاه</button>
                                         </div>
                                         <div class='card-body table-responsive'>
                                             <table class='table table-bordered rad8x my-border table-sm' style='background-color: #ededed45'>
                                                 <thead>
                                                 <tr>
                                                     <th class='text-center align-middle' rowspan='2'>ردیف</th>
                                                     <th class='text-center align-middle' rowspan='2'>منو</th>
                                                     <th class='text-center' colspan='3' style='background: #f690af'>خواهران</th>
                                                     <th class='text-center' colspan='3' style='background: #f56994'>برادران</th>
                                                     <th class='text-center' colspan='3' style='background: #f6326e'>کل</th>
                                                 </tr>
                                                 <tr>
                                                     <th class='text-center text-dark'>رزرو</th>
                                                     <th class='text-center text-dark'>مصرف</th>
                                                     <th class='text-center text-dark'>مازاد</th>
                                                     <th class='text-center text-dark'>رزرو</th>
                                                     <th class='text-center text-dark'>مصرف</th>
                                                     <th class='text-center text-dark'>مازاد</th>
                                                     <th class='text-center text-dark'>رزرو</th>
                                                     <th class='text-center text-dark'>مصرف</th>
                                                     <th class='text-center text-dark'>مازاد</th>
                                                 </tr>
                                                 </thead>
                                                 <tbody>";

                $reserves   = Reservation::where('date', $date)
                    ->whereIn('menu_id', $ddfsId)->get();
                $reserves      = collect($reserves);
                $foodsGroup    = $reserves->groupBy('food_title');

                $resCount   = 0;
                $eatCount   = 0;
                $pertCount  = 0;
                $bResCount  = 0;
                $bEatCount  = 0;
                $bPertCount = 0;

                $index = 1;
                foreach ($foodsGroup->toArray() as $foodTitle => $foodGroup) {
                    $newCollect = collect($foodGroup);
                    $countX     = count($foodGroup);
                    $bCountX    = $newCollect->where('sex', 2)->count();
                    $gCountX    = $countX - $bCountX;

                    $eatCountX  = $newCollect->where('eaten', 1)->count();
                    $eatCountXB = $newCollect->where('sex', 2)->where('eaten', 1)->count();
                    $eatCountXG = $eatCountX - $eatCountXB;

                    $pertX      = $countX  - $eatCountX;
                    $pertXB     = $bCountX - $eatCountXB;
                    $pertXG     = $gCountX - $eatCountXG;

                    $resCount   += $countX;
                    $bResCount  += $bCountX;
                    $pertCount  += $pertX;
                    $eatCount   += $eatCountX;
                    $bEatCount  += $eatCountXB;
                    $bPertCount += $pertXB;

                    $tmpView .= "<tr>
                                     <td class='text-center'>$index</td>
                                     <td class='text-center'><b>$foodTitle</b></td>
                                     <td class='text-center'>$gCountX</td>
                                     <td class='text-center'>$eatCountXG</td>
                                     <td class='text-center'>$pertXG</td>
                                     <td class='text-center'>$bCountX</td>
                                     <td class='text-center'>$eatCountXB</td>
                                     <td class='text-center'>$pertXB</td>
                                     <td class='text-center'>$countX</td>
                                     <td class='text-center'>$eatCountX</td>
                                     <td class='text-center'>$pertX</td>
                                 </tr>";
                    $index++;
                }
                $gResCount  = $resCount  - $bResCount;
                $gEatCount  = $eatCount  - $bEatCount;
                $gPertCount = $pertCount - $bPertCount;

                $tmpView .= "<tr style='background: #d0cfcf'>
                                 <td class='text-center' colspan='2'><b>مجموع</b></td>
                                 <td class='text-center'><b>$gResCount</b></td>
                                 <td class='text-center'><b>$gEatCount</b></td>
                                 <td class='text-center'><b>$gPertCount</b></td>
                                 <td class='text-center'><b>$bResCount</b></td>
                                 <td class='text-center'><b>$bEatCount</b></td>
                                 <td class='text-center'><b>$bPertCount</b></td>
                                 <td class='text-center'><b>$resCount</b></td>
                                 <td class='text-center'><b>$eatCount</b></td>
                                 <td class='text-center'><b>$pertCount</b></td>
                             </tr>
                             </tbody></table>
                             <div class='dorm-view'></div>
                             </div></div></div></div></div>";
            }
            $tmpView .= "<p class='mt-2'><strong class='float-left ml-5'>محل امضای مسئول تغذیه</strong></p>";
            $tmpView .= "</div></div></div></div>";
        }

        return [
            'view' => $tmpView,
        ];
    }

    static public function reserves_dorm_computing($date, $meal)
    {
        $ddfs       = Menu::where('date', $date)->where('meal', $meal)->get();
        $collectDDF = collect($ddfs);
        $ddfsId     = $collectDDF->map(function ($data){
            return $data->id;
        });
        if(count($ddfsId) < 1)
            return "";

        $dorms       = Dorm::orderBy('id')->get();
        $dormsCount  = count($dorms);
        $dormsCountP = $dormsCount + 1;
        $dormView   = "";

        $tmpView = "<div class='table-responsive'>
                             <table class='table table-bordered rad8x my-border table-sm' style='background-color: #ededed45'>
                                 <thead>
                                 <tr>
                                     <th class='text-center align-middle' rowspan='3'>ردیف</th>
                                     <th class='text-center align-middle' rowspan='3'>منو</th>
                                     <th colspan='$dormsCountP' class='text-center'>تعداد رزرو خوابگاه</th>
                                 </tr>";
        foreach ($dorms as $dorm)
            $dormView .= "<th class='text-center text-dark'>$dorm->title</th>";

        $tmpView .= "<tr style='background: #4cd4ea'>
                         $dormView
                         <th class='text-center text-dark'>مجموع</th>
                     </tr>";
        $tmpView .= "</thead>
                     <tbody>";

        $reserves   = Reservation::where('date', $date)
            ->whereIn('menu_id', $ddfsId)->get();
        $reserves   = collect($reserves);
        $foodsGroup = $reserves->groupBy('food_title');

        $index = 1;
        $sumY  = [];
        foreach ($foodsGroup->toArray() as $foodTitle => $foodGroup) {
            $newCollect = collect($foodGroup);
            $dormView   = "";
            $sumX       = 0;

            foreach ($dorms as $dorm) {
                $countX    = $newCollect->where('dorm_id', $dorm->id)->count();
                $sumX     += $countX;
                $sumY[$dorm->id] = isset($sumY[$dorm->id]) ? $sumY[$dorm->id] + $countX : $countX;
                $dormView .= "<td class='text-center'>$countX</td>";
            }

            $tmpView .= "<tr>
                             <td class='text-center align-middle'>$index</td>
                             <td class='text-center align-middle'><b>$foodTitle</b></td>
                             $dormView
                             <td class='text-center'><b>$sumX</b></td>
                         </tr>";
            $index++;
        }

        $dormView = 0;
        $sumXY    = 0;
        foreach ($sumY as $dormId => $dormCount) {
            $sumXY    += $dormCount;
            $dormView .= "<td class='text-center'>$dormCount</td>";
        }
        $tmpView .= "<tr style='background: #d0cfcf'>
                         <td class='text-center' colspan='2'><b>مجموع</b></td>
                         $dormView
                         <td class='text-center'><b>$sumXY</b></td>
                     </tr>
                     </tbody></table></div>";

        return $tmpView;
    }

    public function total(Request $request)
    {
        if(Rbac::check_access('reserves','get_by_date')) {
            $beginDate = $request->beginDate;
            $endDate    = $request->endDate;

            session()->put('resReportBeginDate', $beginDate);
            session()->put('resReportEndDate', $endDate);

            $result = self::reserves_computing($beginDate, $endDate);
            return response()->json([
                'status' => true,
                'view'   => $result['view'],
            ]);
        }
        return response()->json([
            'status' => false,
            'res'    => 'دسترسی  شما به این بخش امکان پذیر نیست',
        ]);
    }

    public function total_dorm(Request $request)
    {
        if(Rbac::check_access('reserves','get_by_date')) {
            $meals    = config('app.meals');
            $mealsStr = implode(',', array_values($meals));
            $v = Validator::make($request->all(),[
                'date'      => 'required|date',
                'meal'      => 'required|in:'.$mealsStr,
            ]);
            if($v->fails())
                return response()->json(['status' => 102, 'res' => 'اطلاعات ورودی نامعتبر است']);

            $date = $request->date;
            $meal = $request->meal;

            $result = self::reserves_dorm_computing($date, $meal);
            return response()->json([
                'status' => 200,
                'view'   => $result,
            ]);
        }
        return response()->json([
            'status' => 102,
            'res'    => 'دسترسی  شما به این بخش امکان پذیر نیست',
        ]);
    }

    public function index()
    {
        if(Rbac::check_access('reserves','index'))
            return view('cms.reserves.index');
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function guests()
    {
        if(Rbac::check_access('reserves','guests')) {
            return view('cms.reserves.guests.index');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function guests_data(Request $request)
    {
        if(Rbac::check_access('reserves','guests_data')) {
            $beginDate = $request->beginDate;
            $endDate = $request->endDate;
            $meal = $request->meal;
            $sex = $request->sex;
            $rr = new Resreport([$sex,]);
            $res = $rr->make_report($meal, $beginDate, $endDate, 4, '');
            $res = json_decode($res);
            return response()->json(['status' => true, 'res' => $res]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function mode_2()
    {
        if(Rbac::check_access('reserves','mode_2')) {
            $dorms = Dorm::all();
            $meals   = config('app.meals');
            return view('cms.reserves.mode-2.index', compact('dorms', 'meals'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function mode_2_data(Request $request)
    {
        if(Rbac::check_access('reserves','mode_2_data')) {
            $beginDate = $request->beginDate;
            $endDate = $request->endDate;
            $dormId = $request->dormId;
            $meal = $request->meal;
            $sex = $request->sex;
            session([
                'beginDate' => $beginDate,
                'endDate' => $endDate,
                'dormId' => $dormId,
                'meal' => $meal,
                'sex' => $sex,
            ]);
            $rr = new Resreport([$sex,]);
            $res = $rr->make_report($meal, $beginDate, $endDate, 2, $dormId);
            $res = json_decode($res);
            return response()->json(['status' => true, 'res' => $res]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function manual_check()
    {
        if(Rbac::check_access('reserves','manual_check')) {
            $jdf = new jdf();
            $today = $jdf->jdate('Y-m-d');

            $meals   = config('app.meals');
            return view('cms.reserves.check-reserve.index', compact('today', 'meals'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function check_reserve(Request $request)
    {
        if (Rbac::check_access('reserves', 'check_reserve')) {
            $meals    = config('app.meals');
            $mealsStr = implode(',', array_values($meals));
            $v = Validator::make($request->json()->all(),[
                'uid'       => 'required|string',
                'date'      => 'nullable|date',
                'meal'      => 'nullable|in:'.$mealsStr,
                'read_type' => 'required|in:manual,rfid',
                'mark_as_eaten' => 'required|in:0,1',
            ]);
            if($v->fails())
                return response()->json(['status' => false, 'res' => 'اطلاعات ورودی نامعتبر است']);

            $jdf      = new jdf();
            $readType = $request->json()->get('read_type');
            $uid      = $request->json()->get('uid');
            if($readType == "rfid") {
                $clock = $jdf->jdate('H:i:s');
                $date  = $jdf->jdate('Y-m-d');

                $meal  = 'میان وعده';
                if('01:00:00' < $clock && $clock <= '09:30:00')
                    $meal = 'صبحانه';
                elseif('09:30:00' < $clock && $clock <= '15:00:00')
                    $meal = 'نهار';
                elseif('15:00:00' < $clock && $clock <= '23:00:00')
                    $meal = 'شام';

                $card = Card::where('cardUid',$uid)->first();
                if(!$card)
                    return response()->json(['status' => false, 'res' => 'مشخصات کارت پیدا نشد']);
                $user = User::where('username',$card->username)
                    ->orWhere('std_no',$card->username)
                    ->orWhere('national_code',$card->username)
                    ->first();
                if(!$user)
                    return response()->json(['status' => false, 'res' => 'اطلاعات کاربر پیدا نشد']);
                if($user->active == 0)
                    return response()->json(['status' => false, 'res' => 'حساب کاربری کاربر غیر فعال است']);
            }
            else {
                $clock = $jdf->jdate('H:i:s');
                $date  = $request->json()->get('date');
                $meal  = $request->json()->get('meal');

                $user = User::where('std_no', $uid)
                    ->orWhere('national_code', $uid)
                    ->first();
                if (!$user)
                    return response()->json(['status' => false, 'res' => 'اطلاعات کاربر پیدا نشد']);
                if($user->active == 0)
                    return response()->json(['status' => false, 'res' => 'حساب کاربری کاربر غیر فعال است']);
            }

            $eatenAt  = $date.' '.$clock;
            $eatenIp  = \Request::ip();
            $eatenIn  = null;
            $restInfo = RestInfo::where('ip',$eatenIp)->first();
            if($restInfo)
                $eatenIn = $restInfo->id;

            $dateTime = $jdf->jdate('H:i Y-m-d');

            $userRole = Rbac::user_role($user);
            if($userRole == 'super-admin' || $userRole == 'developer')
                $userRests = Rest::all();
            else
                $userRests = $user->rests;

            $userRestsCollect = collect($userRests);
            $userRestsIds     = $userRestsCollect->map(function ($value,$key){
                return $value->id;
            });
            if(empty($userRestsIds))
                return response()->json(['status' => false, 'res' => 'دسترسی کاربر به هیچکدام از رستوران ها تعیین نشده است']);

            $ddfs = Menu::where('date', $date)
                ->where('meal', $meal)
                ->whereIn('rest_id', $userRestsIds)
                ->get();

            $userImg    = $user->img != "" ? $user->img : "/img/prof-default.png";
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
            $billNumber = $prefix.'-'.time();

            $orderView = "<div class='row'>".
                "   <div class='col-xl-8 col-lg-8 col-md-8 col-sm-8 col-12' id='order-tbl'>".
                "       <div class='card w-100'>".
                "           <div class='card-header' style='font-family: BPersianGulf; font-size: 13px; text-align: center'><img src='/img/logo.png' width='90%'> </div>".
                "           <div class='card-body'>".
                "               <div class='table-responsive'>".
                "                  <table class='table table-striped table-bordered table-sm' border='2' dir='rtl' style='font-family: IRANYekanWeb; font-size: 13px; width: 100%'>".
                "                      <thead>".
                "                      <tr style='background-color: #a7a7a7' >".
                "                         <th class='text-center' style='background-color: #a7a7a7'>نام</th>".
                "                         <th class='text-center'>کد ملی</th>".
                "                      </tr>".
                "                      </thead>".
                "                      <tbody>".
                "                         <tr>".
                "                            <td class='text-center' style='text-align: center'>$user->name ".
                "                            </td>".
                "                            <td class='text-center' style='text-align: center'>$user->national_code".
                "                            </td>".
                "                         </tr>".
                "                      </tbody>".
                "                   </table>".
                "               </div>";

            if(!isset($ddfs[0]->id)) {
                $orderView .= "<div class='row'>".
                    "    <div class='col-xl-8 col-lg-8 col-md-8 col-sm-8 col-12'>".
                    "        <h3 class='mb-1 text-info'><b>منو غذای این تاریخ در سیستم تعریف نشده است</b></h3>".
                    "    </div>".
                    "</div></div></div></div></div>";

                return response()->json([
                    'status' => true,
                    'res'    => $orderView,
                ]);
            }
            else {
                // check if user haven't reserve
                // we show user info only
                // and today food-opt menu to reserve
                $userHaveNotReserve = true;
                foreach ($ddfs as $ddf) {
                    $reservesCount = $ddf->reservation()->where('user_id', $user->id)->count();
                    if($reservesCount > 0) {
                        $userHaveNotReserve = false;
                        break;
                    }
                }

                if($userHaveNotReserve) {
                    $orderView .= "<div class='row'>".
                        "    <div class='col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 text-center'>".
                        "        <h3 class='mb-1 text-danger'><b>رزرو ندارد</b></h3>".
                        "    </div>".
                        "</div></div></div></div></div>";

                    $rest = $userRests->first();
                    if(!$rest)
                        return response()->json([
                            'status' => false,
                            'res'    => 'دسترسی کاربر به هیچ رستوران و سلف سرویسی تعریف نشده است',
                        ]);
                    $collection = $rest->collection;

                    $data = new \stdClass();
                    $data->user     = $user;
                    $data->req_type = 'change-user';

                    $wb = new WeekBox($data);
                    $wb->change_user($user);

                    $data = new \stdClass();
                    $data->req_type = 'md';
                    $req['date']   = $date;
                    $req['data_m'] = config('app.rMeals')[$meal];
                    $req['data_r'] = $rest->id;
                    $req['data_c'] = $collection->id;

                    $r = new Request($req);
                    $data->request  = $r->all();

                    $wb     = new WeekBox($data);
                    $result = $wb->make_menu();

                    if($result['status'] != 200)
                        return response()->json([
                            'status' => false,
                            'res'    => 'مشخصات نامعتبر است',
                        ]);

                    $orderView .= $result['res'];
                    $orderView .= "<div class='row mt-2'>".
                        "    <div class='col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 text-center'>".
                        "        <button type='button' class='btn btn-success btn-block' id='submit-edit-modal'>رزرو</button>".
                        "    </div>".
                        "</div>";
                    return response()->json([
                        'status' => true,
                        'res'    => $orderView,
                    ]);
                }
            }

            $orderView .= "<div class='table-responsive'>".
                "                  <table class='table table-striped table-bordered table-sm' border='2' dir='rtl' style='font-family: IRANYekanWeb; font-size: 13px; width: 100%'>".
                "                      <thead>".
                "                      <tr style='background-color: #a7a7a7'>".
                "                         <th class='text-center'>کد فیش</th>".
                "                         <th class='text-center'>تاریخ</th>".
                "                      </tr>".
                "                      </thead>".
                "                      <tbody>".
                "                         <tr>".
                "                            <td style='text-align: center'>$billNumber".
                "                            <td style='text-align: center'>$dateTime".
                "                            </td>".
                "                         </tr>".
                "                      </tbody>".
                "                   </table>".
                "               </div>".
                "               <div class='table-responsive' id='foodTable'>".
                "                   <table class=' table-bordered table-sm' border='2' dir='rtl' style='font-family: IRANYekanWeb; font-size: 13px; width: 100%'>".
                "                       <thead>".
                "                       <tr style='background-color: #a7a7a7'>".
                "                           <th class='text-right'>سفارش</th>".
                "                           <th class='text-right'>تعداد</th>".
                "                           <th class='text-right'>قیمت(R)</th>".
                "                           <th class='text-right status-col'>وضعیت</th>".
                "                       </tr>".
                "                       </thead>".
                "                       <tbody>";

            /*$toolbox = '<div class="col-lg-12 col-md-12 col-sm-12 col-12 p-0">
                                        <div class="card">
                                            <div class="card-body">
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="form-group">
                                                    <label>انتخاب صف</label>
                                                    <select id="queue-name" class="form-control">
                                                        <option value="0" selected>طبقه همکف</option>
                                                        <option value="1">طبقه بالا</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>';*/

            $markAsEaten = $request->json()->get('mark_as_eaten');

            $priceSum = 0;
            $count    = 0;
            $discountAmount = "-";
            $autoPrint = "";
            $haveNotReserve = true;
            $eatenStatus    = true;

            foreach ($ddfs as $ddf) {
                $reserve = $ddf->reservation()->where('user_id', $user->id)->first();
                $nimPors = "";
                /*if($ddf->pors == "نیم پرس")
                    $nimPors = "<span class='badge badge-warning'>ن پ</span>";*/
                if($reserve) {
                    $haveNotReserve = false;
                    $food    = $reserve->food_title;
                    if($readType == "rfid") {
                        if($reserve->eaten == 1) {
                            $date1 = new \DateTime($reserve->updated_at);
                            $date2 = new \DateTime(Date('Y-m-d H:i:s'));
                            $intervalDay   = (int)($date1->diff($date2)->format('%R%a'));
                            $intervalHour  = (int)($date1->diff($date2)->format('%R%h'));
                            $intervalMin   = (int)($date1->diff($date2)->format('%R%i'));
                            $intervalSec   = (int)($date1->diff($date2)->format('%R%s'));
                            $totalInterval = ($intervalDay * 1440 * 60)+ ($intervalHour * 60 * 60) + ($intervalMin * 60) + $intervalSec;
                            if((int)$totalInterval > 5) {
                                $updatedAt = $reserve->updatedAt();
                                $checkBox = "<label class='text-danger'>استفاده شده در <strong>$updatedAt</strong></label>";
                            }
                            else {
                                $checkBox = "<label class='text-success'>قابل استفاده</label>";
                                $autoPrint = "auto-print";
                            }
                        }
                        else {
                            $checkBox = "<label class='text-success'>قابل استفاده</label>";
                            $reserve->eaten = 1;
                            $reserve->eaten_in = $eatenIn;
                            $reserve->eaten_ip = $eatenIp;
                            $reserve->eaten_at = $eatenAt;
                            $reserve->update();
                            $autoPrint = "auto-print";
                            $eatenStatus = false;
                        }
                    }
                    else {
                        if ($reserve->eaten == 1)
                            $checkBox = "<label class='text-warning font-weight-bolder'>" .
                                "    <input id='f-$reserve->id' type='checkbox' checked> استفاده شده <span class='text-dark ltr d-inline-block'>($reserve->eaten_at)</span>" .
                                "</label>";
                        else {
                            $checkBox = "<label class='text-success font-weight-bolder'>" .
                                "    <input id='f-$reserve->id' type='checkbox'> قابل استفاده" .
                                "</label>";
                            $eatenStatus = false;
                            if($markAsEaten) {
                                $checkBox = "<label class='text-warning font-weight-bolder'>" .
                                    "    <input id='f-$reserve->id' type='checkbox' checked> استفاده شده <span class='text-dark ltr d-inline-block'>($eatenAt)</span>" .
                                    "</label>";

                                $reserve->eaten_in = $eatenIn;
                                $reserve->eaten_ip = $eatenIp;
                                $reserve->eaten_at = $eatenAt;
                                $reserve->eaten = 1;
                                $reserve->save();
                                $eatenStatus = true;
                            }
                        }
                    }

                    $hasDiscount = "";
                    if($reserve->discount_count >= 1) {
                        $hasDiscount    = "<span class='text-info'>*</span>";
                        $discountAmount = $reserve->amount;
                    }

                    $orderView .= "<tr>".
                        "    <td class='text-right'>$food $nimPors $hasDiscount</td>".
                        "    <td class='text-right'>$reserve->count</td>".
                        "    <td class='text-right'>$reserve->pay_amount</td>".
                        "    <td class='text-right status-col'>$checkBox</td>".
                        "</tr>";
                    $priceSum += $reserve->pay_amount;
                    $count    += $reserve->count;
                }
            }
            $toolbox = '';
            if(!$haveNotReserve) {
                $toolbox = '<div class="col-lg-12 col-md-12 col-sm-12 col-12 p-0">
                                    <div class="card">
                                        <div class="card-body">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <span class="d-block">تغییر وضعیت رزروها به:</span>';
                if ($eatenStatus)
                    $toolbox .= '<div class="form-group">
                                 <button type="button" class="btn btn-secondary btn-block" name="setAll" value="0">مصرف نشده</button>
                             </div>';
                else
                    $toolbox .= '<div class="form-group mt-2">
                                    <button type="button" class="btn btn-success btn-block" name="setAll" value="1">مصرف شده</button>
                                </div>';
                $toolbox .= '</div></div></div></div>';
            }

            if($haveNotReserve)
                $orderView .= "<tr>".
                    "    <td colspan='5' class='text-center text-danger'>رزرو ندارد</td>".
                    "</tr>";
            else
                $orderView .= "<tr>".
                    "    <td class='text-center'>جمع</td>".
                    "    <td class='text-right'>$count</td>".
                    "    <td class='text-right'>$priceSum</td>".
                    "    <td class='text-right status-col'></td>".
                    "</tr>".
                    "<tr>".
                    "    <td class='text-center'>سوبسید</td>".
                    "    <td class='text-right'></td>".
                    "    <td class='text-right'>$discountAmount</td>".
                    "    <td class='text-right status-col'></td>".
                    "</tr>";

            if($readType != "rfid")
                $autoPrint = "";

            $orderView .= "</tbody></table></div>".
                "               <div class='row'>".
                "                   <div class='col-xl-4 col-lg-4 col-md-4 col-sm-4 col-6'>".
                "                       <p class='mb-1'><button class='btn btn-light $autoPrint' id='print'><i class='fa fa-print'></i></button></p>".
                "                   </div>".
                "               </div>".
                "</div></div></div>".
                "<div class='col-xl-4 col-lg-4 col-md-4 col-sm-4 col-6'>".
                "    <div class='col-lg-12 col-md-12 col-sm-12 col-12 p-0'>".
                "        <div class='card'>".
                "            <div class='card-body'>".
                "                <div class='card-img text-center'>".
                "                    <img src='$userImg' style='width: auto;max-height: 400px'>".
                "                </div>".
                "            </div>".
                "        </div>".
                "    </div>".
                "$toolbox".
                "</div>".
                "</div>";

            return response()->json([
                'status' => true,
                'res'    => $orderView,
            ]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست!!!']);
    }

    public function mark_as_eaten(Request $request)
    {
        if (Rbac::check_access('reserves', 'mark_as_eaten')) {

            $meals   = config('app.meals');
            $mealsStr = implode(',', array_values($meals));

            $v = Validator::make($request->json()->all(),[
                'id'   => 'required|string',
                'uid'  => 'required|string',
                'date' => 'required|date',
                'meal' => 'required|in:'.$mealsStr,
                'mode' => 'required|in:0,1',
                'queueName' => 'required|in:0,1',
            ]);
            if($v->fails())
                return response()->json(['status' => false, 'res' => 'مشخصات نامعتبر است']);
            $id   = $request->json()->get('id');
            $uid  = $request->json()->get('uid');
            $date = $request->json()->get('date');
            $meal = $request->json()->get('meal');
            $mode = $request->json()->get('mode');
            $queueName = $request->json()->get('queueName');

            $eatenAt  = Date('Y-m-d H:i:s');
            $eatenIp  = \Request::ip();
            $eatenIn  = null;
            $restInfo = RestInfo::where('ip', $eatenIp)->first();
            if($restInfo)
                $eatenIn = $restInfo->id;

            $user = DB::table('users')->where('std_no', $uid)->orWhere('national_code', $uid)->where('active', 1)->first();
            if (!$user)
                return response()->json(['status' => true, 'res' => 'مشخصات کاربر پیدا نشد']);
            if ($id == 'setAll') {
                $r = Reservation::where('date', $date)
                    ->where('meal', $meal)
                    ->where('user_id', $user->id)
                    ->first();
                if($mode == 1) {
                    if($r) {
                        $r->eaten = 1;
                        $r->eaten_in = $eatenIn;
                        $r->eaten_ip = $eatenIp;
                        $r->eaten_at = $eatenAt;
                        $r->update();
                        Activity::create([
                            'ip_address'  => \Request::ip(),
                            'user_agent'  => \Request::header('user-agent'),
                            'task'        => 'eaten',
                            'description' => 'ثبت غذا به عنوان مصرف نشده مربوط به منو غذایی روز '.$date.'با شناسه رزرو '.$r->id,
                            'user_id'     => Auth::user()->id,
                            'ids'         => $r->id.'-'.$r->user_id,
                        ]);

                        return response()->json(['status' => true, 'res' => 'فرآیند بروزرسانی رزروها انجام شد']);
                    }
                    /*$queue = FreeQueue::where('user_id',$user->id)->where('date',$date)->where('meal',$meal)->first();
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

                        $bill_number = $prefix.'-'.time();

                        $fq = new FreeQueue();
                        $fq->queue_name  = $queueName;
                        $fq->user_id     = $user->id;
                        $fq->date        = $date;
                        $fq->meal        = $mealToNumber[$meal];
                        $fq->bill_number = $bill_number;
                        $fq->save();

                        $freeQ = FreeQueue::find($fq->id);
                        $view  = $freeQ->prepared_view();
                        $freeQ->orders = $view;
                        $freeQ->update();
                    }*/
                }
                elseif ($mode == 0) {
                    if($r) {
                        $r->eaten_in = null;
                        $r->eaten_ip = null;
                        $r->eaten_at = null;
                        $r->eaten = 0;
                        $r->update();
                        Activity::create([
                            'ip_address'  => \Request::ip(),
                            'user_agent'  => \Request::header('user-agent'),
                            'task'        => 'not-eaten',
                            'description' => 'ثبت غذا به عنوان مصرف شده مربوط به منو غذایی روز '.$date.'با شناسه رزرو '.$r->id,
                            'user_id'     => Auth::user()->id,
                            'ids'         => $r->id.'-'.$r->user_id,
                        ]);
                        return response()->json(['status' => true, 'res' => 'فرآیند بروزرسانی رزروها انجام شد']);
                    }
                    // FreeQueue::where('user_id',$user->id)->where('date',$date)->where('meal',$meal)->delete();
                }
            }
            elseif (strpos($id,'f-') >= 0) {
                $exp = explode('-',$id);
                if(!isset($exp[1]) || !is_numeric($exp[1]))
                    return response()->json(['status' => false, 'res' => 'اطلاعات ورودی نامعتبر است']);
                $id = $exp[1];
                $freeReserve = Reservation::where('id', $id)->where('user_id', $user->id)->first();
                if(!$freeReserve)
                    return response()->json(['status' => false, 'res' => 'اطلاعات رزرو پیدا نشد']);
                if($mode == 1) {
                    $freeReserve->eaten = 1;
                    $freeReserve->eaten_in = $eatenIn;
                    $freeReserve->eaten_ip = $eatenIp;
                    $freeReserve->eaten_at = $eatenAt;
                    $freeReserve->save();
                    Activity::create([
                        'ip_address'  => \Request::ip(),
                        'user_agent'  => \Request::header('user-agent'),
                        'task'        => 'eaten',
                        'description' => 'ثبت غذا به عنوان مصرف نشده مربوط به منو غذایی روز '.$date.'با شناسه رزرو '.$freeReserve->id,
                        'user_id'     => Auth::user()->id,
                        'ids'         => $freeReserve->id.'-'.$freeReserve->user_id,
                    ]);
                    /*$queue = FreeQueue::where('user_id',$user->id)->where('date',$date)->where('meal',$meal)->first();
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

                        $bill_number = $prefix.'-'.time();

                        $fq = new FreeQueue();
                        $fq->queue_name  = $queueName;
                        $fq->user_id     = $user->id;
                        $fq->date        = $date;
                        $fq->meal        = $mealToNumber[$meal];
                        $fq->bill_number = $bill_number;
                        $fq->save();

                        $freeQ = FreeQueue::find($fq->id);
                        $view  = $freeQ->prepared_view();
                        $freeQ->orders = $view;
                        $freeQ->update();
                    }*/
                }
                else {
                    $freeReserve->eaten = 0;
                    $freeReserve->eaten_in = null;
                    $freeReserve->eaten_ip = null;
                    $freeReserve->eaten_at = null;
                    $freeReserve->save();
                    Activity::create([
                        'ip_address'  => \Request::ip(),
                        'user_agent'  => \Request::header('user-agent'),
                        'task'        => 'not-eaten',
                        'description' => 'ثبت غذا به عنوان مصرف شده مربوط به منو غذایی روز '.$date.'با شناسه رزرو '.$freeReserve->id,
                        'user_id'     => Auth::user()->id,
                        'ids'         => $freeReserve->id.'-'.$freeReserve->user_id,
                    ]);
                }
            }
            return response()->json(['status' => false, 'res' => 'انجام فرآیند امکان پذیر نیست']);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست!!!']);
    }

    public function fe_male_count()
    {
        if(Rbac::check_access('reserves','fe_male_count')) {
            $meals   = config('app.meals');
            return view('cms.reserves.fe-male-count.index', compact('meals'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function fe_male_count_by_date(Request $request)
    {
        if(Rbac::check_access('reserves','fe_male_count_by_date')) {
            set_time_limit(0);
            $beginDate = $request->beginDate;
            $endDate   = $request->endDate;
            $sex       = $request->sex;
            $meal      = $request->meal;
            session([
                'beginDate3' => $beginDate,
                'endDate3'   => $endDate,
                'sex3'       => $sex,
                'meal3'      => $meal,
            ]);
            $rr  = new Resreport([$sex,]);
            if($sex == 'مرد و زن')
                $rr  = new Resreport(['مرد','زن']);

            $res = $rr->make_report($meal, $beginDate, $endDate, 3, '');
            $res = json_decode($res);
            return response()->json(['status' => true, 'res' => $res]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function fe_male_count_print()
    {
        if(Rbac::check_access('reserves','fe_male_count_print')) {
            set_time_limit(0);
            $rr  = new Resreport([session('sex3'),]);
            if(session('sex3') == 'مرد و زن')
                $rr  = new Resreport(['مرد','زن']);
            $res = $rr->make_report(session('meal3'), session('beginDate3'), session('endDate3'), 3, '');
            $res = json_decode($res);
            return view('cms.prints.reserves.fe-male-count', compact('res'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function home_reserve_count(Request $request)
    {
        if(Rbac::check_access('reserves','home_reserve_count')) {
            $v = Validator::make($request->all(),[
                'meal'         => 'required|in:صبحانه,نهار,شام',
                'resCountDate' => 'required|date_format:Y-m-d',
            ]);
            if ($v->fails())
                return response()->json(['status' => 101, 'res' => $v->errors()]);

            $meal = $request->meal;
            $resCountDate = $request->resCountDate;
            $rr = new Resreport(['مرد','زن','غذا','خوابگاهی','غیر خوابگاهی','کل']);
            $result = $rr->make_report($meal,$resCountDate,$resCountDate);
            $result = json_decode($result);
            $rC1 = isset($result->response[0]->g)       ? $result->response[0]->g       :0;
            $rC2 = isset($result->response[0]->b)       ? $result->response[0]->b       :0;
            $rC3 = isset($result->response[0]->dorm)    ? $result->response[0]->dorm    :0;
            $rC4 = isset($result->response[0]->non_dorm)? $result->response[0]->non_dorm:0;
            $rC5 = isset($result->response[0]->total)   ? $result->response[0]->total   :0;
            session([
                'rC1' => $rC1,
                'rC2' => $rC2,
                'rC3' => $rC3,
                'rC4' => $rC4,
                'rC5' => $rC5,
            ]);
            return response()->json([
                'status' => 200,
                'rC1' => $rC1,
                'rC2' => $rC2,
                'rC3' => $rC3,
                'rC4' => $rC4,
                'rC5' => $rC5,
            ]);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function statistics()
    {
        if(Rbac::check_access('reserves','statistics'))
            return view('cms.reserves.statistics.index');
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public $porsTypes = [
        'پرس',
        'نیم پرس',
    ];

    public function free_statistics(Request $request)
    {
        if(Rbac::check_access('reserves','statistics_date')) {
            $v = Validator::make($request->all(), [
                'beginDate' => 'required|date_format:Y-m-d',
                'endDate'   => 'required|date_format:Y-m-d',
                'type'      => 'required|in:1,2,3,4',
            ]);
            if ($v->fails())
                return response()->json(['status' => 101, 'res' => $v->errors()]);
            $beginDate     = $request->beginDate;
            $endDate       = $request->endDate;
            $personDetails = $request->personDetails;

            $resreport    = new Resreport();
            $intervalDate = $resreport->date_interval($beginDate, $endDate);
            $type         = $request->type;

            switch ($type) {
                // جمع تعداد رزرو و مصرف شده ها به تفکیک نوع غذا
                case 1:
                    $htmlView = "<div class='card'>".
                        "    <div class='card-header'>جمع تعداد رزرو و مصرف شده ها به تفکیک نوع غذا از <span>$beginDate</span> تا <span>$endDate</span>".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 close-report float-left' id='rc-$type'><i class='fa fa-times'></i></button>".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 print-btn float-left' id='rp-$type'><i class='fa fa-print'></i></button>".
                        "    </div>".
                        "    <div class='card-body'>".
                        "        <table class='table table-striped table-bordered table-sm' id='table-$type'>".
                        "            <thead>".
                        "                <th class='text-center'>#</th>".
                        "                <th class='text-right'>نوع غذا</th>".
                        "                <th class='text-center'>تعداد رزرو</th>".
                        "                <th class='text-center'>مصرف شده</th>".
                        "            </thead>".
                        "            <tbody>";
                    $tr = "";
                    $foodCounter = [];
                    foreach ($intervalDate as $date => $day) {
                        $ddfs = Menu::where('date', $date)->get();
                        foreach ($ddfs as $ddf) {
                            $reservesCount = $ddf->reservation()->sum('count');
                            $eatenCount    = $ddf->reservation()->where('eaten', 1)->count();
                            //$nimPors       = $ddf->pors == $this->porsTypes[0] ? "" : "<span class='bg bg-warning p-1'> $ddf->pors</span>";
                            $nimPors       = "";
                            $foodTitle     = $ddf->food_title;
                            if(isset($ddf->desserts[0]->id)) {
                                foreach ($ddf->desserts as $dessert)
                                    $foodTitle .= ' | '.$dessert->title;
                            }
                            //$food         .= $nimPors;
                            if(isset($foodCounter[$foodTitle]))
                                $foodCounter[$foodTitle] = [
                                    'reserves_count' => $foodCounter[$foodTitle]['reserves_count'] + $reservesCount,
                                    'eaten_count'    => $foodCounter[$foodTitle]['eaten_count']    + $eatenCount,
                                ];
                            else
                                $foodCounter[$foodTitle] = [
                                    'reserves_count' => $reservesCount,
                                    'eaten_count'    => $eatenCount,
                                ];
                        }
                    }
                    $i = 1;
                    foreach ($foodCounter as $foodTitle => $details) {
                        $tr .= "<tr>".
                            "    <td class='text-center'>$i</td>".
                            "    <td class='text-right'>$foodTitle</td>".
                            "    <td class='text-center'>$details[reserves_count]</td>".
                            "    <td class='text-center'>$details[eaten_count]</td>".
                            "</tr>";
                        $i++;
                    }
                    $htmlView .= $tr;
                    $htmlView .= "</tbody></table></div></div>";
                    return response()->json(['status' => 200, 'res' => $htmlView]);
                // تعداد رزرو، نوع غذا، مصرف شده و تاریخ
                case 2:
                    $htmlView = "<div class='card'>".
                        "    <div class='card-header'>تعداد رزرو، نوع غذا، مصرف شده و تاریخ".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 close-report float-left' id='rc-$type'><i class='fa fa-times'></i></button>".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 print-btn float-left' id='rp-$type'><i class='fa fa-print'></i></button>".
                        "    </div>".
                        "    <div class='card-body'>".
                        "        <table class='table table-striped table-bordered table-sm' id='table-$type'>".
                        "            <thead>".
                        "                <th class='text-center'>#</th>".
                        "                <th class='text-right'>نوع غذا</th>".
                        "                <th class='text-center'>تعداد رزرو</th>".
                        "                <th class='text-center'>مصرف شده</th>".
                        "            </thead>".
                        "            <tbody>";
                    $tr = "";
                    foreach ($intervalDate as $date => $day) {
                        $ddfs = Menu::where('date', $date)->get();
                        if(isset($ddfs[0]->id))
                            $tr .= "<tr>".
                                "    <td colspan='4' class='text-center'><span class='bg-secondary text-white m-1 p-1' style='border-radius: 5px'>$date $day</span></td>".
                                "</tr>";
                        $i = 1;
                        foreach ($ddfs as $ddf) {
                            $reservesCount = $ddf->reservation()->sum('count');
                            $eatenCount    = $ddf->reservation()->where('eaten', 1)->count();
                            //$nimPors       = $ddf->pors == $this->porsTypes[0] ? "" : "<span class='bg bg-warning p-1'> $ddf->pors</span>";
                            $nimPors = "";
                            $foodTitle     = $ddf->food_title;
                            if(isset($ddf->desserts[0]->id)) {
                                foreach ($ddf->desserts as $dessert)
                                    $foodTitle .= ' | '.$dessert->title;
                            }
                            //$food         .= $nimPors;
                            $tr           .= "<tr>".
                                "    <td class='text-center'>$i</td>".
                                "    <td class='text-right'>$foodTitle</td>".
                                "    <td class='text-center'>$reservesCount</td>".
                                "    <td class='text-center'>$eatenCount</td>".
                                "</tr>";
                            $i++;
                        }
                    }
                    $htmlView .= $tr;
                    $htmlView .= "</tbody></table></div></div>";
                    return response()->json(['status' => 200, 'res' => $htmlView]);
                case 3:
                    $htmlView = "<div class='card'>".
                        "    <div class='card-header'>تعداد رزرو، نوع غذا، مصرف شده و تاریخ".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 close-report float-left' id='rc-$type'><i class='fa fa-times'></i></button>".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 print-btn float-left' id='rp-$type'><i class='fa fa-print'></i></button>".
                        "    </div>".
                        "    <div class='card-body'>".
                        "        <table class='table table-striped table-bordered table-sm' id='table-$type'>".
                        "            <thead>".
                        "                <th class='text-center'>#</th>".
                        "                <th class='text-right'>نوع غذا</th>".
                        "                <th class='text-center'>تعداد رزرو</th>".
                        "                <th class='text-center'>مصرف شده</th>".
                        "            </thead>".
                        "            <tbody>";
                    $tr = "";
                    foreach ($intervalDate as $date => $day) {
                        $ddfs = Menu::where('date', $date)->get();

                        if(isset($ddfs[0]->id))
                            $tr .= "<tr>".
                                "    <td colspan='4' class='text-center'><span class='bg-secondary text-white m-1 p-1' style='border-radius: 5px'>$date $day</span></td>".
                                "</tr>";
                        $i = 1;
                        $infoCount = [];
                        foreach ($ddfs as $ddf) {
                            $reservesCount = $ddf->reservation()->sum('count');
                            $eatenCount    = $ddf->reservation()->where('eaten', 1)->count();
                            //$nimPors       = $ddf->pors == $this->porsTypes[0] ? "" : "<span class='bg bg-warning p-1'> $ddf->pors</span>";
                            $nimPors = "";
                            $foodTitle     = $ddf->food_title;
                            if(isset($ddf->desserts[0]->id)) {
                                foreach ($ddf->desserts as $dessert)
                                    $foodTitle .= ' | '.$dessert->title;
                            }
                            //$food         .= $nimPors;
                            $tr           .= "<tr>".
                                "    <td class='text-center'>$i</td>".
                                "    <td class='text-right'>$foodTitle</td>".
                                "    <td class='text-center'>$reservesCount</td>".
                                "    <td class='text-center'>$eatenCount</td>".
                                "</tr>";
                            $i++;

                            $infoCount['std']         = 0;
                            $infoCount['std_men']     = 0;
                            $infoCount['std_women']   = 0;
                            $infoCount['other']       = 0;
                            $infoCount['other_men']   = 0;
                            $infoCount['other_women'] = 0;
                            $reserves = $ddf->reservation;
                            foreach ($reserves as $reserve) {
                                $user = $reserve->user;
                                if($user->std_no) {
                                    $infoCount['std']           = $infoCount['std']  + 1;
                                    if($user->sex == 1)
                                        $infoCount['std_men']   = $infoCount['std_men']  + 1;
                                    elseif ($user->sex == 2)
                                        $infoCount['std_women'] = $infoCount['std_women']  + 1;
                                }
                                else {
                                    $infoCount['other']           = $infoCount['other']  + 1;
                                    if($user->sex == 1)
                                        $infoCount['other_men']   = $infoCount['other_men']  + 1;
                                    elseif ($user->sex == 2)
                                        $infoCount['other_women'] = $infoCount['other_women']  + 1;
                                }
                            }
                            $tr .= "<tr><td colspan='4'>".
                                "        <table class='table table-sm bg-info'>".
                                "            <tbody>".
                                "            <tr>".
                                "                <td>دانشجو</td>".
                                "                <td>$infoCount[std]</td>".
                                "                <td>سایر</td>".
                                "                <td>$infoCount[other]</td>".
                                "            </tr>".
                                "            <tr>".
                                "                <td>مرد</td>".
                                "                <td>$infoCount[std_men]</td>".
                                "                <td>مرد</td>".
                                "                <td>$infoCount[other_men]</td>".
                                "            </tr>".
                                "            <tr>".
                                "                <td>زن</td>".
                                "                <td>$infoCount[std_women]</td>".
                                "                <td>زن</td>".
                                "                <td>$infoCount[other_women]</td>".
                                "            </tr>".
                                "            </tbody>".
                                "        </table>".
                                "</td></tr>";
                        }
                    }
                    $htmlView .= $tr;
                    $htmlView .= "</tbody></table></div></div>";
                    return response()->json(['status' => 200, 'res' => $htmlView]);
                case 4:
                    $htmlView = "<div class='card'>".
                        "    <div class='card-header'>تعداد رزرو، نوع غذا، مصرف شده و تاریخ".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 close-report float-left' id='rc-$type'><i class='fa fa-times'></i></button>".
                        "        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 print-btn float-left' id='rp-$type'><i class='fa fa-print'></i></button>".
                        "    </div>".
                        "    <div class='card-body'>".
                        "        <table class='table table-striped table-bordered table-sm' id='table-$type'>".
                        "            <thead>".
                        "                <th class='text-center'>#</th>".
                        "                <th class='text-right'>نوع غذا</th>".
                        "                <th class='text-center'>تعداد رزرو</th>".
                        "                <th class='text-center'>مصرف شده</th>".
                        "            </thead>".
                        "            <tbody>";
                    $tr = "";
                    foreach ($intervalDate as $date => $day) {
                        $ddfs = Menu::where('date', $date)->get();

                        if(isset($ddfs[0]->id))
                            $tr .= "<tr>".
                                "    <td colspan='4' class='text-center'><span class='bg-secondary text-white m-1 p-1' style='border-radius: 5px'>$date $day</span></td>".
                                "</tr>";
                        $i = 1;
                        foreach ($ddfs as $ddf) {
                            $reservesCount = $ddf->reservation()->sum('count');
                            $eatenCount    = $ddf->reservation()->where('eaten', 1)->count();
                            //$nimPors       = $ddf->pors == $this->porsTypes[0] ? "" : "<span class='bg bg-warning p-1'> $ddf->pors</span>";
                            $nimPors = "";
                            $foodTitle     = $ddf->food_title;
                            if(isset($ddf->desserts[0]->id)) {
                                foreach ($ddf->desserts as $dessert)
                                    $foodTitle .= ' | '.$dessert->title;
                            }
                            //$food         .= $nimPors;
                            $tr           .= "<tr>".
                                "    <td class='text-center'>$i</td>".
                                "    <td class='text-right'>$foodTitle</td>".
                                "    <td class='text-center'>$reservesCount</td>".
                                "    <td class='text-center'>$eatenCount</td>".
                                "</tr>";
                            $i++;

                            $userInfo = [];
                            $reserves = $ddf->reservation;
                            foreach ($reserves as $reserve) {
                                $user = $reserve->user;
                                $userInfo[] = [
                                    'name'          => $user->name,
                                    'std_no'        => $user->std_no,
                                    'national_code' => $user->national_code,
                                    'count'         => $reserve->count,
                                ];
                            }
                            usort($userInfo, function ($x, $y) {
                                $sub1 = substr(mb_convert_encoding($x['name'],"auto",'UTF-8'),0,1);
                                $sub2 = substr(mb_convert_encoding($y['name'],"auto",'UTF-8'),0,1);
                                $ord1 = ord(mb_convert_encoding($sub1,"auto",'UTF-8'));
                                $ord2 = ord(mb_convert_encoding($sub2,"auto",'UTF-8'));
                                return $ord1 > $ord2;
                            });
                            $tr .= "<tr><td colspan='4'>".
                                "        <table class='table table-sm bg-yellow'>".
                                "            <thead>".
                                "            <tr>".
                                "            <td>نام</td>".
                                "            <td>کد ملی</td>".
                                "            <td>ش.دانشجویی</td>".
                                "            <td>تعداد</td>".
                                "            </thead>".
                                "            <tbody>";
                            foreach ($userInfo as $info)
                                $tr .= "<tr>".
                                    "       <td>$info[name]</td>".
                                    "       <td>$info[national_code]</td>".
                                    "       <td>$info[std_no]</td>".
                                    "       <td>$info[count]</td>".
                                    "   </tr>";
                            $tr .= "</tbody>".
                                "</table>".
                                "</td></tr>";
                        }
                    }
                    $htmlView .= $tr;
                    $htmlView .= "</tbody></table></div></div>";
                    return response()->json(['status' => 200, 'res' => $htmlView]);
            }
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function free_statistics_reserves_excel($date)
    {
        if(Rbac::check_access('reserves','free_statistics_reserves_excel')) {
            $v = Validator::make(['date' => $date], [
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($v->fails())
                return redirect()->back()->with('warningMsg' , 'لطفاَ تاریخ را وارد کنید');
            $resreport = new Resreport();
            $resreport->get_free_reserves_excel($date);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function active_users()
    {
        if(!Rbac::check_access('reserves','active_users'))
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

        $usersCount = User::where('active',1)->count();
        $jdf = new jdf();
        $year =  $jdf->jdate('y');
        $enterTmp = [];
        for($baseYear = 92; $baseYear <= $year; $baseYear++) {
            $usersCountEnter = DB::table('users')->where('std_no','LIKE',$baseYear.'%')->where('active',1)->count();
            $enterTmp[] = (object)[
                'enter' => $baseYear,
                'count' => $usersCountEnter,
            ];
        }
        return view('cms.reserves.active-users.index',compact('usersCount','enterTmp'));
    }

    public function active_users_calculate(Request $request)
    {
        if(!Rbac::check_access('reserves','active_users'))
            return response()->json(['status' => 101,'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);

        $v = Validator::make($request->all(),[
            'begin_date'  => 'required|date_format:Y-m-d',
            'end_date'    => 'required|date_format:Y-m-d',
            'res_count'   => 'required|numeric',
            'sign'        => 'required|in:1,2,3',
        ],[
            'begin_date.required'    => 'تاریخ شروع را انتخاب کنید',
            'begin_date.date_format' => 'تاریخ شروع را به صورت xxxx-xx-xx وارد کنید',
            'end_date.required'      => 'تاریخ پایان را انتخاب کنید',
            'end_date.date_format'   => 'تاریخ پایان را به صورت xxxx-xx-xx وارد کنید',
            'res_count.required'     => 'تعداد رزرو را به صورت عدد وارد کنید',
            'res_count.numeric'      => 'تعداد رزرو را به صورت عدد وارد کنید',
            'sign.required'          => 'یکی از گزینه ها را به درستی انتخاب کنید',
            'sign.in'                => 'یکی از گزینه ها را به درستی انتخاب کنید',
        ]);
        if($v->fails())
            return response()->json(['status' => 101,'res' => $v->errors()]);

        $reserveCount = $request->res_count;
        $sign = "=";
        if($request->sign == 1)
            $sign = ">";
        elseif ($request->sign == 3)
            $sign = "<";
        $fromDate = $request->begin_date;
        $toDate   = $request->end_date;

        $jdf = new jdf();
        $fromDate = $jdf->getUTCDate($fromDate);
        $toDate   = $jdf->getUTCDate($toDate);

        $reserves = DB::select("SELECT user_id,created_at,COUNT(*) FROM reservation WHERE created_at >= :from_date AND created_at <= :to_date GROUP BY user_id HAVING COUNT(*) ".$sign." :res_count",[
            'res_count' => $reserveCount,
            'from_date' => $fromDate,
            'to_date'   => $toDate,
        ]);
        $count = count($reserves);

        $usersCount = User::where('created_at','>=',$fromDate)->where('updated_at','<=',$toDate)->count();

        $res =  "<div class='card card-body'>".
            "<div class='card-title'><button type=\"button\" class=\"btn btn-light btn-sm float-left print-btn\"><i class=\"fa fa-print\"></i></button></div>".
            "<div class='table-responsive'>".
            "    <table class='table table-striped table-bordered table-sm'>".
            "        <thead>".
            "           <tr>".
            "                 <th colspan='4' class='text-right'>گزارش تعداد کاربران فعال سامانه تغذیه بلوط دانشگاه ایلام</th>".
            "           </tr>".
            "           <tr>".
            "                 <th class='text-right'>شروع از تاریخ</th>".
            "                 <th class='text-center'>تا تاریخ</th>".
            "                 <th class='text-center'>تعداد رزرو برابر $reserveCount </th>".
            "                 <th class='text-center'>تعداد کاربران ثبت نام شده در سامانه در این بازه زمانی</th>".
            "           </tr>".
            "        </thead>".
            "        <tbody>".
            "        <tr>".
            "          <td class='text-right'>$request->begin_date</td>".
            "          <td class='text-center'>$request->end_date</td>".
            "          <td class='text-center'>$count</td>".
            "          <td class='text-center'>$usersCount</td>".
            "        </tr>".
            "        </tbody>".
            "    </table>".
            "</div>";

        return response()->json(['status' => 200,'res' => $res]);
    }

    public function pay_back()
    {
        if(Rbac::check_access('reserves','manual_check')) {
            $meals   = config('app.meals');
            $cats   = Dorm::all();
            return view('cms.reserves.pay-back.index', compact('meals','cats'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function pay_back_res(Request $request)
    {
        if(Rbac::check_access('reserves','manual_check')) {
            $meals    = config('app.meals');
            $mealsStr = implode(',', array_values($meals));
            $v = Validator::make($request->json()->all(),[
                'cat'       => 'required|exists:dorm,id',
                'date'      => 'nullable|date',
                'meal'      => 'nullable|in:'.$mealsStr,
                'del'       => 'nullable|boolean',
            ]);
            if($v->fails())
                return response()->json(['status' => false, 'res' => 'اطلاعات ورودی نامعتبر است']);

            if ($request->json()->get('cat') == 11 ){
                $reserves = Reservation::where('dorm_id', null)
                    ->where('date', $request->json()->get('date'))
                    ->where('meal', $request->json()->get('meal'))
                    ->get();
            }
            else {
                $reserves = Reservation::where('dorm_id', $request->json()->get('cat'))
                    ->where('date', $request->json()->get('date'))
                    ->where('meal', $request->json()->get('meal'))
                    ->get();
            }

            $trHtml='';
            foreach ($reserves as $i=>$reserve){
                $counter = $i+1;

                $trHtml .="<tr>
                            <td>$counter</td>
                            <td>$reserve->user_name</td>
                            <td>$reserve->food_title</td>
                        </tr>";
                if ($request->json()->get('del') == true) {
                    $wallet = Wallet::where('user_id', $reserve->user_id)->orderBy('id', 'desc')->first();
                    $newWallet = new Wallet();
                    $newWallet->amount = $wallet->amount + $reserve->pay_amount;
                    $newWallet->value = $reserve->pay_amount;
                    $newWallet->_for = 'بازگشت گروهی 1 عدد ' . $reserve->food_title;
                    $newWallet->operation = 1;
                    $newWallet->user_id = $reserve->user_id;
                    if ($newWallet->save()) {
                        $notif = new Notification();
                        $notif->broadcast = 0;
                        $notif->title = 'برگشت مبلغ گروهی تاریخ ' . $reserve->date;
                        $notif->content = ' مبلغ ' . $reserve->pay_amount . ' ریال مربوط به غذای ' . $reserve->food_title . ' در روز ' . $reserve->day . '(' . $reserve->date . ') ،وعده ' . $reserve->meal . ' به حساب شما بازگشت داده شد.';
                        $notif->self = 0;
                        $notif->user_id = $reserve->user_id;
                        $notif->save();
                        $reserve->delete();
                    }
                }
            }
            $date = $request->json()->get('date');
            $meal = $request->json()->get('meal');
            $dorm = Dorm::find($request->json()->get('cat'))->title;
            $html = "<div class='col-12 text-center p-4'>
                        $date - $meal - $dorm
                        </div><div class='table-responsive'>
                        <table class='table table-striped'>
                            <thead>
                                <tr style='background-color: #a7a7a7'>
                                    <th class='text-right'>ردیف</th>
                                    <th class='text-right'>نام</th>
                                    <th class='text-right'>رزرو</th>
                                </tr>
                            </thead>
                            <tbody>
                            $trHtml
                            </tbody>
                            </table>
                        </div>";
            if ($request->json()->get('del') == true) {
                Activity::create([
                    'ip_address'  => \Request::ip(),
                    'user_agent'  => \Request::header('user-agent'),
                    'task'        => 'pay-back',
                    'description' => 'بازگشت مبلغ رزرو تاریخ '.$request->json()->get('date').' وعده '.$request->json()->get('meal').' مربوط به دسته '.Dorm::find($request->json()->get('cat'))->title,
                    'user_id'     => Auth::user()->id,
                    'ids'         => '',
                ]);
            }
            return response()->json([
                'status' => true,
                'res'    => $html,
            ]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست.']);
    }

    public function edit_reserve_name()
    {
        if(Rbac::check_access('reserves','manual_check')) {
            $meals   = config('app.meals');
            return view('cms.reserves.edit-reserves.index', compact('meals'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function edit_reserve_name_get_names(Request $request)
    {
        if(Rbac::check_access('reserves','manual_check')) {
            $meals    = config('app.meals');
            $mealsStr = implode(',', array_values($meals));
            $v = Validator::make($request->json()->all(),[
                'date'      => 'required|date',
                'meal'      => 'required|in:'.$mealsStr,
            ]);
            if($v->fails())
                return response()->json(['status' => 102, 'res' => 'اطلاعات ورودی نامعتبر است']);

            $date = $request->json()->get('date');
            $meal = $request->json()->get('meal');

            $ddfs       = Menu::where('date', $date)->where('meal', $meal)->get();
            $collectDDF = collect($ddfs);
            $ddfsId     = $collectDDF->map(function ($data){
                return $data->id;
            });
            if(count($ddfsId) < 1)
                return response()->json(['status' => 102, 'res' => 'برای تاریخ و وعده انتخاب شده منو غذا تعریف نشده است']);

            $reserves   = Reservation::where('date', $date)
                ->whereIn('menu_id', $ddfsId)->get();
            $reserves   = collect($reserves);
            $foodsGroup = $reserves->groupBy('food_title');
            $foodTitles = array_keys($foodsGroup->toArray());

            return response()->json(['status' => 200, 'res' => $foodTitles]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function update_reserve_name(Request $request)
    {
        if(Rbac::check_access('reserves','manual_check')) {
            $meals    = config('app.meals');
            $mealsStr = implode(',', array_values($meals));
            $v = Validator::make($request->json()->all(),[
                'date'         => 'required|date',
                'meal'         => 'required|in:'.$mealsStr,
                'oldFoodTitle' => 'required|string',
                'newFoodTitle' => 'required|string',
            ]);
            if($v->fails())
                return response()->json(['status' => 102, 'res' => 'اطلاعات ورودی نامعتبر است']);

            $date = $request->json()->get('date');
            $meal = $request->json()->get('meal');
            $oldFoodTitle = $request->json()->get('oldFoodTitle');
            $newFoodTitle = $request->json()->get('newFoodTitle');

            Reservation::where('date', $date)
                ->where('meal', $meal)
                ->where('food_title', $oldFoodTitle)
                ->update(['food_title' => $newFoodTitle]);

            Activity::create([
                'ip_address'  => \Request::ip(),
                'user_agent'  => \Request::header('user-agent'),
                'task'        => 'update_reserve_name',
                'description' => 'تغییر '.$oldFoodTitle.' به '.$newFoodTitle,
                'user_id'     => Auth::user()->id,
                'ids'         => '',
            ]);

            return response()->json(['status' => 200, 'res' => 'بروزرسانی انجام شد']);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
