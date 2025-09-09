<?php

namespace App\Http\Controllers\Cms;

use App\Collection;
use App\Dorm;
use App\Library\Ddf;
use App\Menu;
use App\DdfFoodPrice;
use App\Event;
use App\Facades\Activity;
use App\Facades\Rbac;
use App\Food;
use App\FoodPrice;
use App\FreeDdf;
use App\FreeDdo;
use App\FreeFoodMenu;
use App\FreeFoodPrice;
use App\FreeOSE;
use App\FreeRSE;
use App\FreeUserGroup;
use App\Inventory;
use App\Library\jdf;
use App\Library\Resreport;
use App\MenuDessert;
use App\MenuEvent;
use App\MenuUserGroup;
use App\Reservation;
use App\Setting;
use App\User;
use App\UserGroup;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    static public function make_week($weekbeginTimestamp)
    {
        $jdf  = new jdf();
        $week = '<div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm" id="week-tbl">';
        $tbl_head     = '<thead>
                            <tr>
                                <th class="text-center">روز</th>
                                <th class="text-center">تاریخ</th>
                                <th class="text-center"></th>
                            </tr>
                         </thead>
                         <tbody>';
        $week .= $tbl_head;
        $tbody = '';
        for ($i=1; $i<=7; $i++) {
            $currDay  = $jdf->jdate('l',$weekbeginTimestamp);
            $currDate = $jdf->jdate('Y-m-d',$weekbeginTimestamp);
            $date     = explode('-', $jdf->jdate('Y-m-d', $weekbeginTimestamp));
            $date     = $date[0] . $date[1] . $date[2];

            $tbody .= '<tr>
                         <td class="text-center align-middle">' . $currDay . '</td>
                         <td class="text-center align-middle">' . $currDate . '</td>
                         <td class="text-center align-middle"><button type="button" data-date="'.$currDate.'" class="btn btn-primary text-light menu-btn btn-sm">برنامه غذایی</button></td>
                      </tr>';

            $weekbeginTimestamp += 86400;
        }
        $week .= $tbody;
        $week .= '</tbody></table></div>';
        return $week;
    }

    public function add()
    {
        if(Rbac::check_access('define-day-food','add')) {
            $todayTimestamp = time();
            $jdf  = new jdf();
            $dayToweekbegin     = $jdf->jdate('w');
            $weekbeginTimestamp = $todayTimestamp - ($dayToweekbegin * 86400);

            $res = self::make_week($weekbeginTimestamp);
            session()->put('ddf_currWBeginTimestamp',$weekbeginTimestamp);

            $setting    = Setting::first();
            $foods      = Food::where('type',0)
                ->where('is_active', 1)
                ->orderBy('title')
                ->get();
            $desserts   = Food::where('type',1)
                ->where('is_active', 1)
                ->orderBy('title')
                ->get();
            $userGroups = UserGroup::all();
            $events     = Event::where('active', 1)->where('confirmed', 1)->get();
            $firstCollection  = Collection::first();
            $collections      = Collection::all();
            $dorms = Dorm::all();

            $foodList = '<div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm mb-0">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input class="form-control search-food-input" placeholder="جست و جو غذا">
                                </th>
                                <th class="text-center">کپشن</th>
                                <th class="text-center">انتخاب برای منو غذایی</th>
                            </tr>
                            </thead>
                         </table>
                         </div>
                         <div class="table-responsive" style="max-height: 120px; overflow: auto">
                         <table class="table table-striped table-bordered table-sm foods-tbl">
                            <tbody>';
            foreach ($foods as $food) {
                $foodList .= '<tr>
                                  <td class="text-center">' . $food->title . '</td>
                                  <td class="text-center">' . $food->caption . '</td>
                                  <td class="text-center"><button type="button" data-id="' . $food->id . '" class="btn btn-info btn-sm add-to-list-btn">افزودن به لیست</button></td>
                             </tr>';
            }
            $foodList .= '</tbody></table></div>';

            $dessertsList = '<table class="table table-bordered table-sm dessert-tbl" style="width: auto">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="check-all"></th>
                                        <th class="text-center">
                                            <input class="form-control search-desert-input" placeholder="جست و جو">
                                        </th>
                                        <th class="text-center">کپشن</th>
                                    </tr>
                                </thead>
                                <tbody>';
            foreach ($desserts as $dessert)
                $dessertsList .= '<tr>
                                        <td><input type="checkbox" value="'.$dessert->id.'"></td>
                                        <td class="text-center">' . $dessert->title . '</td>
                                        <td class="text-center">' . $dessert->caption . '</td>
                                   </tr>';
            $dessertsList .= '</tbody></table>';


            $userGroupList  = '<table class="table table-bordered table-sm mt-3" style="width: auto">
                            <tbody>';
            $ugSimultaneous = $userGroupList;
            $dormsList = $userGroupList;

            foreach ($userGroups as $userGroup) {
                $userGroupList .= '<tr>
                                        <td><input type="checkbox" value="'.$userGroup->id.'" checked></td>
                                        <td>'.$userGroup->title.'</td>
                                        <td>تعداد رزرو:</td>
                                        <td><input type="number" class="text-center" value="0" style="width: 50px"></td>
                                   </tr>';
                $canReserveCheck = $userGroup->can_reserve ? "checked":"";
                $ugSimultaneous .= '<tr>
                                        <td>'.$userGroup->title.'</td>
                                        <td>تعداد همزمان :</td>
                                        <td><input type="number" name="filter['.$userGroup->id.'][simultaneous]" class="text-center" value="'.$userGroup->max_reserve_simultaneous.'" style="width: 50px"></td>
                                        <td>تعداد تخفیف :</td>
                                        <td><input type="number" name="filter['.$userGroup->id.'][max_discount]" class="text-center" value="'.$userGroup->max_discount.'" style="width: 50px"></td>
                                        <td>امکان رزرو :</td>
                                        <td><input type="checkbox" name="filter['.$userGroup->id.'][can_reserve]" class="text-center" '.$canReserveCheck.' style="width: 50px"></td>
                                   </tr>';
            }
            foreach ($dorms as $dorm) {
                $canReserveCheck = $dorm->can_reserve ? "checked":"";
                $dormsList .= '<tr>
                                    <td>'.$dorm->title.'</td>
                                    <td>امکان رزرو :</td>
                                    <td>
                                        <input type="checkbox" name="dorm['.$dorm->id.'][can_reserve]" class="text-center" '.$canReserveCheck.' style="width: 50px">
                                        <input type="hidden" name="dorm['.$dorm->id.'][help]" class="text-center">
                                    </td>
                               </tr>';
            }
            $userGroupList  .= '</tbody></table>';
            $ugSimultaneous .= '</tbody></table>';
            $dormsList .= '</tbody></table>';

            $eventList = '<table class="table table-bordered table-sm mt-3" style="width: auto">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-right">رویداد/مراسم</th>
                                    <th class="text-right">برگزارکننده</th>
                                    <th class="text-right">تعداد مهمان</th>
                                    <th class="text-right">تاریخ برگزاری</th>
                                </tr>
                                </thead>
                                <tbody>';
            foreach ($events as $event) {
                $createdAt = $event->created_at();
                $eventList .= '<tr>
                                    <td class="text-center"><input type="checkbox" value="'.$event->id.'"></td>
                                    <td>'.$event->name.'</td>
                                    <td class="text-right align-middle">'.$event->organization.'</td>
                                    <td class="text-right align-middle">'.$event->guest_count.'</td>
                                    <td class="text-right align-middle">'.$createdAt.'</td>
                               </tr>';
            }
            $eventList .= '</tbody></table>';

            $collList = "";
            $restList = "";
            if($firstCollection) {
                $collList .= '<div class="form-control" style="height: 120px; max-height: 120px; overflow: auto"><table class="table table-bordered table-sm">
                            <tbody>';
                $restList .= '<div class="form-control" style="height: 180px; max-height: 180px; overflow: auto" id="restlist-'.$firstCollection->id.'"><table class="table table-bordered table-sm">
                            <tbody>';

                $restsFirstColl = $firstCollection->rests;
                $collList .= '<tr>
                                  <td class="align-middle"><input type="checkbox" name="collection[]" class="collection-check align-middle" id="collection-'.$firstCollection->id.'" value="'.$firstCollection->id.'" checked> '.$firstCollection->name.'</td>
                              </tr>';
                foreach ($restsFirstColl as $value) {
                    $restList .= '<tr>
                                    <td class="align-middle"><input type="checkbox" name="rest['.$firstCollection->id.'][]" class="rest-check align-middle" id="rest-col-'.$value->id.'" value="'.$value->id.'" checked> '.$value->name.'</td>
                                    <td class="align-middle text-center"><button type="button" id="get-price-'.$firstCollection->id.'-'.$value->id.'" class="btn btn-light btn-sm get-prices" >قیمت ها</button></td>
                                    <td class="align-middle text-center"><button type="button" id="get-ddf-'.$firstCollection->id.'-'.$value->id.'" class="btn btn-secondary btn-sm get-ddf">برنامه</button></td>
                               </tr>';
                }
                $restList .= '</tbody></table></div>';

                foreach ($collections as $collection) {
                    if($collection->id == $firstCollection->id)
                        continue;
                    $rests = $collection->rests;
                    $collList .= '<tr>
                                  <td class="align-middle"><input type="checkbox" name="collection[]" class="collection-check align-middle" id="collection-'.$collection->id.'" value="'.$collection->id.'"> '.$collection->name.'</td>
                              </tr>';

                    $restList .= '<div class="form-control mt-1" style="height: 180px; max-height: 180px; display:none; overflow: auto" id="restlist-'.$collection->id.'"><table class="table table-bordered table-sm">
                            <tbody>';
                    foreach ($rests as $value) {
                        $restList .= '<tr>
                                    <td class="align-middle"><input type="checkbox" name="rest['.$collection->id.'][]" class="rest-check align-middle" id="rest-col-'.$value->id.'" value="'.$value->id.'"> '.$value->name.'</td>
                                    <td class="align-middle text-center"><button type="button" id="get-price-'.$collection->id.'-'.$value->id.'" class="btn btn-light btn-sm get-prices" >قیمت ها</button></td>
                                    <td class="align-middle text-center"><button type="button" id="get-ddf-'.$collection->id.'-'.$value->id.'" class="btn btn-secondary btn-sm get-ddf">برنامه</button></td>
                               </tr>';
                    }
                    $restList .= '</tbody></table></div>';
                }
                $collList .= '</tbody></table></div>';
            }

            $mealActivation = '<table class="table table-bordered table-sm mt-3" style="width: auto">
                                   <thead>
                                        <tr>
                                            <td>ردیف</td>
                                            <td>وعده</td>
                                            <td>فعال/غیرفعال</td>
                                        </tr>
                                    </thead>
                                    <tbody>';

            $mealTabs = "";
            $mealTabsContent = "";
            $counter = 0;
            $i = 1;
            $meals = config('app.meals');
            foreach ($meals as $prefix => $meal) {
                $active = '';
                $mealIsActive = '';
                $field = $prefix.'_meal_is_active';
                if ($setting->$field == true) {
                    if ($counter == 0)
                        $active = 'active';

                    $mealTabs .= '<li class="nav-item m-1">
                                      <a class="btn btn-light btn-sm '.$active.'" href="#'.$prefix.'" role="tab" data-toggle="tab">'.$meal.'</a>
                                  </li>';
                    $mealTabsContent .= '<div role="tabpanel" class="tab-pane '.$active.'" id="'.$prefix.'">
                                            <div class="row">
                                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                    <h6>از لیست غذاهای زیر غذای مورد نظر را جهت تخصیص انتخاب کنید:</h6>
                                                    <div class="foods-box"></div>
                                                    <hr>
                                                    <h6>لیست غذاهای انتخاب شده برای منو:</h6>
                                                    <div class="accordion" id="'.$prefix.'-chosen-foods"></div>
                                                    <hr>
                                                </div>
                                            </div>
                                        </div>';

                    $mealIsActive = 'checked';
                    $counter++;
                }
                $mealActivation .= '<tr>
                                    <td>'.$i.'</td>
                                    <td>'.$meal.'</td>
                                    <td>
                                        <input type="checkbox" name="meals['.$prefix.']" class="text-center" '.$mealIsActive.' style="width: 50px">
                                    </td>
                               </tr>';
                $i++;
            }
            $mealActivation .= '</tbody></table>';

            return view('cms.ddf.add',[
                'week'        => $res,
                'active_temp' => 'stu',
                'foods'       => $foodList,
                'desserts'    => $dessertsList,
                'userGroups'  => $userGroupList,
                'events'      => $eventList,
                'collects'    => $collList,
                'rests'       => $restList,
                'setting'     => $setting,
                'ugSimultaneous'  => $ugSimultaneous,
                'dorms'           => $dormsList,
                'mealTabs'        => $mealTabs,
                'mealTabsContent' => $mealTabsContent,
                'mealActivation' => $mealActivation,
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function get_prices(Request $request)
    {
        if(Rbac::check_access('define-day-food','get_prices')) {
            $mealStr = '';
            foreach (config('app.meals') as $prefix => $meal) {
                $mealStr .= $meal.",";
            }
            $v = Validator::make($request->json()->all(),[
                'meal'      => 'required|in:'.$mealStr,
                'food_id'   => 'required|array',
                'food_id.*' => 'required|numeric|exists:food,id',
                'r_id'      => 'required|numeric|exists:t_rest,id',
            ]);
            if($v->fails())
                return response()->json(['status' => 101, 'res' => 'مشخصات نامعتبر است']);

            $data = new \stdClass();
            $data->meal     = $request->json()->get('meal');
            $data->food_ids = $request->json()->get('food_id');
            $data->rest_id  = $request->json()->get('r_id');

            $ddf = new \App\Library\Ddf($data);
            $res = $ddf->get_prices();

            return response()->json(['status' => 200, 'res' => $res]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function get_menus(Request $request)
    {
        if(Rbac::check_access('define-day-food','get_prices')) {

            $v = Validator::make($request->json()->all(),[
                'type' => 'required|in:on-show-modal,on-rest-btn-click',
                'date' => 'required',
            ]);
            if($v->fails())
                return response()->json(['status' => 101, 'res' => 'مشخصات نامعتبر است. کد 01']);

            $type = $request->json()->get('type');
            $date = $request->json()->get('date');

            switch ($type) {
                case 'on-show-modal':
                    $firstCollection = Collection::first();
                    if(!$firstCollection)
                        return response()->json(['status' => 101, 'res' => 'حداقل یک مجموعه در سیستم باید تعریف شده باشد']);
                    $rest = $firstCollection->rests()->first();
                    if(!$rest)
                        return response()->json(['status' => 101, 'res' => 'حداقل یک رستوران یا سلف سرویس در سیستم باید تعریف شده باشد']);

                    $data = new \stdClass();
                    $data->date    = $date;
                    $data->coll_id = $firstCollection->id;
                    $data->rest_id = $rest->id;

                    $ddf = new \App\Library\Ddf($data);
                    $res = $ddf->get_menus();

                    return response()->json(['status' => 200, 'res' => $res]);
                case 'on-rest-btn-click':
                    $v = Validator::make($request->json()->all(),[
                        'r_id' => 'required|numeric|exists:t_rest,id',
                    ]);
                    if($v->fails())
                        return response()->json(['status' => 101, 'res' => 'مشخصات نامعتبر است. کد 02']);

                    $data = new \stdClass();
                    $data->date    = $date;
                    $data->rest_id = $request->json()->get('r_id');

                    $ddf = new \App\Library\Ddf($data);
                    $res = $ddf->get_menus();

                    return response()->json(['status' => 200, 'res' => $res]);
                default:
                    return response()->json(['status' => 102, 'res' => 'مشخصات نامعتبر است. کد 03']);
            }
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('define-day-food','store')) {
            $collectionRests = $request->rest;
            $ddfs            = $request->ddf;

            $faMeal = config('app.meals');
            $jdf = new jdf();
            $menuId = [];

            foreach ($collectionRests as $collection => $rests) {
                $v = Validator::make(['collection' => $collection],[
                    'collection' => 'required|numeric|exists:t_collection,id',
                ]);
                if($v->fails())
                    return response()->json(['status' => 101,'res' => 'مشخصات نامعتبر است کد 00']);

                foreach ($rests as $rest) {
                    $v = Validator::make(['rest' => $rest],[
                        'rest' => 'required|numeric|exists:t_rest,id',
                    ]);
                    if($v->fails())
                        return response()->json(['status' => 101,'res' => 'مشخصات نامعتبر است کد 01']);

                    foreach ($ddfs as $date => $someData1) {
                        $day = $jdf->jalali_day($date,'l');

                        foreach ($someData1 as $enMeal => $someData2) {
                            foreach ($someData2 as $foodId => $someData3) {
                                $food = Food::find($foodId);
                                if(!$foodId)
                                    return response()->json(['status' => 101,'res' => 'مشخصات نامعتبر است. کد 02']);

                                if(!isset($someData3['dessert_select']))
                                    return response()->json(['status' => 101,'res' => 'پر کردن فیلدهای ستاره دار الزامی است']);

                                $forCommonMenu     = isset($someData3['common']) ? 1 : 0;
                                $dessertSelectType = $someData3['dessert_select']; // 0 = free & 1 = non-free
                                $active       = isset($someData3['active']) ? 1 : 0;
                                $closeAt      = $someData3['close_at'];
                                $maxRes       = $someData3['max_res'];
                                $maxResTotal  = $someData3['max_res_total'];
                                $halfRes      = isset($someData3['half_res']) ? 1 : 0;
                                $userGroups   = isset($someData3['user_group']) ? $someData3['user_group']: [];

                                $desserts     = isset($someData3['dessert'])    ? $someData3['dessert']   : [];
                                $events       = isset($someData3['event'])      ? $someData3['event']     : [];
                                $hasGarnish   = isset($someData3['has_garnish'])? $someData3['has_garnish']:[];
                                $meal         = $faMeal[$enMeal];

                                // بعضی از گروه ها که تیکشون برداشته میشه اینجا فیلتر میشن
                                $userGroupsTmp = [];
                                foreach ($userGroups as $userGroup => $prop) {
                                    if(isset($prop['is']))
                                        $userGroupsTmp[$userGroup] = $prop;
                                        //unset($userGroups[$userGroup]);
                                }
                                $userGroups = $userGroupsTmp;

                                $menuType = 1; // یعنی برا رویداده
                                if($forCommonMenu == 1) {
                                    if(!empty($events))
                                        $menuType = 2; // یعنی برا هردووه
                                    else
                                        $menuType = 0;// یعنی برا منو عادیه
                                }

                                // برای ذخیره خود غذا
                                $foodType = 0;

                                $menuExists  = Menu::where('date',$date)
                                    ->where('collection_id',$collection)
                                    ->where('rest_id',$rest)
                                    ->where('food_id',$foodId)
                                    ->where('meal',$meal)
                                    ->where('food_type',$foodType)
                                    ->first();
                                if(isset($menuExists->id)) {
                                    $menuId[] = [
                                        'food_id' => $foodId,
                                        'menu_id' => $menuExists->id
                                    ];

                                    if (!empty($desserts)) {
                                        if ($dessertSelectType == 1) { // برای دسر های وابسته این منو
                                            foreach ($desserts as $dessert) {
                                                $mdCheck = MenuDessert::where('menu_id', $menuExists->id)->where('dessert_id', $dessert)->first();
                                                if(!$mdCheck) {
                                                    $menuDessert = new MenuDessert();
                                                    $menuDessert->menu_id    = $menuExists->id;
                                                    $menuDessert->dessert_id = $dessert;
                                                    $menuDessert->save();
                                                }

                                                // تغییر نوع دسر از آزاد به وابسته باعث حذف دسر های آزاد میشود
                                                $extraMenus = Menu::where('date', $date)
                                                    ->where('collection_id', $collection)
                                                    ->where('rest_id', $rest)
                                                    ->where('food_id', $dessert)
                                                    ->where('meal', $meal)
                                                    ->where('food_type', 1)
                                                    ->get();
                                                foreach ($extraMenus as $extraMenu) {
                                                    $data = new \stdClass();
                                                    $data->menu      = $extraMenu;
                                                    $data->el_db     = 'db';
                                                    $data->pay_back  = true;
                                                    $data->menu_type = 'dessert';

                                                    $ddf = new Ddf($data);
                                                    $ddf->cancel_menu();
                                                }
                                            }


                                            $extraMDs = DB::table('menu_dessert')->where('menu_id',$menuExists->id)->whereNotIn('dessert_id',$desserts)->get();
                                            // *** check for other menuDessert that not in $desserts -
                                            foreach ($extraMDs as $extraMD)
                                                 MenuDessert::destroy($extraMD->id);

                                        }
                                        else { // برای دسرهای آزاد
                                            $foodType = 1;
                                            foreach ($desserts as $dessert) {
                                                $dessertExists = Menu::where('date', $date)
                                                    ->where('collection_id', $collection)
                                                    ->where('rest_id', $rest)
                                                    ->where('food_id', $dessert)
                                                    ->where('meal', $meal)
                                                    ->where('food_type', $foodType)
                                                    ->where('menu_type',$menuType)
                                                    ->first();

                                                $dInFood = Food::find($dessert);
                                                if($dInFood) {
                                                    if (!$dessertExists) {
                                                        $menu = new Menu();
                                                        $menu->day = $day;
                                                        $menu->date = $date;
                                                        $menu->collection_id = $collection;
                                                        $menu->rest_id = $rest;
                                                        $menu->food_id = $dessert;
                                                        $menu->meal = $meal;
                                                        $menu->menu_type = $menuType;
                                                        $menu->food_type = $foodType;
                                                        $menu->dessert_type = $dessertSelectType;
                                                        $menu->food_title = $dInFood->title;
                                                        $menu->max_reserve_user = 0; // دسر آزاد بدون محدودیت رزرو
                                                        $menu->max_reserve_total = 0;
                                                        $menu->half_reserve = 0;
                                                        $menu->active = $active;
                                                        $menu->close_at = $closeAt;
                                                        $menu->has_garnish = 4;
                                                        $menu->save();
                                                    } else {
                                                        $dessertExists->menu_type = $menuType;
                                                        $dessertExists->food_type = $foodType;
                                                        $dessertExists->dessert_type = $dessertSelectType;
                                                        $dessertExists->food_title = $dInFood->title;
                                                        $dessertExists->max_reserve_user = 0; // دسر آزاد بدون محدودیت رزرو
                                                        $dessertExists->max_reserve_total = 0;
                                                        $dessertExists->active = $active;
                                                        $dessertExists->close_at = $closeAt;
                                                        $dessertExists->update();
                                                    }
                                                }

                                                // تغییر نوع دسر از وابسته به آزاد باعث حذف دسر های وابسته میشود
                                                MenuDessert::where('menu_id',$menuExists->id)
                                                    ->where('dessert_id',$dessert)
                                                    ->delete();
                                            }

                                            $extraMenus = DB::table('menu')
                                                ->where('date', $date)
                                                ->where('collection_id', $collection)
                                                ->where('rest_id', $rest)
                                                ->where('meal', $meal)
                                                ->where('food_type', $foodType)
                                                ->whereNotIn('food_id',$desserts)
                                                ->get();
                                            // *** check for other menuDessert that not in $desserts -
                                            // if exists before delete we must check if somebody reserve it pay back price
                                            foreach ($extraMenus as $extraMenu) {
                                                $data = new \stdClass();
                                                $data->menu      = $extraMenu;
                                                $data->el_db     = 'db';
                                                $data->pay_back  = true;
                                                $data->menu_type = 'dessert';

                                                $ddf = new Ddf($data);
                                                $ddf->cancel_menu();
                                            }
                                        }
                                    }
                                    else {
                                        // اینجا چون آرایه دسرها خالی اومده معنیش اینه که هیچ دسر آزاد یا وابسته ای رو نمیخوایم
                                        $extraMDs = DB::table('menu_dessert')->where('menu_id',$menuExists->id)->get();
                                        // *** check for other menuDessert that not in $desserts -
                                        // if exists before delete we must check if somebody reserve it pay back price
                                        foreach ($extraMDs as $extraMD)
                                            MenuDessert::destroy($extraMD->id);


                                        $extraMenus = Menu::where('date', $date)
                                            ->where('collection_id', $collection)
                                            ->where('rest_id', $rest)
                                            ->where('meal', $meal)
                                            ->where('food_type', 1)
                                            ->where('dessert_type',0)
                                            ->get();
                                        // *** check for other menuDessert that not in $desserts -
                                        // if exists before delete we must check if somebody reserve it pay back price
                                        foreach ($extraMenus as $extraMenu) {
                                            $data = new \stdClass();
                                            $data->menu      = $extraMenu;
                                            $data->el_db     = 'db';
                                            $data->pay_back  = true;
                                            $data->menu_type = 'dessert';

                                            $ddf = new Ddf($data);
                                            $ddf->cancel_menu();
                                        }
                                    }

                                    if($menuType == 0) { // یعنی منو میخواد عادی باشه رویداد هارو حذف کن
                                        $mExistsEvents = MenuEvent::where('menu_id',$menuExists->id)->get();
                                        foreach ($mExistsEvents as $mExistsEvent)
                                            $mExistsEvent->delete();

                                        // *** if exists before delete we must check if somebody reserve it pay back price
                                    }
                                    elseif($menuType == 1 || $menuType == 2) { // اینجا باید چک کنیم رویداد هارو کمو زیاد کنیم
                                        if (!empty($events)) {
                                            foreach ($events as $event) {
                                                $meCheck = MenuEvent::where('menu_id',$menuExists->id)->where('event_id',$event)->first();
                                                if(!$meCheck) {
                                                    $menuEvent = new MenuEvent();
                                                    $menuEvent->menu_id  = $menuExists->id;
                                                    $menuEvent->event_id = $event;
                                                    $menuEvent->save();
                                                }
                                            }
                                            $extraMEs = DB::table('menu_event')->where('menu_id',$menuExists->id)->whereNotIn('event_id',$events)->get();
                                            // *** check for other menuEvents that not in $events -
                                            // if exists before delete we must check if somebody reserve it pay back price
                                            foreach ($extraMEs as $extraME)
                                                MenuEvent::destroy($extraME->id);
                                        }
                                        else {
                                            $extraMEs = DB::table('menu_event')->where('menu_id', $menuExists->id)->get();
                                            // *** check for other menuEvents that not in $events -
                                            // if exists before delete we must check if somebody reserve it pay back price
                                            foreach ($extraMEs as $extraME)
                                                MenuEvent::destroy($extraME->id);
                                        }
                                    }

                                    if (!empty($userGroups)) {
                                        foreach ($userGroups as $userGroup => $someData4) {
                                            $mugCheck = MenuUserGroup::where('menu_id', $menuExists->id)->where('user_group_id', $userGroup)->first();
                                            if (!$mugCheck) {
                                                $menuUGroup = new MenuUserGroup();
                                                $menuUGroup->menu_id = $menuExists->id;
                                                $menuUGroup->user_group_id = $userGroup;
                                                $menuUGroup->max_res = $someData4['count'];
                                                $menuUGroup->save();
                                            } else {
                                                $mugCheck->max_res = $someData4['count'];
                                                $mugCheck->update();
                                            }
                                        }

                                        // حذف اونایی که قبلا ثبت شدن ولی الان نمیخوایمشون
                                        $extraMUGs = DB::table('menu_user_group')
                                            ->where('menu_id', $menuExists->id)
                                            ->whereNotIn('user_group_id', array_keys($userGroups))->get();
                                        // *** check for other menuEvents that not in $events -
                                        foreach ($extraMUGs as $extraMUG)
                                            MenuUserGroup::destroy($extraMUG->id);
                                    }

                                    $menuExists->dessert_type = $dessertSelectType;
                                    $menuExists->menu_type    = $menuType;
                                    $menuExists->active       = $active;
                                    $menuExists->half_reserve = $halfRes;
                                    $menuExists->max_reserve_user  = $maxRes;
                                    $menuExists->max_reserve_total = $maxResTotal;
                                    $menuExists->has_garnish       = $hasGarnish;
                                    $menuExists->close_at          = $closeAt;
                                    $menuExists->update();
                                }
                                else {
                                    $menu = new Menu();
                                    $menu->day               = $day;
                                    $menu->date              = $date;
                                    $menu->collection_id     = $collection;
                                    $menu->rest_id           = $rest;
                                    $menu->food_id           = $foodId;
                                    $menu->meal              = $meal;
                                    $menu->menu_type         = $menuType;
                                    $menu->food_type         = $foodType;
                                    $menu->dessert_type      = $dessertSelectType;
                                    $menu->food_title        = $food->title;
                                    $menu->max_reserve_user  = $maxRes;
                                    $menu->max_reserve_total = $maxResTotal;
                                    $menu->half_reserve      = $halfRes;
                                    $menu->active            = $active;
                                    $menu->close_at          = $closeAt;
                                    $menu->has_garnish       = $hasGarnish;
                                    $menu->save();

                                    $menuId[] = [
                                        'food_id' => $foodId,
                                        'menu_id' => $menu->id
                                    ];

                                    if (!empty($desserts)) {
                                        if ($dessertSelectType == 1) { // برای دسر های وابسته این منو
                                            foreach ($desserts as $dessert) {
                                                $menuDessert = new MenuDessert();
                                                $menuDessert->menu_id = $menu->id;
                                                $menuDessert->dessert_id = $dessert;
                                                $menuDessert->save();
                                            }
                                        } else { // برای دسرهای آزاد
                                            $foodType = 1;
                                            foreach ($desserts as $dessert) {
                                                $dInFood = Food::find($dessert);
                                                if($dInFood) {
                                                    // اینجا چک میکنیم که این دسر توسط منو های دیگه ثبت نشده باشه
                                                    $dessertExists  = Menu::where('date',$date)
                                                        ->where('collection_id',$collection)
                                                        ->where('rest_id',$rest)
                                                        ->where('food_id',$dessert)
                                                        ->where('meal',$meal)
                                                        ->where('menu_type',$menuType)
                                                        ->where('food_type',$foodType)
                                                        ->first();
                                                    if(!$dessertExists) {
                                                        $menu2 = new Menu();
                                                        $menu2->day               = $day;
                                                        $menu2->date              = $date;
                                                        $menu2->collection_id     = $collection;
                                                        $menu2->rest_id           = $rest;
                                                        $menu2->food_id           = $dessert;
                                                        $menu2->meal              = $meal;
                                                        $menu2->menu_type         = $menuType;
                                                        $menu2->food_type         = $foodType;
                                                        $menu2->dessert_type      = $dessertSelectType;
                                                        $menu2->food_title        = $dInFood->title;
                                                        $menu2->max_reserve_user  = 0; // مقدار صفر به این خاطر که رزرو دسر آزاد محدودیت ندارد
                                                        $menu2->max_reserve_total = 0;
                                                        $menu2->half_reserve      = 0;
                                                        $menu2->active            = $active;
                                                        $menu2->close_at          = $closeAt;
                                                        $menu2->has_garnish       = 4;
                                                        $menu2->save();
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // اعمال این فیلتر برای گروه های کاربری فقط روی غذا تاثیر دارد نه دسر ها
                                    if (!empty($userGroups)) {
                                        foreach ($userGroups as $userGroup => $someData4) {
                                            $menuUGroup = new MenuUserGroup();
                                            $menuUGroup->menu_id = $menu->id;
                                            $menuUGroup->user_group_id = $userGroup;
                                            $menuUGroup->max_res = $someData4['count'];
                                            $menuUGroup->save();
                                        }
                                    }

                                    if (!empty($events)) {
                                        foreach ($events as $event) {
                                            $menuEvent = new MenuEvent();
                                            $menuEvent->menu_id  = $menu->id;
                                            $menuEvent->event_id = $event;
                                            $menuEvent->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return response()->json(['status' => 200, 'res' => 'تغییرات منو انجام شد.','id' => $menuId]);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function common_setting(Request $request)
    {
        if(!Rbac::check_access('define-day-food','add'))
            return redirect('/')->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نیست');

        $v = Validator::make($request->all(),[
            'discount_type'     => 'required|in:percent,sub',
            'min_possible_cash' => 'required|numeric',
            'block_bf_no_card'  => 'nullable|in:on',
            'block_lu_no_card'  => 'nullable|in:on',
            'block_dn_no_card'  => 'nullable|in:on',
            'block_bf_non_dorm' => 'nullable|in:on',
            'block_lu_non_dorm' => 'nullable|in:on',
            'block_dn_non_dorm' => 'nullable|in:on',
        ]);
        if($v->fails())
            return redirect()->back()->withInput()->withErrors($v->errors());

        $filters = $request->filter;
        foreach ($filters as $ugId => $item) {
            $v = Validator::make([
                'id' => $ugId,
                'simultaneous' => $item['simultaneous'],
                'max_discount' => $item['max_discount'],
                'can_reserve'  => isset($item['can_reserve'])? $item['can_reserve']: null,
            ],[
                'id'    => 'required|numeric|exists:user_group',
                'simultaneous' => 'required|numeric',
                'max_discount' => 'required|numeric',
                'can_reserve'  => 'nullable|in:on',
            ]);
            if($v->fails())
                return redirect()->back()->withInput()->withErrors($v->errors());

            $ug = UserGroup::find($ugId);
            $ug->max_reserve_simultaneous = $item['simultaneous'];
            $ug->max_discount             = $item['max_discount'];
            $ug->can_reserve              = isset($item['can_reserve'])? 1: 0;
            $ug->update();
        }

        $dorms = $request->dorm;
        foreach ($dorms as $dormId => $item) {
            $v = Validator::make([
                'id' => $dormId,
                'can_reserve'  => isset($item['can_reserve'])? $item['can_reserve']: null,
            ],[
                'id' => 'required|numeric|exists:dorm',
                'can_reserve' => 'nullable|in:on',
            ]);
            if($v->fails())
                return redirect()->back()->withInput()->withErrors($v->errors());

            $dorm = Dorm::find($dormId);
//            $ug->max_discount = $item['max_discount'];
            $dorm->can_reserve  = isset($item['can_reserve'])? 1: 0;
            $dorm->update();
        }

        $setting = Setting::first();
        $setting->discount_type     = $request->discount_type;
        $setting->min_possible_cash = $request->min_possible_cash;
        $setting->block_bf_non_dorm = $request->has('block_bf_non_dorm') ? 1: 0;
        $setting->block_lu_non_dorm = $request->has('block_lu_non_dorm') ? 1: 0;
        $setting->block_dn_non_dorm = $request->has('block_dn_non_dorm') ? 1: 0;
        $setting->block_bf_no_card  = $request->has('block_bf_no_card') ? 1: 0;
        $setting->block_lu_no_card  = $request->has('block_lu_no_card') ? 1: 0;
        $setting->block_dn_no_card  = $request->has('block_dn_no_card') ? 1: 0;
        $setting->update();

        $meals = config('app.rMeals');
        $activeMeals = $request->has('meals') ? $request->meals : [];
        foreach ($activeMeals as $prefix => $value) {
            if(!in_array($prefix, $meals))
                if($v->fails())
                    return redirect()->back()->withInput()->withErrors($v->errors());
            $field = $prefix.'_meal_is_active';
            $setting->$field = 1;
        }
        $diff = array_diff(array_values($meals),array_keys($activeMeals));
        foreach ($diff as $item) {
            $field = $item.'_meal_is_active';
            $setting->$field = 0;
        }
        $setting->update();

        return redirect()->back()->with('successMsg', 'تنظیمات بروزرسانی شد');
    }

    public function next_week()
    {
        if(Rbac::check_access('define-day-food','next_week')) {
            if(session()->has('ddf_currWBeginTimestamp')) {
                $weekbeginTimestamp = session('ddf_currWBeginTimestamp') + (7 * 86400);
                session()->put('ddf_currWBeginTimestamp', $weekbeginTimestamp);
                $res = self::make_week($weekbeginTimestamp);
                return response()->json([
                    'status' => true,
                    'res'   => $res,
                ]);
            }
            else {
                response()->json(['status' => false, 'res' => 'عملیات ناموفق بود']);
            }
        }
        return response()->json(['status'=>false,'res'=>'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function curr_week()
    {
        if(Rbac::check_access('define-day-food','curr_week')) {
            $todayTimestamp = time();
            $jdf  = new jdf();
            $dayToweekbegin  = $jdf->jdate('w');
            $weekbeginTimestamp = $todayTimestamp - ($dayToweekbegin * 86400);
            $res = self::make_week($weekbeginTimestamp);
            session()->put('ddf_currWBeginTimestamp',$weekbeginTimestamp);
            return response()->json([
                'status' => true,
                'res'   => $res,
            ]);
        }
        return response()->json(['status'=>false,'res'=>'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function prev_week()
    {
        if(Rbac::check_access('define-day-food','prev_week')) {
            if(session()->has('ddf_currWBeginTimestamp')) {
                $weekbeginTimestamp = session('ddf_currWBeginTimestamp') - (7 * 86400);
                session()->put('ddf_currWBeginTimestamp', $weekbeginTimestamp);
                $res = self::make_week($weekbeginTimestamp);
                return response()->json([
                    'status' => true,
                    'res'    => $res,
                ]);
            }
            else {
                response()->json(['status'=>false, 'res'=>'عملیات ناموفق بود']);
            }
        }
        return response()->json(['status'=>false, 'res'=>'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function cancel_menu(Request $request)
    {
        if(Rbac::check_access('define-day-food','cancel_menu')) {
            $v = Validator::make($request->json()->all(), [
                'm_id'        => 'required|numeric|exists:menu,id',
                'del_dessert' => 'required|boolean',
                'pay_back'    => 'required|boolean',
            ]);
            if ($v->fails())
                return response()->json(['status' => 101, 'res' => 'مشخصات ارسال شده نامعتبر است']);

            $menuId     = $request->json()->get('m_id');
            $payBack    = $request->json()->get('pay_back');
            $delDessert = $request->json()->get('del_dessert');

            $delType = 'food';
            if($delDessert)
                $delType = 'both';

            $menu = Menu::find($menuId);

            $data = new \stdClass();
            $data->menu      = $menu;
            $data->el_db     = 'el';
            $data->pay_back  = $payBack;
            $data->menu_type = $delType;

            $ddf    = new Ddf($data);
            $result = $ddf->cancel_menu();
            return response()->json(['status' => $result['status'], 'res' => $result['message']]);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);
    }
}
