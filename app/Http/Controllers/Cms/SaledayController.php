<?php

namespace App\Http\Controllers\Cms;

use App\Menu;
use App\DdfFoodPrice;
use App\Facades\Rbac;
use App\Food;
use App\FoodPrice;
use App\FreeBillNumPrefix;
use App\FreeDdf;
use App\FreeDdo;
use App\FreeFoodPrice;
use App\FreeReservation;
use App\FreeReservationOpt;
use App\FreeUserGroup;
use App\Inventory;
use App\Library\jdf;
use App\Library\Weekcreator;
use App\Reservation;
use App\RestInfo;
use App\Setting;
use App\User;
use App\UserGroup;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;

class SaledayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(Rbac::check_access('sale-day','index')) {
            $jdf = new jdf();
            $date = $jdf->jdate('Y-m-d');
            $day = $jdf->jdate('l');
            $clock = $jdf->jdate('H:i:s');

            if (Rbac::get_active_temp() == 'stu') {
                $meal = '';
                if ('01:00:00' < $clock && $clock <= '09:30:00')
                    $meal = 'صبحانه';
                elseif ('09:30:00' < $clock && $clock <= '16:00:00')
                    $meal = 'نهار';
                elseif ('16:00:00' < $clock && $clock <= '23:00:00')
                    $meal = 'شام';
                session()->forget('manualOrSaleData');
                $foodTmp = [];
                $ddfs = Menu::where('date', $date)->where('day', $day)->where('meal', $meal)->get();
                foreach ($ddfs as $ddf) {
                    $food = $ddf->food_menu;
                    $foodTmp[] = (object)[
                        'title' => isset($food->title) ? $food->title : '',
                        'ddfId' => $ddf->id,
                    ];
                }
                return view('cms.sale-day.index', [
                    'meal' => $meal,
                    'foods' => $foodTmp,
                ]);
            }
            elseif (Rbac::get_active_temp() == 'freeSelf') {
                $meal = '';
                if ('01:00:00' < $clock && $clock <= '09:00:00')
                    $meal = 1;
                elseif ('09:00:00' < $clock && $clock <= '16:00:00')
                    $meal = 2;
                elseif ('16:00:00' < $clock && $clock <= '23:00:00')
                    $meal = 3;
                $ddfs = FreeDdf::where('date',$date)->where('meal',$meal)->where('active',1)->get();
                $ddos = FreeDdo::where('date',$date)->where('meal',$meal)->where('active',1)->get();
                $day  = $jdf->jdate('l');
                return view('cms.sale-day.index', [
                    'date'  => $date,
                    'day'   => $day,
                    'foods' => $ddfs,
                    'opts'  => $ddos,
                ]);
            }
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function date_changed(Request $request)
    {
        if(Rbac::check_access('sale-day','date_changed')) {
            $jdf = new jdf();
            $clock = $jdf->jdate('H:i:s');
            $date = $request->json()->get('date');
            if (Rbac::get_active_temp() == 'stu') {
                $meal = '';
                if ('01:00:00' < $clock && $clock <= '09:30:00')
                    $meal = 'صبحانه';
                elseif ('09:30:00' < $clock && $clock <= '15:00:00')
                    $meal = 'نهار';
                elseif ('15:00:00' < $clock && $clock <= '23:00:00')
                    $meal = 'شام';

                $ddfs = Menu::where('date', $date)->where('meal', $meal)->get();
                $foodHtml = '';
                foreach ($ddfs as $ddf) {
                    $food = $ddf->food_menu;
                    $foodHtml .= '<option value="' . $ddf->id . '">' . (isset($food->title) ? $food->title : "بدون عنوان") . '</option>';
                }

                $mealTypes = ['صبحانه', 'نهار', 'شام',];
                $mealHtml = '';
                foreach ($mealTypes as $mealType) {
                    if ($mealType == $meal)
                        $mealHtml .= '<option value="' . $mealType . '" selected>' . $mealType . '</option>';
                    else
                        $mealHtml .= '<option value="' . $mealType . '">' . $mealType . '</option>';
                }
                return response()->json(['status' => true, 'meal' => $mealHtml, 'food' => $foodHtml]);
            }
            elseif (Rbac::get_active_temp() == 'freeSelf') {
                $v = \Illuminate\Support\Facades\Validator::make(['date'=>$date],[
                    'date' => 'required|date_format:Y-m-d',
                ]);
                if($v->fails())
                    return response()->json(['status' => 101, 'فرمت تاریخ صحیح نیست.به صورت 00-00-0000 وارد کنید']);
                $meal = '';
                if ('01:00:00' < $clock && $clock <= '09:00:00')
                    $meal = 1;
                elseif ('09:00:00' < $clock && $clock <= '16:00:00')
                    $meal = 2;
                elseif ('16:00:00' < $clock && $clock <= '23:00:00')
                    $meal = 3;

                $ddfs = FreeDdf::where('date',$date)->where('meal',$meal)->where('active',1)->get();
                $ddos = FreeDdo::where('date',$date)->where('meal',$meal)->where('active',1)->get();
                $foodsTmp = '';
                $optsTmp = '';
                foreach ($ddfs as $i=>$ddf) {
                    $nimPors = $ddf->pors == 'پرس'? '' : '<span class="badge badge-warning p-1">نیم پرس </span>';
                    $foodsTmp .= '<tr>
                                      <td class="text-right align-middle">'.($i+1).'</td>
                                      <td class="text-center align-middle">
                                      <div class="form-check mt-0">
                                          <label class="form-check-label">
                                              <input class="form-check-input" name="chosenFoods[]" type="checkbox" value="'.$ddf->id.'">
                                              <span class="form-check-sign"></span> &nbsp;
                                          </label>
                                      </div>
                                      </td>
                                      <td class="text-center align-middle">'.$ddf->food_menu->title.$nimPors.'</td>
                                      <td class="text-center align-middle"><input type="number" class="text-center" min="1" name="food-count-'.$ddf->id.'"></td>
                                   </tr>';
                }
                foreach ($ddos as $i=>$ddo) {
                    $optsTmp .= '<tr>
                                     <td class="text-right align-middle">'.($i+1).'</td>
                                     <td class="text-center align-middle">
                                         <div class="form-check mt-0">
                                             <label class="form-check-label">
                                                 <input class="form-check-input" name="chosenOpts[]" type="checkbox" value="'.$ddo->id.'">
                                                 <span class="form-check-sign"></span> &nbsp;
                                             </label>
                                         </div>
                                     </td>
                                     <td class="text-center align-middle">'.$ddo->opt->title.'</td>
                                     <td class="text-center align-middle"><input type="number" class="text-center" min="1" name="opt-count-'.$ddo->id.'"></td>
                                  </tr>';
                }
                return response()->json(['status' => 200, 'opts' => $optsTmp, 'foods' => $foodsTmp]);
            }
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function meal_changed(Request $request)
    {
        if(Rbac::check_access('sale-day','meal_changed')) {
            $date = $request->json()->get('date');
            $meal = $request->json()->get('meal');
            $ddfs = Menu::where('date', $date)->where('meal', $meal)->get();
            $foodHtml = '';
            foreach ($ddfs as $ddf) {
                $food = $ddf->food_menu;
                $foodHtml .= '<option value="' . $ddf->id . '">' . (isset($food->title) ? $food->title : "بدون عنوان") . '</option>';
            }
            return response()->json(['status' => true, 'food' => $foodHtml]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    static public function get_price($foodId,$usergroupId,$meal,$ddfId)
    {
        $foodPriceId = DdfFoodPrice::where('usergroup_id',$usergroupId)->where('ddf_id',$ddfId)->orderBy('id','desc')->first();
        if($foodPriceId) {
            $foodPrice = FoodPrice::find($foodPriceId->foodprice_id);
            return $foodPrice;
        }
        $foodPrice = FoodPrice::where('foodmenu_id', $foodId)->where('usergroup_id', $usergroupId)->where('meal',$meal)->where('active',1)->where('saleDay',0)->orderBy('id','desc')->first();
        return $foodPrice;
    }

    public function check_info(Request $request)
    {
        if(Rbac::check_access('sale-day','check_info')) {
            session()->forget('manualOrSaleData');
            $ddfId    = $request->json()->get('id');
            $identify = $request->json()->get('identify');
            $resType  = $request->json()->get('type');
            $date     = $request->json()->get('date');
            $meal     = $request->json()->get('meal');
            $ddf = Menu::find($ddfId);
            if (!$ddf)
                return response()->json(['status' => 101, 'res' => 'اطلاعات ورودی نامعتبر است']);
            if ($ddf->meal != $meal || $ddf->date != $date)
                return response()->json(['status' => 101, 'res' => 'اطلاعات ورودی نامعتبر است']);

            $user = DB::table('users')->where('username', $identify)->orWhere('std_no', $identify)->first();
            if (!$user)
                return response()->json(['status' => 101, 'res' => 'اطلاعات کاربر پیدا نشد']);

            $dataForSession = [
                'meal' => $meal,
                'ddfId' => $ddfId,
                'userId' => $user->id,
                'resType' => $resType,
                'date' => $date,
            ];

            $wallet = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $tbl = '<table class="table table-responsive table-striped table-bordered">
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
            if (!$wallet)
                $tbl .= '<td  class="text-center ltr" id="tblAmount">0</td></tr></tbody></table>';
            else
                $tbl .= '<td  class="text-center ltr" id="tblAmount">' . $wallet->amount . '</td></tr></tbody></table>';

            $reservedTbl = '<table class="table table-responsive table-striped table-bordered mt-2">
                <thead>
                <tr>
                    <th class="text-center bg-warning text-white" colspan="3">رزرو شده است!</th>
                </tr>
                <tr>
                    <th class="text-center">نوع غذا</th>
                    <th class="text-center">وضعیت</th>
                    <th class="text-center">لغو</th>
                </tr>
                </thead>
                <tbody>';
            $ddfs = Menu::where('date', $date)->where('meal', $meal)->get();
            foreach ($ddfs as $ddfDate) {
                $reservation = Reservation::where('ddf_id', $ddfDate->id)->where('user_id', $user->id)->first();
                if ($reservation) {
                    $food = Food::find($ddfDate->food_id);
                    $dataForSession['resId'] = $reservation->id;
                    session()->put('manualOrSaleData', $dataForSession);
                    $reservedTbl .= '<tr>
                                    <td>' . $food->title . '</td>
                                    <td>' . ($reservation->eaten == 0 ? 'استفاده نشده' : 'استفاده شده') . '</td>
                                    <td><a class="btn btn-warning" href="javascript:void(0)" id="cancelReserve"><i class="fa fa-ban"></i> </a></td>
                                 </tr>
                                 </tbody>
                                 </table>';
                    return response()->json(['status' => 106, 'info' => $tbl, 'reserved' => $reservedTbl]);
                }
            }
            session()->put('manualOrSaleData', $dataForSession);
            if ($resType == 2) {
                $thirtyDayAgo = date('Y-m-d H:i:s', (int)time() - 30 * 86400);
                $reservationCheck = Reservation::where('created_at', '>=', $thirtyDayAgo)->where('user_id', $user->id)->where('saleDay', 1)->count();
                if ($reservationCheck >= 3) {
                    $tbl2 = '<table class="table table-responsive table-striped table-bordered">
                <thead>
                <tr>
                    <th colspan="3" class="text-center text-danger">روز فروش امکان پذیر نیست</th>
                </tr>
                </thead>
                <tbody>';
                    $threeReserve = Reservation::where('created_at', '>=', $thirtyDayAgo)->where('user_id', $user->id)->where('saleDay', 1)->get();
                    $i = 1;
                    $jdf = new jdf();
                    foreach ($threeReserve as $value) {
                        $ddf = Menu::find($value->ddf_id);
                        $food = Food::find($ddf->food_id);
                        $created_at = $jdf->getPersianDate($value->created_at, 'Y-m-d H:i:s');
                        $tbl2 .= '<tr>
                                <td>' . $i . '</td>
                                <td>' . $food->title . '</td>
                                <td>' . $created_at . '</td>
                            </tr>';
                        $i++;
                    }
                }
                $food = Food::find($ddf->food_id);
                $usergroup = UserGroup::where('kindid', $user->kindid)->first();
                $foodPrices = FoodPrice::where('foodmenu_id', $food->id)->where('usergroup_id', $usergroup->id)->where('saleDay', 1)->where('active', 1)->orderBy('id', 'desc')->get();
                $pricesTbl = '<div class="card border-primary">
                              <div class="card-body">
                                  <h4 class="card-title text-dark text-center">انتخاب قیمت برای غذا</h4>
                              </div>
                              <ul class="list-group list-group-flush mb-3" id="prices">';
                foreach ($foodPrices as $foodPrice) {
                    $pricesTbl .= '<li class="list-group-item font-weight-bold" style="cursor: pointer">
                                   <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" value="' . $foodPrice->id . '" name="prices">
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description"> ' . $foodPrice->price . '</span>
                                   </label>
                               </li>';
                }
                $pricesTbl .= '</ul>
                           <div class="col-sm-12 mb-3 mt-3">
                               <label class="custom-control custom-checkbox">
                                   <input type="checkbox" class="custom-control-input" id="newPriceCh">
                                   <span class="custom-control-indicator"></span>
                                   <span class="custom-control-description"> اعمال قیمت جدید(ریال)</span>
                               </label>
                               <input type="text" class="form-control d-none" id="newPrice">
                               <div class="invalid-feedback"></div>
                           </div>
                           <div class="col-sm-12 mb-3">
                               <button class="btn btn-secondary btn-block" type="button" id="submitRes">ثبت نهایی رزرو</button>
                           </div>
                           </div>';
                if (isset($tbl2))
                    return response()->json(['status' => 103, 'info' => $tbl, 'deny' => $tbl2, 'price' => $pricesTbl]);
                return response()->json(['status' => 103, 'info' => $tbl, 'price' => $pricesTbl]);
            }
            elseif ($resType == 1) {
                $food = Food::find($ddf->food_id);
                $usergroup       = UserGroup::where('kindid', $user->kindid)->first();
                $specifiedPrice  = self::get_price($food->id,$usergroup->id,$meal,$ddf->id);
                $foodPrices      = FoodPrice::where('foodmenu_id', $food->id)->where('usergroup_id', $usergroup->id)->where('meal',$meal)->where('saleDay', 0)->where('active', 1)->orderBy('id', 'desc')->get();
                $pricesTbl  = '<div class="card border-primary">
                              <div class="card-body">
                                  <h4 class="card-title text-dark text-center">انتخاب قیمت برای غذا</h4>
                              </div>
                              <ul class="list-group list-group-flush mb-3" id="prices">';
                foreach ($foodPrices as $foodPrice) {
                    $pricesTbl .= '<li class="list-group-item font-weight-bold" style="cursor: pointer">
                                   <label class="custom-control custom-radio">';
                    if($foodPrice->id == $specifiedPrice->id)
                        $pricesTbl .= '<input type="radio" class="custom-control-input" value="' . $foodPrice->id . '" name="prices" checked>';
                    else
                        $pricesTbl .= '<input type="radio" class="custom-control-input" value="' . $foodPrice->id . '" name="prices">';
                    $pricesTbl .= '<span class="custom-control-indicator"></span>
                                        <span class="custom-control-description"> ' . $foodPrice->price . '</span>
                                   </label>
                                   <div class="invalid-feedback"></div>
                               </li>';
                }
                $pricesTbl .= '</ul>
                           <div class="col-sm-12 mb-3 mt-3">
                               <label class="custom-control custom-checkbox">
                                   <input type="checkbox" class="custom-control-input" id="newPriceCh">
                                   <span class="custom-control-indicator"></span>
                                   <span class="custom-control-description"> اعمال قیمت جدید(ریال)</span>
                               </label>
                               <input type="text" class="form-control d-none" id="newPrice">
                               <div class="invalid-feedback"></div>
                           </div>
                           <div class="col-sm-12 mb-3">
                               <button class="btn btn-secondary btn-block" type="button" id="submitRes">ثبت نهایی رزرو</button>
                           </div>
                           </div>';
                return response()->json(['status' => 104, 'info' => $tbl, 'price' => $pricesTbl]);
            }
            return response()->json(['status' => 105, 'res' => 'انجام عمل فوق امکان پذیر نمی باشد.لطفا در مقادیر ارسلی را صحیح وارد کنید']);
        }
        return response()->json(['status' => 105, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function set_reserve(Request $request)
    {
        if(Rbac::check_access('sale-day','set_reserve')) {
            if (Rbac::get_active_temp() == 'stu') {
                if (!session()->has('manualOrSaleData'))
                    return response()->json(['status' => 101, 'res' => 'لطفاً مراحل را مجدداً انجام دهید']);
                $newPrice = $request->json()->get('newPrice');
                $oldPriceId = $request->json()->get('price');

                $sessionData = session('manualOrSaleData');
                $ddfId = $sessionData['ddfId'];
                $userId = $sessionData['userId'];
                $resType = $sessionData['resType'];
                $meal = $sessionData['meal'];
                $date = $sessionData['date'];

                $ddf = Menu::find($ddfId);
                $user = User::find($userId);
                $food = Food::find($ddf->food_id);
                $usergroup = UserGroup::where('kindid', $user->kindid)->first();

                $ddfs = Menu::where('date', $date)->where('meal', $meal)->get();
                foreach ($ddfs as $ddfData) {
                    $reservation = Reservation::where('ddf_id', $ddfData->id)->where('user_id', $user->id)->first();
                    if ($reservation)
                        return response()->json(['status' => 105, 'res' => 'قبلاً این وعده رزرو شده است']);
                }

                $setting = Setting::first();
                $saleDayMinCash = $setting->saleDayMinCash;

                $wallet = Wallet::where('user_id', $userId)->orderBy('id', 'desc')->first();
                if (!$wallet)
                    $amount = 0;
                else
                    $amount = $wallet->amount;
                if ($newPrice != '' && $newPrice != null) {
                    $minCash = $amount - $newPrice;
                    if ($minCash < $saleDayMinCash && $newPrice != 0)
                        return response()->json(['status' => 102, 'res' => 'موجودی کاربر در این لحظه کافی نمی باشد', 'amount' => $amount]);

                    $saleDay = ($resType == 2) ? 1 : 0;
                    $foodPrice = FoodPrice::where('price', $newPrice)->where('foodmenu_id', $food->id)->where('usergroup_id', $usergroup->id)->where('saleDay', $saleDay)->where('meal', $meal)->first();
                    if (!$foodPrice) {
                        $foodPrice = new FoodPrice();
                        $foodPrice->price = $newPrice;
                        $foodPrice->foodmenu_id = $food->id;
                        $foodPrice->usergroup_id = $usergroup->id;
                        $foodPrice->saleDay = $saleDay;
                        $foodPrice->meal = $meal;
                        $foodPrice->save();
                    }

                    $rs = new Reservation();
                    $rs->ddf_id = $ddf->id;
                    $rs->user_id = $user->id;
                    $rs->foodprice_id = $foodPrice->id;
                    $rs->saleDay = ($resType == 2) ? 1 : 0;
                    $rs->save();

                    $wl = new Wallet();
                    $wl->amount = $amount - $newPrice;
                    $wl->user_id = $user->id;
                    $wl->save();
                    return response()->json(['status' => 200, 'res' => 'رزرو انجام شد']);
                } elseif ($oldPriceId != '' && $oldPriceId != null) {
                    $foodPrice = FoodPrice::find($oldPriceId);
                    $minCash = $amount - $foodPrice->price;
                    if ($minCash < $saleDayMinCash && $foodPrice->price != 0)
                        return response()->json(['status' => 102, 'res' => 'موجودی کاربر در این لحظه کافی نمی باشد', 'amount' => $amount]);

                    $rs = new Reservation();
                    $rs->ddf_id = $ddf->id;
                    $rs->user_id = $user->id;
                    $rs->foodprice_id = $foodPrice->id;
                    $rs->saleDay = ($resType == 2) ? 1 : 0;
                    $rs->save();

                    $wl = new Wallet();
                    $wl->amount  = $amount - $foodPrice->price;
                    $wl->user_id = $user->id;
                    $wl->save();
                    return response()->json(['status' => 200, 'res' => 'رزرو انجام شد']);
                }
            }
            elseif (Rbac::get_active_temp() == 'freeSelf') {
                $v = \Illuminate\Support\Facades\Validator::make($request->all(),[
                    'chosenFoods'    => 'nullable|array',
                    'chosenFoods.*'  => 'nullable|numeric|digits_between:1,11|exists:free_ddf,id',
                    'chosenOpts'     => 'nullable|array',
                    'chosenOpts.*'   => 'nullable|numeric|digits_between:1,11|exists:free_ddo,id',
                    'date'           => 'required|date_format:Y-m-d',
                    'user-mode'      => 'required|in:user,out',
                    'uid'            => 'nullable|required_if:user-mode,user|string',
                    'out-add-wallet' => 'nullable|numeric',
                ],[
                    //'chosenFoods.required'         => 'حداقل یک غذا را انتخاب کنید',
                    'chosenFoods.array'            => 'لیست غذاهای انتخابی نامعتبر است',
                    //'chosenFoods.*.required'       => 'حداقل یک غذا را انتخاب کنید',
                    'chosenFoods.*.numeric'        => 'غذای انتخاب شده نامعتبر است',
                    'chosenFoods.*.digits_between' => 'غذای انتخاب شده نامعتبر است',
                    'chosenFoods.*.exists'         => 'منو غذا پیدا نشد',
                    'chosenOpts.array'             => 'لیست مخلفات انتخابی نامعتبر است',
                    'chosenOpts.*.required'        => 'حداقل یکی از مخلفات را انتخاب کنید',
                    'chosenOpts.*.numeric'         => 'مخلفات انتخاب شده نامعتبر است',
                    'chosenOpts.*.digits_between'  => 'مخلفات انتخاب شده نامعتبر است',
                    'chosenOpts.*.exists'          => 'مخلفات انتخابی در سیستم پیدا نشد',
                    'user-mode.required'           => 'ثبت رزرو برای چه کاربری انجام می شود؟',
                    'user-mode.in'                 => 'مقدار ثبت رزرو نامعتبر است',
                    'date.required'                => 'تاریخ را انتخاب کنید',
                    'date.date_format'             => 'فرمت تاریخ نامعتبر است',
                    'uid.required_if'              => 'شناسه کاربری را وارد کنید',
                    'uid.string'                   => 'شناسه کاربری نامعتبر است',
                    'out-add-wallet.numeric'       => 'مبلغ افزایش اعتبار باید عدد و به ریال باشد',
                ]);
                if($v->fails())
                    return response()->json(['status' => 101, 'res' => $v->errors()]);

                $userMode    = $request->get('user-mode');
                $chosenFoods = $request->has('chosenFoods') ? $request->chosenFoods : [];
                $chosenOpts  = $request->has('chosenOpts') ? $request->chosenOpts : [];
                $date        = $request->date;
                $mealTrans   = [
                    1 => 'صبحانه',
                    2 => 'نهار',
                    3 => 'شام',
                ];
                $pricesFood  = [];


                if($userMode == 'user') {
                    $credential = $request->uid;
                    $user       = DB::table('users')->where('std_no', $credential)->orWhere('national_code', $credential)->first();
                    if(!$user)
                        return response()->json(['status' => 101, 'res' => ['uid' => ['مشخصات کاربر پیدا نشد']]]);
                }
                else {
                    $user = User::where('username','rest-out-user')->first();
                    if(!$user) {
                        $user = new User();
                        $user->username = 'rest-out-user';
                        $user->name     = 'مشتری بیرون دانشگاه';
                        $user->family   = 'غیر دانشگاهی';
                        $user->active   = 1;
                        $user->sso      = 0;
                        $user->save();
                    }
                    $outAddWallet = $request->out_add_wallet;
                    if($outAddWallet != 0) {
                        $checkWallet = Wallet::where('user_id',$user->id)->orderBy('id','desc')->first();
                        if(!$checkWallet)
                            $newAmount = $outAddWallet;
                        else
                            $newAmount = $outAddWallet + $checkWallet->amount;

                        $newWallet                 = new Wallet();
                        $newWallet->amount         = $newAmount;
                        $newWallet->value          = $outAddWallet;
                        $newWallet->_for           = 'افزایش اعتبار دستی کاربری غیردانشگاهی';
                        $newWallet->user_id        = $user->id;
                        $newWallet->save();
                    }
                }

                $wc = new Weekcreator(['userId'=>$user->id]);
                $foodString = '';
                foreach ($chosenFoods as $i=>$ddfId) {
                    $count = $request->get('food-count-'.$ddfId);
                    if (!is_numeric($ddfId) || !is_numeric($count))
                        return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                    if (strlen($ddfId) > 11)
                        return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                    if ($count < 1 || $ddfId < 1)
                        return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);

                    //check ddf expired or de-actived or date passed
                    $response = $wc->check_dd_status($ddfId,'ddf',$count,'saleday');
                    if ($response['status'] === false)
                        return response()->json(['status'=>102,'res'=>$response['msg']]);

                    $chosenFoods[$i] = $ddfId.'-'.$count;

                    $ddf      = FreeDdf::find($ddfId);
                    $ddfPrice = $wc->get_price($ddf->food_id,$mealTrans[$ddf->meal],'آزاد',$ddf->pors);
                    if ($ddfPrice === false) {
                        $food = $ddf->food_menu;
                        return response()->json(['status' => 102, 'res' => 'قیمت ' . $food->title . ' در سیستم تعریف نشده است']);
                    }
                    if($userMode == 'user') {
                        $discountPrice = $wc->get_price($ddf->food_id, $mealTrans[$ddf->meal], null,$ddf->pors);
                        $discountPrice = isset($discountPrice['price']) ? $discountPrice['price'] : 0;
                        $cdc = $wc->calculate_difference_costs($ddfId, $ddfPrice['price'], $discountPrice, $count, 'ddf', $user->id);
                    }
                    else
                        $cdc = $wc->calculate_difference_costs($ddfId, $ddfPrice['price'], 0, $count, 'ddf', $user->id);
                    if($cdc['status'] === false)
                        return response()->json(['status'=>102,'res'=>$cdc['res']]);

                    $pricesFood[$i] = $cdc['currentPrice'];

                    $food = $ddf->food_menu;
                    $foodString .= '~'.$food->title.'~';
                }

                if(!empty($chosenFoods)) {
                    $subAndAdd = $wc->get_difference_costs();
                    $totalCount = $wc->get_difference_counts();

                    $walletProcess = $wc->wallet_process($subAndAdd['priceSub'], $subAndAdd['priceAdd'], $totalCount, $foodString, $user->id);
                    if ($walletProcess['status'] === false)
                        return response()->json(['status' => 102, 'res' => $walletProcess['msg']]);

                    $reserveRes = $wc->reserve($chosenFoods, $pricesFood, 'food', $user->id);
                }
                if ($chosenOpts) {
                    $priceIdsOpt = [];
                    unset($wc);
                    $wc = new Weekcreator();
                    $optString = '';
                    foreach ($chosenOpts as $i => $ddoId) {
                        $count = $request->get('opt-count-'.$ddoId);
                        if (!is_numeric($ddoId) || !is_numeric($count))
                            return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                        if (strlen($ddoId) > 11)
                            return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                        if ($count < 0 || $ddoId < 1)
                            return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);

                        $chosenOpts[$i] = $ddoId.'-'.$count;

                        $ddo = FreeDdo::find($ddoId);
                        $ddoPrice = $wc->get_price($ddo->opt_id, $mealTrans[$ddo->meal],'آزاد');
                        if ($ddoPrice === false) {
                            $opt = $ddo->opt;
                            return response()->json(['status' => 102, 'res' => 'قیمت ' . $opt->title . ' در سیستم تعریف نشده است']);
                        }

                        $cdc = $wc->calculate_difference_costs($ddoId, $ddoPrice['price'],0, $count, 'ddo',$user->id);
                        if($cdc['status'] === false)
                            return response()->json(['status'=>102,'res'=>$cdc['res']]);

                        $priceIdsOpt[$i] = $ddoPrice['id'];
                        $opt = $ddo->opt;
                        $optString .= '~'.$opt->title.'~';
                    }

                    $subAndAdd = $wc->get_difference_costs();
                    $totalCount = $wc->get_difference_counts();
                    $walletProcess = $wc->wallet_process($subAndAdd['priceSub'], $subAndAdd['priceAdd'], $totalCount, $optString, $user->id);
                    if ($walletProcess['status'] === false)
                        return response()->json(['status' => 102, 'res' => $walletProcess['msg']]);

                    $reserveRes = $wc->reserve($chosenOpts, $priceIdsOpt, 'opt', $user->id);

                    if($reserveRes['status'] === true) {
                        $ddfs = FreeDdf::where('date', $date)->where('meal', 2)->get();
                        $ddos = FreeDdo::where('date', $date)->where('meal', 2)->get();
                        $userOrder = self::get_free_user_order($user,$ddfs,$ddos,$date);
                        return response()->json([
                            'status' => 200,
                            'res'    => $reserveRes['msg'],
                            'wallet' => $walletProcess['walletAmount'],
                            'orders' => $userOrder,
                        ]);
                    }
                }
                else {
                    $ddfs = FreeDdf::where('date', $date)->where('meal', 2)->get();
                    $ddos = FreeDdo::where('date', $date)->where('meal', 2)->get();
                    $userOrder = self::get_free_user_order($user,$ddfs,$ddos,$date);
                    return response()->json([
                        'status' => 200,
                        'res'    => isset($reserveRes)? $reserveRes['msg']:'',
                        'wallet' => isset($walletProcess)? $walletProcess['walletAmount']: 0,
                        'orders' => $userOrder,
                    ]);
                }
            }
            return response()->json(['status' => 104, 'res' => 'انجام رزرو ناموفق بود']);
        }
        return response()->json(['status' => 105, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function get_free_user_order($user,$ddfs,$ddos = null,$date = '',$markAsEaten = 0,$clock = '')
    {
        $eatenAt  = $date.' '.$clock;
        $eatenIp  = \Request::ip();
        $eatenIn  = null;
        $restInfo = RestInfo::where('ip',$eatenIp)->first();
        if($restInfo)
            $eatenIn = $restInfo->id;

        $userImg = $user->img != "" ? $user->img : "/img/prof-default.png";

        $orderView = "<div class='row'>".
            "   <div class='col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12' id='order-tbl'>".
            "       <div class='card w-100'>".
            "           <div class='card-header' style='font-family: BPersianGulf; font-size: 13px; text-align: center'><img src='/img/logo.png' width='90%'> </div>".
            "           <div class='card-body'>".
            "               <div class='table-responsive'>".
            "                  <table class='table table-striped table-bordered table-sm' border='2' width='100%' dir='rtl' style='font-family: IRANYekanWeb; font-size: 13px'>".
            "                      <thead>".
            "                      <tr style='background-color: #a7a7a7' >".
            "                         <th class='text-right' style='background-color: #a7a7a7'>نام</th>".
            "                         <th class='text-right'>کد ملی</th>".
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
            "               </div>".
            "               <div class='table-responsive'>".
            "                  <table class='table table-striped table-bordered table-sm' border='2' width='100%' dir='rtl'style='font-family: IRANYekanWeb; font-size: 13px'>".
            "                      <thead>".
            "                      <tr bgcolor='#a7a7a7'>".
            "                         <th class='text-right'>تاریخ</th>".
            "                      </tr>".
            "                      </thead>".
            "                      <tbody>".
            "                         <tr>".
            "                            <td style='text-align: center'>$date".
            "                            </td>".
            "                         </tr>".
            "                      </tbody>".
            "                   </table>".
            "               </div>".
            "               <div class='table-responsive' id='foodTable'>".
            "                   <table class=' table-bordered table-sm' border='2' width='100%' dir='rtl' style='font-family: IRANYekanWeb; font-size: 13px'>".
            "                       <thead>".
            "                       <tr bgcolor='#a7a7a7'>".
            "                           <th class='text-right'>سفارش</th>".
            "                           <th class='text-right'>تعداد</th>".
            "                           <th class='text-right'>قیمت(R)</th>".
            "                           <th class='text-right status-col'>حالت</th>".
            "                           <th class='text-right'>لغو رزرو</th>".
            "                       </tr>".
            "                       </thead>".
            "                       <tbody>";
        $priceSum = 0;
        $count    = 0;
        $discountAmount = "-";
        $autoPrint = "";
        $haveNotReserve = true;
        foreach ($ddfs as $ddf) {
            $reserve = $ddf->reservation()->where('user_id', $user->id)->where('active', 1)->first();
            //$nimPors = $ddf->pors == "پرس" ? "" : "<span class='badge badge-warning p-1'>ن پ </span>";
            $nimPors = "";
            $foodTitle = $ddf->food_title;
            if(isset($ddf->desserts[0]->id)) {
                foreach ($ddf->desserts as $dessert)
                    $foodTitle .= ' | '.$dessert->title;
            }

            if($reserve) {
                $haveNotReserve = false;
                if($reserve->eaten == 1) {
                    $date1 = new \DateTime($reserve->eaten_at);
                    $date2 = new \DateTime(Date('Y-m-d H:i:s'));
                    $intervalDay   = (int)($date1->diff($date2)->format('%R%a'));
                    $intervalHour  = (int)($date1->diff($date2)->format('%R%h'));
                    $intervalMin   = (int)($date1->diff($date2)->format('%R%i'));
                    $intervalSec   = (int)($date1->diff($date2)->format('%R%s'));
                    $totalInterval = ($intervalDay * 1440 * 60)+ ($intervalHour * 60 * 60) + ($intervalMin * 60) + $intervalSec;
                    if((int)$totalInterval > 5)
                        $checkBox    = "<label class='text-danger'>استفاده شده در <strong>$reserve->updated_at()</strong></label>";
                    else {
                        $checkBox = "<label class='text-success'>قابل استفاده</label>";
                        $autoPrint = "auto-print";
                    }
                }
                else {
                    $checkBox = "<label class='text-success'>قابل استفاده</label>";
                    if($markAsEaten) {
                        $reserve->eaten = $markAsEaten;
                        $reserve->eaten_in = $eatenIn;
                        $reserve->eaten_ip = $eatenIp;
                        $reserve->eaten_at = $eatenAt;
                        $reserve->update();
                    }
                    $autoPrint = "auto-print";
                }

                $hasDiscount = "";
                if($reserve->discount_count > 0) {
                    $hasDiscount    = "<span class='text-info'>*</span>";
                    $discountAmount = $reserve->discount_amount;
                }
                $orderView .= "<tr>".
                              "    <td class='text-right'>$foodTitle $hasDiscount</td>".
                              "    <td class='text-right'>$reserve->count</td>".
                              "    <td class='text-right'>$reserve->pay_amount</td>".
                              "    <td class='text-right status-col'>$checkBox</td>".
                              "    <td class='text-right status-col'><button id='$reserve->id-food-$user->id' class='btn btn-danger cancel-reserve'>لغو</button></td>".
                              "</tr>";
                $priceSum += $reserve->pay_amount;
                $count    += $reserve->count;
            }
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
                "    <td class='text-right'></td>".
                "</tr>".
                "<tr>".
                "    <td class='text-center'>سوبسید</td>".
                "    <td class='text-right'></td>".
                "    <td class='text-right'>$discountAmount</td>".
                "    <td class='text-right status-col'></td>".
                "    <td class='text-right'></td>".
                "</tr>";
        $orderView .= "</tbody></table></div>";
        $orderView .= "<div class='row'>".
                     "    <div class='col-xl-4 col-lg-4 col-md-4 col-sm-4 col-6'>".
                     "        <p class='mb-1'><button class='btn btn-light $autoPrint' id='print'><i class='fa fa-print'></i></button></p>".
                     "    </div>".
                     "</div>";

        $orderView .="</div></div></div>".
            "    <div class='col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12'>".
            "        <div class='card'>".
            "            <div class='card-body'>".
            "                <div class='card-img text-center'>".
            "                    <img src='$userImg' style='width: auto;max-height: 400px'>".
            "                </div>".
            "            </div>".
            "        </div>".
            "    </div></div>";
        return $orderView;
    }

    public function cancel_reserve(Request $request)
    {
        if(Rbac::check_access('sale-day','cancel_reserve')) {
            if (Rbac::get_active_temp() == 'stu') {
                if (!session()->has('manualOrSaleData'))
                    return response()->json(['status' => false, 'res' => 'لطفاً مراحل را مجدداً انجام دهید']);

                $sessionData = session('manualOrSaleData');
                $resId = $sessionData['resId'];
                $userId = $sessionData['userId'];

                $reservation = Reservation::find($resId);
                $price = FoodPrice::find($reservation->foodprice_id);
                if (!$price)
                    return response()->json(['status' => false, 'res' => 'قیمت این غذا از سیستم حذف شده است و امکان لغو وجود ندارد']);
                $wallet = Wallet::where('user_id', $userId)->orderBy('id', 'desc')->first();
                $wl = new Wallet();
                $wl->amount = $wallet->amount + $price->price;
                $wl->user_id = $userId;
                $wl->save();
                $reservation->delete();
                return response()->json(['status' => true, 'res' => 'رزرو لغو و مبلغ غذا به حساب کاربر افزوده شد']);
            }
            elseif (Rbac::get_active_temp() == 'freeSelf') {
                $v = Validator::make(['dd'=>$request->dd],[
                    'dd' => 'required|string',
                ]);
                if($v->fails())
                    return response()->json(['status' => 101, 'اطلاعات رزرو پیدا نشد']);

                $dd   = $request->dd;
                $dd   = explode('-',$dd);
                $frId   = isset($dd[0])?$dd[0]:null;
                $type   = isset($dd[1])?$dd[1]:null;
                $userId = isset($dd[2])?$dd[2]:null;

                if($type != 'opt' && $type != 'food')
                    return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                if (!is_numeric($frId))
                    return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                if (strlen($frId) > 11)
                    return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                if ($frId < 1)
                    return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);

                $user = User::find($userId);
                if(!$user)
                    return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);

                if($type == 'opt') {
                    $opt = FreeReservationOpt::where('id', $frId)->where('user_id', $userId)->where('active',1)->first();
                    if (!$opt)
                        return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                    $ddo = $opt->ddo;
                    $optTitle = $ddo->opt->title;
                    $wc = new Weekcreator();
                    $result = $wc->cancel_reserve($opt, 'opt','sale-day');
                    if ($result['status'] === false)
                        return response()->json(['status' => 102, 'res' => $result['msg']]);
                    $cwResult = $wc->cancel_wallet($opt->count, $opt->price_id,$type,$optTitle);
                    if ($cwResult['status'] === false)
                        return response()->json(['status' => 102, 'res' => $cwResult['msg']]);
                    return response()->json([
                        'status' => 200,
                        'res'    => 'رزرو مخلفات لغو شد',
                    ]);
                }
                else {
                    $food = FreeReservation::where('id', $frId)->where('user_id', Auth::user()->id)->where('active',1)->first();
                    if (!$food)
                        return response()->json(['status' => 102, 'res' => 'اطلاعات ارسال شده نامعتبر است.']);
                    $ddf = $food->ddf;
                    $foodTitle = $ddf->food_menu->title;
                    $wc = new Weekcreator();
                    $result = $wc->cancel_reserve($food, 'food','sale-day');
                    if ($result['status'] === false)
                        return response()->json(['status' => 102, 'res' => $result['msg']]);
                    $cwResult = $wc->cancel_wallet($food->count, $food->price,$type,$foodTitle);
                    if ($cwResult['status'] === false)
                        return response()->json(['status' => 102, 'res' => $cwResult['msg']]);
                    return response()->json([
                        'status' => 200,
                        'res'    => 'رزرو غذا لغو شد',
                    ]);
                }
            }
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function check_reserve(Request $request)
    {
        if(Rbac::check_access('sale-day','check_reserve')) {
            $v = Validator::make($request->all(),[
                'credential'  => 'required|string',
                'date'        => 'required|date_format:Y-m-d',
            ]);
            if($v->fails())
                return response()->json(['status' => 101, 'res' => $v->errors()]);

            $uid  = $request->credential;
            $date = $request->date;
            $meal = 2;

            $user = DB::table('users')->where('std_no', $uid)->orWhere('national_code', $uid)->where('active', 1)->first();
            if (!$user)
                return response()->json(['status' => false, 'res' => 'اطلاعات کاربر پیدا نشد']);

            $img  = $user->img?$user->img:'/img/avatar.png';
            $name = $user->name;

            $ddfs = FreeDdf::where('date', $date)->where('meal', $meal)->get();
            $ddos = FreeDdo::where('date', $date)->where('meal', $meal)->get();

            $view = '<div class="card" style="width: 18rem;">
                        <img src="'.$img.'" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">'.$name.'</h5>
                        </div>';

            if(isset($ddfs[0]->id) || isset($ddos[0]->id))
                $view .= '<ul class="list-group list-group-flush pr-0">';

            foreach ($ddfs as $ddf) {
                $reserve = $ddf->reservation()->where('user_id', $user->id)->where('active', 1)->first();
                $food    = $ddf->food_menu->title;
                if($reserve) {
                    $view .= '<div class="list-group-item">
                                  <span class="float-right">'.$food.'</span>
                                  <span class="float-left"><div class="badge badge-danger p-2">'.$reserve->count.'</div></span>';
                    if($reserve->eaten == 1)
                        $view .= '<span class="float-left"><i class="fas fa-exclamation-triangle"></i> استفاده شده</span>';
                    $view .= '</div>';
                }
            }
            foreach ($ddos as $ddo) {
                $reserve = $ddo->reservation()->where('user_id', $user->id)->where('active', 1)->first();
                $opt     = $ddo->opt->title;
                if($reserve) {
                    $view .= '<div class="list-group-item">
                                  <span class="float-right">'.$opt.'</span>
                                  <span class="float-left"><div class="badge badge-danger p-2">'.$reserve->count.'</div></span>';
                    if($reserve->eaten == 1)
                        $view .= '<span class="float-left"><i class="fas fa-exclamation-triangle"></i> استفاده شده</span>';
                    $view .= '</div>';
                }
            }

            if(isset($ddfs[0]->id) || isset($ddos[0]->id))
                $view .= '</ul>';
            return response()->json(['status' => 200, 'res' => $view]);
        }

        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
