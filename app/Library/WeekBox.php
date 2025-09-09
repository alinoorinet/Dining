<?php


namespace App\Library;


use App\Card;
use App\Collection;
use App\Dorm;
use App\DormException;
use App\FoodPrice;
use App\Menu;
use App\Reservation;
use App\ReservationEvent;
use App\Rest;
use App\Setting;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WeekBox
{
    /*
     * 1. Html view + menu + reserves ...
     * 2. Doing reserve + validation ...
     * 3. Cancel reserve + validation ...
     * */

    /*
     * Shared variables
     * */
    public $err = [];
    public $wrn = [];
    public $request;
    public $requestType;
    public $jdf;
    public $isAdmin = false;
    public $isUser  = false;
    public $user;
    public $currentUserMode;
    public $userRoles;
    public $userRolesTitle;
    public $userGroups;
    public $userRests = [];
    public $userColls = [];
    public $selectedCollection;
    public $selectedRest;
    public $userHasNotDorm = false;
    public $userHasNotCard = false;
    public $tdWarnMsg;
    public $setting;
    public $meals;
    /*
     * Make menu variables
     * */

    public $modalHtml   = "";
    public $weekBoxHtml = "";
    public $day_date    = [];
    public $weekbeginTimestamp;
    public $reserves;
    public $menus;
    public $selectedDate;
    public $selectedMeal;
    public $menuType;

    /*
     * reserve
     * */
    public $maxDiscountedFood;
    public $walletAmount    = 0;
    public $minPossibleCash = 0;

    public function __construct($data = null)
    {
        $this->requestType = $data->req_type;

        if(isset($data->request))
            $this->request = $data->request;

        $authUser   = Auth::user();
        $this->user = session()->has('wb_user') ? session()->get('wb_user') : $authUser;
        $this->currentUserMode = $this->user->id != $authUser->id ? 'other' : 'himself';

        $this->userRoles      = $this->user->userRole()->where('active', 1)->get();
        $this->userRolesTitle = \App\Facades\Rbac::user_roles($this->user);
        $this->menuType       = session()->has('menu_type') ? session()->get('menu_type') : 0;

        if (in_array('super-admin', $this->userRolesTitle) || in_array('developer', $this->userRolesTitle))
            $this->isAdmin = true;
        else
            $this->isUser = true;

        $this->set_rest_collection();

        $setting = Setting::first();
        $this->setting = $setting;

        $this->jdf = new jdf();

        $this->tdWarnMsg = new \stdClass();
        $this->meals = config('app.meals');
        foreach ($this->meals as $prefix => $meal)
            $this->tdWarnMsg->$prefix = "";
    }

    protected function set_rest_collection()
    {
        if($this->isAdmin)
            $this->userRests = Rest::all();
        else
            $this->userRests = $this->user->rests;

        if(session()->has('selected_coll') || session()->has('selected_rest')) {
            $this->selectedCollection = session()->get('selected_coll');
            $this->selectedRest       = session()->get('selected_rest');
        }
        elseif(isset($this->userRests[0])) {
            $this->selectedCollection = $this->userRests[0]->collection;
            $this->selectedRest       = $this->userRests[0];
        }

        foreach ($this->userRests as $userRest) {
            $collection = $userRest->collection;
            if(!in_array($collection,$this->userColls))
                array_push($this->userColls,$collection);
        }
    }



    protected function validation()
    {
        switch ($this->requestType) {
            case 'md': // show modal
                $mealStr = '';
                foreach ($this->meals as $prefix => $meal)
                    $mealStr .= $prefix.",";

                $v = Validator::make($this->request,[
                    'date'   => 'required',
//                    'date'   => 'required|date_format:Y-m-d',
                    'data_c' => 'required|numeric|exists:t_collection,id',
                    'data_r' => 'required|numeric|exists:t_rest,id',
                    'data_m' => 'required|string|min:2|max:2|in:'.$mealStr,
                ],[
                    'date.required'    => 'مشخصات نامعتبر است',
                    'date.date_format' => 'مشخصات نامعتبر است',
                    'data_c.required'  => 'مشخصات نامعتبر است',
                    'data_c.numeric'   => 'مشخصات نامعتبر است',
                    'data_c.exists'    => 'مشخصات نامعتبر است',
                    'data_r.required'  => 'مشخصات نامعتبر است',
                    'data_r.numeric'   => 'مشخصات نامعتبر است',
                    'data_r.exists'    => 'مشخصات نامعتبر است',
                    'data_m.required'  => 'مشخصات نامعتبر است',
                    'data_m.string'    => 'مشخصات نامعتبر است',
                    'data_m.min'       => 'مشخصات نامعتبر است',
                    'data_m.max'       => 'مشخصات نامعتبر است',
                    'data_m.in'        => 'مشخصات نامعتبر است',
                ]);
                if($v->fails())
                    array_push($this->err,$v->errors()->first());

                $restId      = $this->request['data_r'];
                $checkExists = $this->userRests->find($restId);
                if(!$checkExists)
                    array_push($this->err,'مشخصات نامعتبر است');
                break;
            case 'set': // set reserve
                if(!isset($this->request->order['menu'])) {
                    array_push($this->err, 'سبد سفارشات جدید خالی است');
                    break;
                }

                $order = $this->request->order['menu'];
                foreach ($order as $someData) {
                    $v = Validator::make(['id' => $someData['id'],'count' => $someData['count'],], [
                        'id'    => 'required|numeric|exists:menu,id',
                        'count' => 'required|numeric',
                    ], [
                        'id.required' => 'لیست سفارشات جدید خالی است',
                        'id.numeric' => 'مشخصات نامعتبر است',
                        'id.exists' => 'مشخصات نامعتبر است',
                        'count.required' => 'تعداد سفارشات جدید خالی است',
                        'count.numeric' => 'تعداد سفارشات جدید نامعتبر است',
                    ]);
                    if ($v->fails()) {
                        array_push($this->err, $v->errors()->first());
                        break;
                    }
                }
                break;
            case 'unset': // remove reserve
                $v = Validator::make($this->request->json()->all(), [
                    'rsId' => 'required|numeric|exists:reservation,id',
                ], [
                    'rsId.required' => 'مشخصات سفارش نامعتبر است',
                    'rsId.numeric'  => 'مشخصات سفارش نامعتبر است',
                    'rsId.exists'   => 'مشخصات سفارش نامعتبر است',
                ]);
                if ($v->fails()) {
                    array_push($this->err, $v->errors()->first());
                    break;
                }
                $id = $this->request->json()->get('rsId');
                $reserve = Reservation::where('id',$id)->where('user_id',$this->user->id)->first();
                if(!isset($reserve->id))
                    array_push($this->err, 'مشخصات سفارش نامعتبر است');
                break;
        }
    }

    protected function possibility()
    {
        if($this->requestType == 'wb') {
            $res = $this->_dorm_filter();
            if($res == 'non-dorm') {
                if($this->setting->block_bf_non_dorm) {
                    $this->tdWarnMsg->bf = "کاربران غیرخوابگاهی مجاز به رزرو صبحانه نیستند";
                    $this->tdWarnMsg->sh = "کاربران غیرخوابگاهی مجاز به رزرو سحری نیستند";
                }

                if($this->setting->block_lu_non_dorm)
                    $this->tdWarnMsg->lu = "کاربران غیرخوابگاهی مجاز به رزرو نهار نیستند";

                if($this->setting->block_dn_non_dorm) {
                    $this->tdWarnMsg->dn = "کاربران غیرخوابگاهی مجاز به رزرو شام نیستند";
                    $this->tdWarnMsg->ft = "کاربران غیرخوابگاهی مجاز به رزرو افطار نیستند";
                }

                return;
            }

            $res = $this->_card_filter();
            if($res == 'has-no-card') {
                if($this->setting->block_bf_no_card) {
                    $this->tdWarnMsg->bf = "جهت تحویل صبحانه در سلف سرویس ها ابتدا کارت دانشجویی یا پرسنلی خود را هوشمند کنید";
                    $this->tdWarnMsg->sh = "جهت تحویل صبحانه در سلف سرویس ها ابتدا کارت دانشجویی یا پرسنلی خود را هوشمند کنید";
                }
                if($this->setting->block_lu_no_card)
                    $this->tdWarnMsg->lu = "جهت تحویل نهار در سلف سرویس ها ابتدا کارت دانشجویی یا پرسنلی خود را هوشمند کنید";

                if($this->setting->block_dn_no_card) {
                    $this->tdWarnMsg->dn = "جهت تحویل شام در سلف سرویس ها ابتدا کارت دانشجویی یا پرسنلی خود را هوشمند کنید";
                    $this->tdWarnMsg->ft = "جهت تحویل افطار در سلف سرویس ها ابتدا کارت دانشجویی یا پرسنلی خود را هوشمند کنید";
                }

                return;
            }
        }
        elseif($this->requestType == 'set') {
            $userGroup = $this->_user_group('all','primary');
            if (!$userGroup) {
                array_push($this->wrn,'خطا: شما به هیچ کدام از گروه های کاربری تعلق ندارید. راه حل: ثبت درخواست پشتیبانی آنلاین');
                return null;
            }

            $maxReserveSimultaneous = $userGroup->max_reserve_simultaneous;

            $wlObj = new \stdClass();
            $wlObj->type    = 'wallet_amount';
            $walletAmount   = $this->_wallet($wlObj);
            $totalPayAmount = 0;

            $order = $this->request->order['menu'];
            $totalOrderCountMenu = 0;
            foreach ($order as $item) {
                $menuId     = $item['id'];
                $orderCount = $item['count'];
                $menu       = Menu::find($menuId);

                //
                if(!$menu->active) {
                    array_push($this->wrn,'خطا: منو مورد نظر غیر فعال است.');
                    break;
                }

                // حداکثر رزرو هر کاربر و هر گروه کاربری
                $maxPossibleRes = $userGroup->max_reserve;
                if($maxPossibleRes > 0) {
                    if($menu->max_reserve_user > $maxPossibleRes)
                        $maxPossibleRes = $menu->max_reserve_user;
                }
                else
                    $maxPossibleRes = $menu->max_reserve_user;


                if($maxPossibleRes != 0) { // نامحدود
                    $checkOtherReserves = $this->user->reserves()
                        ->where('date',$menu->date)
                        ->where('meal',$menu->meal)
                        ->sum('count');
                    $totalOrderCountMenu += $checkOtherReserves + $orderCount;
                    if($totalOrderCountMenu > $maxPossibleRes) {
                        array_push($this->wrn, 'خطا: رزرو منو برای گروه کاربری ' . $userGroup->title . ' حداکثر به تعداد ' . $maxPossibleRes . ' پرس امکان پذیر است.');
                        //array_push($this->wrn, 'خطا: رزرو ' . $menu->food_title . ' برای گروه کاربری ' . $userGroup->title . ' حداکثر به تعداد ' . $maxPossibleRes . ' پرس امکان پذیر است.');
                        break;
                    }
                }


                // حد نصب تعداد رزرو این غذا
                $maxSumPossibleRes = $menu->max_reserve_total;
                if($maxSumPossibleRes != 0) {
                    $checkOtherReserves = $menu->reservation()
                        ->where('date', $menu->date)
                        ->where('food_type', 0)
                        ->sum('count');

                    $totalOrderCount = $checkOtherReserves + $orderCount;
                    if ($totalOrderCount > $maxSumPossibleRes) {
                        array_push($this->wrn, 'خطا: مجموع رزرو های صورت گرفته همه کاربران ' . $menu->food_title . ' حداکثر به تعداد ' . $maxSumPossibleRes . ' امکان پذیر است. و در حال حاضر به حد نصاب رسیده است.');
                        break;
                    }
                }

                // سقف مجموع رزرو های صورت گرفته هر گروه کاربری
                $menuUGs = $menu->user_groups()->where('user_group_id',$userGroup->id)->first();
                if(!$menuUGs) {
                    array_push($this->wrn,'خطا: رزرو '.$menu->food_title.' برای گروه کاربری '.$userGroup->title .' امکان پذیر نیست');
                    break;
                }
                else {
                    $maxSumPossibleRes  = $menuUGs->pivot->max_res;
                    if($maxSumPossibleRes != 0) {
                        $checkOtherReserves = $menu->reservation()
                            ->where('date', $menu->date)
                            ->where('user_group_id', $userGroup->id)
                            ->where('food_type', 0)
                            ->sum('count');

                        $totalOrderCount = $checkOtherReserves + $orderCount;
                        if ($totalOrderCount > $maxSumPossibleRes) {
                            array_push($this->wrn, 'خطا: مجموع رزرو های صورت گرفته ' . $menu->food_title . ' برای گروه کاربری ' . $userGroup->title . ' حداکثر به تعداد ' . $maxSumPossibleRes . ' امکان پذیر است.');
                            break;
                        }
                    }
                }

                $notPermittedUserGroupTitle = $this->_user_group_filter($menu);
                if($notPermittedUserGroupTitle) {
                    array_push($this->wrn, "گروه کاربری $notPermittedUserGroupTitle امکان رزرو این وعده را ندارد!!! ");
                    break;
                }


                // 1
                $checkOtherRestsReserves = $this->user->reserves()
                    ->where('date',$menu->date)
                    ->where('rest_id','!=',$menu->rest_id)
                    ->distinct('rest_id')
                    ->count();
                if($maxReserveSimultaneous <= $checkOtherRestsReserves) {
                    array_push($this->wrn,'خطا: شما حداکثر مجاز به رزرو در '.$maxReserveSimultaneous.' رستوران به صورت همزمان می باشید.');
                    break;
                }


                // 2
                $closeCheck = $this->_close_filter($menu);
                if($closeCheck == 'is-close') {
                    array_push($this->wrn,'خطا: بازه زمانی مجاز سفارش به پایان رسیده است');
                    break;
                }

                // 3
                $priceObj = new \stdClass();
                $priceObj->menu     = $menu;
                $priceObj->is_event = 0;
                $priceObj->event_id = null;
                $prices   = $this->_price($priceObj);
                $discount = $this->_discount('remain',$menu);
                $pd       = $this->_price_discount($prices,$discount,$orderCount);

                $totalPayAmount += $pd['pay_amount'];
                if($totalPayAmount > ($walletAmount + $this->setting->min_possible_cash)) {
                    array_push($this->wrn, 'خطا: موجودی شما برای ثبت سفارش کافی نیست.ابتدا موجودی کیف پول خود را افزایش دهید');
                    break;
                }
            }
        }
        elseif ($this->requestType == 'unset') {
            $resId   = $this->request->json()->get('rsId');
            $reserve = Reservation::find($resId);
            $menu    = $reserve->menu;

            // 1
            $closeCheck = $this->_close_filter($menu);
            if($closeCheck == 'is-close')
                array_push($this->wrn,'خطا: بازه زمانی مجاز لغو سفارش به پایان رسیده است');
        }
    }


    public function change_user($user)
    {
        session()->put('wb_user',$user);
        $this->user = $user;

        $wlObj = new \stdClass();
        $wlObj->type = 'wallet_amount';
        $amount = $this->_wallet($wlObj);

        return [
            'status' => 200,
            'res'    => 'تمام تغییرات برای کاربر '.$user->username.' اعمال می شود.',
            'wallet' => $amount
        ];
    }

    public function change_self()
    {
        $type = $this->request->json()->get('type');
        if($type == 'col') {
            $collId = $this->request->json()->get('colId');
            $collection = Collection::find($collId);
            if(!in_array($collection,$this->userColls))
                array_push($this->err,'خطا: مشخصات مجموعه نامعتبر است');
            else {
                $this->selectedCollection = $collection;
                session()->put('selected_coll',$this->selectedCollection);

                $collectionRests = $collection->rests;
                if(!isset($collectionRests[0]))
                    array_push($this->err,'خطا: برای مجموعه انتخاب شده سلف/رستوران تعریف نشده است');

                $userHasAccessToCollectionRest = false;
                foreach ($collectionRests as $collectionRest)
                    if($this->userRests->find($collectionRest)) {
                        $this->selectedRest = $collectionRest;
                        session()->put('selected_rest',$this->selectedRest);
                        $userHasAccessToCollectionRest = true;
                        break;
                    }
                if(!$userHasAccessToCollectionRest)
                    array_push($this->err,'خطای محدودیت دسترسی به سلف/رستوران!');
            }
        }
        else {
            $restId = $this->request->json()->get('restId');
            $rest   = Rest::find($restId);
            if(!$this->userRests->find($rest->id))
                array_push($this->err,'خطا: مشخصات سلف سرویس/رستوران نامعتبر است');
            else {
                $this->selectedRest = $rest;
                $collection = $rest->collection;
                $this->selectedCollection = $collection;
                session()->put('selected_rest',$this->selectedRest);
                session()->put('selected_coll',$this->selectedCollection);
            }
        }

        if(!empty($this->err))
            return [
                'status' => 101,
                'res' => $this->err
            ];

        return [
            'status' => 200,
        ];
    }


    public function make_menu($data = null)
    {
        $this->mm_data_provider($data);
        return $this->mm_kernel();
    }

    protected function mm_data_provider($data)
    {
        if (isset($data->weekbeginTimestamp)) {
            $wts = $data->weekbeginTimestamp;
            $this->weekbeginTimestamp = $wts;
            $this->jdf = new jdf();
            $this->day_date['شنبه'] = $this->jdf->jdate('Y-m-d', $wts);
            $this->day_date['یکشنبه'] = $this->jdf->jdate('Y-m-d', $wts + 86400);
            $this->day_date['دوشنبه'] = $this->jdf->jdate('Y-m-d', $wts + (2 * 86400));
            $this->day_date['سه شنبه'] = $this->jdf->jdate('Y-m-d', $wts + (3 * 86400));
            $this->day_date['چهارشنبه'] = $this->jdf->jdate('Y-m-d', $wts + (4 * 86400));
            $this->day_date['پنجشنبه'] = $this->jdf->jdate('Y-m-d', $wts + (5 * 86400));
            $this->day_date['جمعه'] = $this->jdf->jdate('Y-m-d', $wts + (6 * 86400));
        }

        if($this->requestType == 'md') {
            $this->selectedDate = $this->request['date'];
            $this->selectedRest = Rest::find($this->request['data_r']);
            $this->selectedCollection = $this->selectedRest->collection;

            $prefix = $this->request['data_m'];
            $this->selectedMeal = $this->meals[$prefix];
        }

        $userGroup = $this->_user_group('all','primary');
        $this->maxDiscountedFood = isset($userGroup->id) ? $userGroup->max_discount: 0;
    }

    protected function mm_kernel()
    {
        // part 1: modal
        // part 2: week box

        if($this->requestType == 'md') {
            $this->validation();
            if(!empty($this->err))
                return [
                    'status' => 101,
                    'res'   => $this->err,
                ];

            $this->mm_modal();
            return [
                'status' => 200,
                'res'   => $this->modalHtml,
            ];
        }
        else if($this->requestType == 'wb') {
            $this->possibility();

            $this->mm_week_box();
            return $this->weekBoxHtml;
        }
        return "Unknown request type.";
    }

    protected function mm_week_box()
    {
        $bfWeek   = $this->mm_week_box_back_forward_week();
        $rList    = $this->mm_week_box_rest_list();
        $cList    = $this->mm_week_box_collection_list();

        $rows = $this->mm_week_box_rows();

        $setting = $this->setting;
        $mealsTh = "";
        $meals = config('app.meals');
        $icons = config('app.mealsIcon');
        foreach ($meals as $prefix => $meal) {
            $field = $prefix.'_meal_is_active';
            if ($setting->$field != true)
                continue;
            $mealsTh .= "<th colspan='2' class='font13'><i class='fa fa-$icons[$prefix]'></i> $meal </th>";
        }

        $view = "<div class='row myBg'>
                    <div class='ui dimmer' id='week-box-dimmer'>
                        <div class='ui large text loader'>چند لحظه صبر کنید...</div>
                    </div>
                <div class='col-12 mb-lg '>
                <div class='row pb-1'>
                    <div class='col-lg-4 col-sm-4 col-md-4  mt-2 pt-3 pb-3'>
                        $cList
                    </div>
                    <div class='col-lg-4 col-sm-4 col-md-4  mt-2 pt-3 pb-3'>
                        $rList
                    </div>
                    $bfWeek
                </div>
            </div>
            <div class='col-12 mb-lg'>
                <div class='widget-body p-0 support table-wrapper table-responsive'>
                    <table class='table table-bordered mb-0 tblBg rad8x my-border'>
                        <thead>
                        <tr class='text-muted text-center'>
                            <th class='font13'><i class='fa fa-clock'></i> تاریخ </th>
                            <th class='font13'><i class='fa fa-cogs'></i> تنظیمات </th>
                            $mealsTh
                        </tr>
                        </thead>
                        <tbody class='text-dark text-center mailTbl'>
                        $rows
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";
        $this->weekBoxHtml .= $view;
    }

    protected function mm_week_box_rows()
    {
        # section1: view an delete all btn
        # section2: bf meal count
        #           bf meal edit|order|undefined
        # section3: lu meal count
        #           lu meal edit|order|undefined
        # section4: dn meal count
        #           dn meal edit|order|undefined
        # section5: undefined + undefined + undefined = tatil/undefinded

        $activeMealsCount = 0;
        $setting = $this->setting;
        $meals = config('app.meals');
        foreach ($meals as $prefix => $meal) {
            $field = $prefix . '_meal_is_active';
            if ($setting->$field == true)
                $activeMealsCount++;
        }

        $colspan1 = $activeMealsCount * 2 + 2;


        if(count($this->userRests) == 0) {
            $warningMsg = "<tr><td colspan='$colspan1'><div class='alert alert-warning font-lg'>دسترسی کاربر به هیچ کدام از رستوران ها/سلف سرویس ها تعیین نشده است.</div></td></tr>";
            return $warningMsg;
        }

        $rows = "";
        foreach ($this->day_date as $day => $date) {
            $viewDelAllBtn  = "";
            $threeMeal      = "";
            $menusUndefined = "";

            $menus = Menu::where('date', $date)
                ->where('collection_id', $this->selectedCollection->id)
                ->where('rest_id', $this->selectedRest->id)
                ->whereIn('menu_type', [$this->menuType,2])
                ->where('food_type', 0)
                ->get();

            $this->menus = collect($menus);
            $menusCount = $this->menus->count();
            if($menusCount > 0) {
                $reserves = $this->user->reserves()
                    ->where('date', $date)
                    ->where('collection_id', $this->selectedCollection->id)
                    ->where('rest_id', $this->selectedRest->id)
                    ->where('menu_type', $this->menuType)
                    ->get();
                $this->reserves = collect($reserves);

                $res           = $this->mm_week_box_rows_three_meal($date);
                $threeMeal     = $res['view'];
                $viewDelAllBtn = $this->mm_week_box_rows_view_del_all($date,$res['threeNoReserve']);
            }
            else
                $menusUndefined = "<td colspan='$colspan1' class=''>
                                   <span class='btn btn-order w-100'>تعطیل</span>
                               </td>";

            $rows .= "<tr>
                        <td class='align-middle'>
                            <span class='d-block'>$day</span>
                            <span class='d-block'>$date</span>
                        </td>

                        $viewDelAllBtn

                        $threeMeal

                        $menusUndefined
                    </tr>";
        }

        return $rows;
    }

    protected function mm_week_box_rows_view_del_all($date, $threeNoReserve)
    {
        $menuCount = $this->menus->count();
        if ($menuCount == 0)
            return "";


        if($threeNoReserve)
            return "<td>
                    <span class='d-block'><button type='button' data-date='$date' class='btn btn-light w-100 view-all-reserve' disabled>مشاهده تمام رزروهای امروز</button></span>
                    <span class='d-block'><button type='button' data-date='$date' class='btn btn-light w-100 view-all-delete' disabled>حذف تمام رزروهای امروز</button></span>
                </td>";

        $selectedColl = isset($this->selectedCollection->id) ? $this->selectedCollection->id : '';
        $selectedRest = isset($this->selectedRest->id)       ? $this->selectedRest->id       : '';
        return "<td>
                    <span class='d-block'><button type='button' data-date='$date' data-c='$selectedColl' data-r='$selectedRest' class='btn btn-light w-100 view-all-reserve'>مشاهده تمام رزروهای امروز</button></span>
                    <span class='d-block'><button type='button' data-date='$date' data-c='$selectedColl' data-r='$selectedRest' class='btn btn-light w-100 view-all-delete'>حذف تمام رزروهای امروز</button></span>
                </td>";
    }

    protected function mm_week_box_rows_three_meal($date)
    {
        $selectedColl = isset($this->selectedCollection->id) ? $this->selectedCollection->id : '';
        $selectedRest = isset($this->selectedRest->id)       ? $this->selectedRest->id       : '';

        $setting  = $this->setting;
        $meals    = config('app.meals');
        $mealsStr = new \stdClass();
        $threeNoReserve = true;

        foreach ($meals as $prefix => $meal) {
            $field = $prefix . '_meal_is_active';
            if ($setting->$field == true) {
                $mealsStr->$prefix = "<td colspan='2' class=''>
                                        <span class='btn btn-order w-100'>تعریف نشده</span>
                                    </td>";
                $menusCount = $prefix.'MenusCount';
                $mealsStr->$menusCount = $this->menus->where('meal',$meal)->count();


                if($this->tdWarnMsg->$prefix != "") {
                    $txt = $this->tdWarnMsg->$prefix;
                    $mealsStr->$prefix = "<td colspan='2' class=''>
                                <span class='btn btn-order w-100'>$txt</span>
                            </td>";
                }
                elseif($mealsStr->$menusCount > 0) {
                    $mealsStr->$prefix = "<td class=''>
                            <span class='countB'>تعداد</span>
                            <span class='d-block'><a class='btn btn-dribbble w-100'>0</a></span>
                        </td>
                        <td class=''>
                            <a href='#' class='btn btn-order w-100 edit-modal' data-c='$selectedColl' data-r='$selectedRest' data-date='$date' data-m='$prefix'><img src='/img/icons/reserve.png' class='reserveImg' width='30px'>انتخاب غذا</a>
                        </td>";

                    $reservesCount = $this->reserves->where('meal',$meal)->sum('count');
                    $sumPrice      = $this->reserves->where('meal',$meal)->sum('pay_amount');
                    if($reservesCount) {
                        $mealsStr->$prefix = "<td class=''>
                            <span class='countB'>تعداد</span>
                            <span class='d-block'><a class='btn btn-dribbble w-100'>$reservesCount</a></span>
                        </td>
                        <td class=''>
                            <span class='d-block'><a href='#' class='btn btn-check w-100 edit-modal' data-c='$selectedColl' data-r='$selectedRest' data-date='$date' data-m='$prefix'>مشاهده / ویرایش</a></span>
                            <span class='d-block'><a class='btn border-dribbble text-right w-100 text-muted'>مبلغ <span class='price'>$sumPrice</span></a></span>
                        </td>";
                        $threeNoReserve = false;
                    }
                }
            }
        }

        $view = "";
        foreach ($meals as $prefix => $meal) {
            if(isset($mealsStr->$prefix))
                $view .= $mealsStr->$prefix;
        }

        return [
            'threeNoReserve' => $threeNoReserve,
            'view' => $view,
        ];
    }

    protected function mm_week_box_back_forward_week()
    {
        $fromDate = $this->jdf->jdate('m/d', $this->weekbeginTimestamp);
        $toDate   = $this->jdf->jdate('m/d', $this->weekbeginTimestamp + (6 * 86400));

        $bfWeek   = "<div class='col-lg-4 col-sm-4 col-md-4 mt-2 pt-3 text-center'>
                        <div class='my-border rad8x p-2 bg-white'>
                            <div class='row'>
                                <div class='col-lg-3 col-3 pl-0 pr-0'>
                                    <button class='btn week-play-btn' href='javascript:void(0)' title='هفته بعد' id='nextWeek'>
                                        <i class='fa fa-4x fa-arrow-circle-right text-success'></i>
                                    </button>
                                </div>
                                <div class='col-lg-6 col-6 pr-0 pl-0'>
                                    <button class='btn btn-primary btn-sm mb-2  week-play-btn' href='javascript:void(0)' title='هفته جاری' id='currWeek'>هفته جاری</button>
                                    <div class='col-lg-12 pr-0 pl-0'>برنامه غذایی <span>$fromDate</span> تا <span> $toDate</span></div>
                                </div>
                                <div class='col-lg-3 col-3 pr-0 pl-0'>
                                    <button class='btn week-play-btn' href='javascript:void(0)' title='هفته قبل' id='prevWeek'>
                                        <i class='fa fa-4x fa-arrow-circle-left text-success'></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>";
        return $bfWeek;
    }

    /*
     * در این جا باید اولین عضو لیست مجموعه ها مشخص بشه و
     * لیست رستوران ها هم متعلق به کاربر و هم متعلق به این مجموعه باشد
     * */
    protected function mm_week_box_rest_list()
    {
        $view = "<label>رستوران/سلف سرویس :</label>".
                "        <select class='form-control' name='rests'>";

        foreach ($this->userRests as $userRest) {
            if($this->selectedCollection->id && ($this->selectedCollection->id == $userRest->collection_id)) {
                if ($this->selectedRest && ($userRest->id == $this->selectedRest->id))
                    $view .= "<option value='$userRest->id' selected>$userRest->name</option>";
                else
                    $view .= "<option value='$userRest->id'>$userRest->name</option>";
            }
        }

        $view .=  "</select>";
        return $view;
    }

    protected function mm_week_box_collection_list()
    {
        $view = "<label>مجموعه :</label>
                        <select class='form-control' name='collections'>";
        foreach ($this->userColls as $userColl) {
            if($this->selectedCollection && ($userColl->id == $this->selectedCollection->id))
                $view .= "<option value='$userColl->id' class='' selected>$userColl->name</option>";
            else
                $view .= "<option value='$userColl->id' class=''>$userColl->name</option>";
        }
        $view .=  "</select>";
        return $view;
    }



    protected function mm_modal()
    {
        $menus = Menu::where('date', $this->selectedDate)
            ->where('collection_id', $this->selectedCollection->id)
            ->where('rest_id', $this->selectedRest->id)
            ->whereIn('menu_type', [$this->menuType,2])
            ->where('meal', $this->selectedMeal)
            ->orderBy('food_type')
            ->get();
        $this->menus = collect($menus);

        $csrfField = csrf_field();

        $menuPart  = "";
        $orderPart = "";

        $resCount = 0;
        $billSum  = 0;
        $billDis  = 0;
        $billTot  = 0;

        foreach ($this->menus as $menu) {
            $foodTitle = $menu->food_title;
            if(isset($menu->desserts[0]->id)) {
                foreach ($menu->desserts as $dessert)
                    $foodTitle .= ' | '.$dessert->title;
            }

            $orderCount = 1;
            $priceObj = new \stdClass();
            $priceObj->menu     = $menu;
            $priceObj->is_event = 0;
            $priceObj->event_id = null;
            $prices   = $this->_price($priceObj);
            $discount = $this->_discount('remain',$menu);
            $pd       = $this->_price_discount($prices,$discount,$orderCount);

            // قیمت تعریف نشده
            if($pd['real_price'] == -1 || !$menu->active)
                continue;

            $btnClass1   = "add-to-order";
            $btnClass2   = "remove-order";
            $btnOpacity  = "1";
            $btnDToggle  = "";
            $btnTitle    = "";
            $closeCheck  = $this->_close_filter($menu);
            if($closeCheck == 'is-close') {
                $btnClass1   = "";
                $btnClass2   = "";
                $btnOpacity  = "0.2";
                $btnDToggle  = "data-toggle='tooltip'";
                $btnTitle    = "title='پایان مهلت زمانی'";
            }

            $nutValue   = "";
            $food       = $menu->food_menu;
            $foodStuffs = $food->stuffs;
            foreach ($foodStuffs as $foodStuff) {
                $nutValue .= "$foodStuff->stuff_name $foodStuff->amount $foodStuff->amount_unit = $foodStuff->nut_value"."<br>";
            }
            $nutValue = $nutValue != "" ? $nutValue : "تعریف نشده";

            $dataParent = "$menu->id";
            $menuPart .= "<tr class='menu-row'>
                            <td class='align-middle'>
                                $foodTitle
                            </td>
                            <td class='align-middle'>
                                <button class='btn' data-toggle='popover' data-content='$nutValue' data-html='true' title='ارزش غذایی'><i class='fa fa-question-circle text-info'></i></button>
                            </td>
                            <td class='align-middle'>
                                <a class='btn w-100' data-ps='$pd[real_price]|$pd[discount_amount]'>$pd[real_price]</a>
                                <span class='dis-remain d-none'>$pd[discount_count]</span>
                            </td>
                            <td class='align-middle'>
                                <button type='button' data-mu='$dataParent' class='btn $btnClass1' style='opacity: $btnOpacity' $btnDToggle $btnTitle>
                                    <i class='fa fa-2x fa-plus-square text-success'></i>
                                </button>
                            </td>
                        </tr>";

            /*$x = "<input type='hidden' name='order[menu][$menu->id][id]' value='$menu->id'>
                                        <input type='hidden' name='order[menu][$menu->id][count]' value='$userReserve->count'>";*/
            $userReserve = $this->user->reserves()->where('menu_id',$menu->id)->first();
            if($userReserve) {
                $orderPart .= "<tr class='order-row' id='order-row-$userReserve->menu_id'>
                                    <td class='align-middle'>
                                        $userReserve->food_title
                                    </td>
                                    <td class='align-middle'>
                                        <button type='button' class='btn $btnClass2' data-ps='$userReserve->real_price|$userReserve->discount_amount' data-rs='$userReserve->id' data-mu='$userReserve->menu_id' style='opacity: $btnOpacity' $btnDToggle $btnTitle>
                                            <i class='fa fa-2x text-danger fa-trash'></i>
                                        </button>
                                        <span class='dis-count d-none'>$userReserve->discount_count</span>
                                    </td>
                                    <td class='align-middle'>
                                        <a class='btn btn-dribbble'>$userReserve->count</a>
                                    </td>
                                    <td class='align-middle'>
                                        <a class='btn w-100'>$userReserve->real_price</a>
                                    </td>
                                </tr>";

                $resCount += $userReserve->count;
                $billSum  += $userReserve->real_price;
                $billDis  += $userReserve->discount_amount;
            }
        }

        if($orderPart == "")
            $orderPart = "<tr class='non-order-row'>
                              <td class='align-middle'>سفارشی ثبت نشده است.</td>
                          </tr>";

        $billPart  = "<tr class='border-top no-bot-padd' id='sum-row'>
                        <td colspan='2' class='align-middle text-left'>جمع</td>
                        <td class='align-middle'><button class='btn btn-dribbble' id='sum-count'>$resCount</button></td>
                        <td class='align-middle'><button class='btn w-100' id='sum-value'>$billSum</button></td>
                    </tr>
                    <tr class='no-padd' id='discount-row'>
                        <td colspan='2' class='align-middle text-left'>تخفیف</td>
                        <td class='align-middle'></td>
                        <td class='align-middle'><button class='btn w-100' id='discount-value'>$billDis</button></td>
                    </tr>
                    <tr class='no-padd' id='total-row'>
                        <td colspan='2' class='align-middle text-left'>قابل پرداخت</td>
                        <td class='align-middle'></td>
                        <td class='align-middle'><button class='btn w-100' id='total-value'>$billTot</button></td>
                    </tr>";


        $order  = "<div class='col-12 mb-lg p-0'>
                        <div class='widget-body p-0 support table-wrapper table-responsive'>
                            <form id='order-form'>
                                $csrfField
                                <table class='editTbl table table-borderless mb-0 rad8x' id='order-table'>
                                    <thead>
                                    <tr class='text-muted text-center border-bottom'>
                                        <th class='font13'> عنوان </th>
                                        <th class='font13'> حذف </th>
                                        <th class='font13'> تعداد </th>
                                        <th class='font13'> مبلغ به ریال </th>
                                    </tr>
                                    </thead>
                                    <tbody class='text-dark text-center'>
                                    $orderPart
                                    $billPart
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>";

        $menu = "<div class='col-12 mb-lg mt-3 p-0 '>
                        <div class='widget-body p-0 support table-wrapper table-responsive'>
                            <table class='table table-borderless mb-0 rad8x my-border' id='menu-table'>
                                <thead>
                                <tr class='text-muted text-center border-bottom'>
                                    <th class='font13'> عنوان </th>
                                    <th class='font13'> ارزش غذایی </th>
                                    <th class='font13'> مبلغ به ریال </th>
                                    <th class='font13 text-success'> سفارش </th>
                                </tr>
                                </thead>
                                <tbody class='text-dark text-center '>
                                $menuPart
                                </tbody>
                            </table>
                        </div>
                    </div>";
        $this->modalHtml .= $order.$menu;
    }

    /*
     * Set Reserve
     * */

    public function set_reserve()
    {
        return $this->set_kernel();
    }

    protected function set_kernel()
    {
        $this->validation();
        if(!empty($this->err))
            return [
                'status' => 101,
                'res'   => $this->err,
            ];

        $this->possibility();
        if(!empty($this->wrn))
            return [
                'status' => 101,
                'res'   => $this->wrn,
            ];

        $this->set_data_provider();

        $reserveIds = [];
        $creatorId = Auth::user()->id;
        $userId    = $this->user->id;
        $userName  = $this->user->name.' '.$this->user->family;
        $dormId    = $this->_dorm('id');
        $userGroup = $this->_user_group('all','primary');

        $ugId = null;
        $this->maxDiscountedFood = 0;
        if(isset($userGroup->id)) {
            $ugId = $userGroup->id;
            $this->maxDiscountedFood = $userGroup->max_discount;
        }

        $order = $this->request->order['menu'];
        foreach ($order as $item) {
            $menuId     = $item['id'];
            $orderCount = $item['count'];
            $menu       = Menu::find($menuId);
            $this->menuType = $menu->menu_type;

            $halfReserve = isset($item['half']) ? 1 : 0;//?

            $priceObj = new \stdClass();
            $priceObj->menu     = $menu;
            $priceObj->is_event = 0;
            $priceObj->event_id = null;
            $prices   = $this->_price($priceObj);
            $discount = $this->_discount('remain',$menu);
            $pd       = $this->_price_discount($prices,$discount,$orderCount);

            $foodTitle = $menu->food_title;
            if(isset($menu->desserts[0]->id)) {
                foreach ($menu->desserts as $dessert)
                    $foodTitle .= ' | '.$dessert->title;
            }

            $userReserve = $this->user->reserves()->where('menu_id',$menuId)->first();
            if(!$userReserve) {
                $rs = new Reservation();
                $rs->discount_count  = $pd['discount_count'];
                $rs->discount_amount = $pd['discount_amount'];
                $rs->real_price      = $pd['real_price'];
                $rs->pay_amount      = $pd['pay_amount'];
                $rs->foodprice_id    = $pd['price_id'];
                $rs->count           = $orderCount;
                /*
                // for this part we need the event id
                if ($menu->menu_type == 1 || $menu->menu_type == 2) {
                    $rsEvent = new ReservationEvent();
                }
                */
            }
            else {
                $rs = $userReserve;
                $rs->discount_count  += $pd['discount_count'];
                $rs->discount_amount += $pd['discount_amount'];
                $rs->real_price      += $pd['real_price'];
                $rs->pay_amount      += $pd['pay_amount'];
                $rs->foodprice_id    = $pd['price_id'];
                $rs->count           += $orderCount;
            }

            $wlObj = new \stdClass();
            $wlObj->amount   = $pd['pay_amount'];
            $wlObj->type     = 'compute';
            $wlObj->operation = 'sub';
            $wlObj->for      = " سفارش $orderCount عدد از $foodTitle";
            $wlRes = $this->_wallet($wlObj);
            if($wlRes === false) {
                array_push($this->err,'انجام فرآیند با خطا مواجه شد.کد wl-01 را به کارشناس سیستم اعلام کنید');
                return [
                    'status' => 101,
                    'res'   => $this->err,
                ];
            }

            $walletAfter = $wlRes;

            $rs->food_title      = $foodTitle;
            $rs->date            = $menu->date;
            $rs->day             = $menu->day;
            $rs->user_name       = $userName;
            $rs->meal            = $menu->meal;
            $rs->sex             = $this->user->sex;
            $rs->menu_type       = $menu->menu_type;
            $rs->food_type       = $menu->food_type;
            $rs->half_reserve    = $halfReserve;
            $rs->wallet_after    = $walletAfter;
            $rs->dorm_id         = $dormId;
            $rs->creator_id      = $creatorId;
            $rs->user_id         = $userId;
            $rs->user_group_id   = $ugId;
            $rs->menu_id         = $menu->id;
            $rs->rest_id         = $menu->rest_id;
            $rs->collection_id   = $menu->collection_id;
            $rs->save();

            $reserveIds[] = [
                'menu_id' => $menuId,
                'res_id' => $rs->id,
            ];
        }
        return [
            'status'      => 200,
            'res'         => 'ثبت و بروزرسانی سفارشات شما انجام شد.',
            'wallet'      => $this->walletAmount,
            'walletOwner' => $this->currentUserMode,
            'reserveIds'  => $reserveIds,
        ];
    }

    protected function set_data_provider()
    {
        //$this->user
        //$this->maxDiscountedFood = 2;
    }


    /*
     * Unset Reserve
     * */

    public function unset_reserve()
    {
        return $this->unset_kernel();
    }

    protected function unset_kernel()
    {
        $this->validation();
        if(!empty($this->err))
            return [
                'status' => 101,
                'res'   => $this->err,
            ];

        $this->possibility();
        if(!empty($this->wrn))
            return [
                'status' => 101,
                'res'   => $this->wrn,
            ];

        $resId   = $this->request->json()->get('rsId');
        $reserve = Reservation::find($resId);

        $wlObj = new \stdClass();
        $wlObj->amount   = $reserve->pay_amount;
        $wlObj->type     = 'compute';
        $wlObj->operation = 'add';
        $wlObj->for      = " حذف $reserve->count عدد از $reserve->food_title";
        $wlRes = $this->_wallet($wlObj);
        if($wlRes === false) {
            array_push($this->err,'انجام فرآیند با خطا مواجه شد.کد wl-01 را به کارشناس سیستم اعلام کنید');
            return [
                'status' => 101,
                'res'    => $this->err,
            ];
        }

        $reserve->delete();

        return [
            'status'      => 200,
            'res'         => 'حذف سفارش انجام و مبلغ به کیف پول شما برگشت داده شد.',
            'wallet'      => $this->walletAmount,
            'walletOwner' => $this->currentUserMode,
        ];
    }



    public function _dorm($need,$user = null)
    {
        if(!$user)
            $user = $this->user;
        $dorm = Dorm::where('uid_dormid',$user->dorm_id)->first();
        if(!$dorm)
            return null;

        if($need == 'id')
            return $dorm->id;
        elseif($need == 'all')
            return $dorm;

        return null;
    }

    public function _user_group($column,$filter)
    {
        $userGroups = $this->user->user_groups;
        $userGroups = collect($userGroups);

        if($filter == 'all') {
            return $userGroups->all();
        }
        elseif ($filter == 'primary') {
            if($column == 'id') {
                $ug = $userGroups->where('pivot.is_primary',1)->first();
                if($ug)
                    return $ug->id;
                else {
                    $ug = $userGroups->first();
                    if($ug)
                        return $ug->id;
                }
            }
            else
                return $userGroups->where('pivot.is_primary',1)->first();
        }

        return null;
    }

    /*
     *  $data = new \stdClass();
        $data->food_id  = 93;
        $data->coll_id  = 1;
        $data->rest_id  = 1;
        $data->is_event = 1;
        $data->event_id = 1;
        $data->meal     = 'صبحانه';
        $this->_price($data);
     * */
    public function _price($data)
    {
        if(isset($data->userGroups)) {
            $userGroups = $data->userGroups;
        }
        elseif(isset($data->user)) {
            $this->user = $data->user;
            $userGroups = $this->_user_group('all','all');
        }
        else
            $userGroups = $this->_user_group('all','all');

        $this->jdf = new jdf();
        $nowTime = $this->jdf->jdate('H:i:s');

        $setting = Setting::first();
        $discountType = $setting->discount_type;

        $dis_price = -1;
        $dis_count = 0;
        $min_price = -1;
        $price_id  = null;
        $group_id  = null;

        $hasTimePrice = false;

        foreach ($userGroups as $userGroup) {
            $prices = FoodPrice::where('foodmenu_id', $data->menu->food_id)
                ->where('usergroup_id', $userGroup->id)
                ->where('collection_id', $data->menu->collection_id)
                ->where('rest_id', $data->menu->rest_id)
                ->where('meal', $data->menu->meal)
                ->get();
            $prices = collect($prices);

            if($data->is_event) {
                $eventPrices = $prices->where('type', 2)->all();
                foreach ($eventPrices as $price) {
                    $event = $price->events()->where('event_id',$data->event_id)->first();
                    if($event)
                        if($price->price < $min_price || $min_price == -1) {
                            $min_price = $price->price;
                            $group_id  = $userGroup->id;
                            $price_id  = $price->id;
                        }
                }
            }
            else {
                $timePrices   = $prices->where('type',1)->all();
                foreach ($timePrices as $price) {
                    $times = $price->times;
                    foreach ($times as $time) {
                        if($time->time_from <= $nowTime && $nowTime <= $time->time_to)
                            if($price->price < $min_price || $min_price == -1) {

                                $generalPrice = $prices->where('type', 0)->first();
                                if($generalPrice) {
                                    $disPrice = $discountType == 'sub' ? $price->price - $generalPrice->discount : $price->price - (($generalPrice->discount * $price->price) / 100);
                                    if ($disPrice < $dis_price || $dis_price == -1) {
                                        $dis_price = $disPrice;
                                        $dis_count = $generalPrice->discount_count;
                                    }
                                }
                                $min_price = $price->price;
                                $group_id  = $userGroup->id;
                                $price_id  = $price->id;

                                $hasTimePrice = true;
                            }
                    }
                }

                if(!$hasTimePrice) {
                    $generalPrice = $prices->where('type', 0)->first();
                    if ($generalPrice) {
                        if ($generalPrice->price < $min_price || $min_price == -1)
                            $min_price = $generalPrice->price;

                        $disPrice = $discountType == 'sub' ? $generalPrice->price - $generalPrice->discount : $generalPrice->price - (($generalPrice->discount * $generalPrice->price) / 100);
                        if ($disPrice < $dis_price || $dis_price == -1) {
                            $dis_price = $disPrice;
                            $dis_count = $generalPrice->discount_count;
                        }

                        $group_id  = $userGroup->id;
                        $price_id  = $generalPrice->id;
                    }
                }
            }
        }

        return [
            'dis_price' => $dis_price,
            'dis_count' => $dis_count,
            'price'     => $min_price,
            'price_id'  => $price_id,
            'group_id'  => $group_id,
        ];
    }

    public function _discount($mode,$menu)
    {
        /*
         * R2 = تعداد تخفیف قابل اعمال از نظر قیمت
         * K  = مجموع تعداد تخفیف مصرف شده
         */
        if($mode == 'remain') {
            $alreadyDisCount = $this->user->reserves()
                ->where('date',$menu->date)
                ->where('meal',$menu->meal)
                ->sum('discount_count');

            return [
                'R2' => $this->maxDiscountedFood,
                'K'  => $alreadyDisCount,
            ];
        }
        return null;
    }

    public function _price_discount($prices,$discount,$orderCount)
    {
        /*
         * C  = باقیمانده کل تخفیف قابل اعمال
         * R1 = تعداد تخفیف قابل اعمال از نظر قیمت
         * R2 = تعداد تخفیف قابل اعمال از نظر رزرو
         * K  = مجموع تعداد تخفیف مصرف شده
         * C1 = تعداد قابل تخفیف از تعداد سفارش درخواستی
         * C2 = تعداد غیر قابل تخفیف از تعداد سفارش درخواستی
         * */

        $foodPriceId = $prices['price_id'];
        $realPrice   = $orderCount * $prices['price'];

        $R1 = $prices['dis_count'];
        $R2 = $discount['R2'];
        $minR = $R1 > $R2 ? $R2 : $R1;
        $K = $discount['K'];

        $C = $minR - $K;
        if($C > $orderCount) {
            $C1 = $orderCount;
            $C2 = 0;
        }
        elseif ($C > 0 && (0 == $C || $C <= $orderCount)) {
            $C1 = $C;
            $C2 = $orderCount - $C1;
        }
        else {
            $C1 = 0;
            $C2 = $orderCount;
        }

        $payAmount = ($C1 * $prices['dis_price'])
            + ($C2 * $prices['price']);
        $disAmount = $realPrice - $payAmount;


        return [
            'price_id'   => $foodPriceId,
            'real_price' => $realPrice,
            'pay_amount' => $payAmount,
            'discount_count'  => $C1,
            'discount_amount' => $disAmount,
        ];
    }

    public function _wallet($data)
    {
        $walletAmount = 0;
        $wallet = $this->user->wallet()->orderBy('id','desc')->first();
        if(isset($wallet->id))
            $walletAmount = $wallet->amount;

        if($data->type == 'compute') {
            $newWallet = new Wallet();
            $newWallet->user_id     = $this->user->id;
            $newWallet->amount      = $data->operation == 'add' ? $walletAmount + $data->amount : $walletAmount - $data->amount;
            $newWallet->value       = $data->amount;
            $newWallet->_for        = $data->for;
            $newWallet->operation   = $data->operation == 'add' ? 1 : 0;
            try {
                if (!$newWallet->save())
                    throw new \Exception;
                $this->walletAmount = $newWallet->amount;
                return $newWallet->amount;
            } catch (\Exception $exception) {
                return false;
            }
        }
        elseif ($data->type == 'wallet_amount')
            return $walletAmount;

        return false;
    }



    public function _close_filter($menu)
    {
        if($menu->close_at) {
            $currentDatetime = $this->jdf->jdate('Y-m-d H:i:s');
            if($menu->close_at < $currentDatetime)
                return 'is-close';
            return 'is-open';
        }
        elseif ($this->selectedRest->close_at) {
            $currentDate      = date('Y-m-d H:i:s');
            $menuDate         = $menu->date;
            $currentTimestamp = $this->jdf->date_to_timestamp($currentDate,'Y-m-d H:i:s');
            $general_close_at_sec = $this->selectedRest->close_at * 3600;

            $currentDatePlus = $this->jdf->jdate('Y-m-d',$currentTimestamp + $general_close_at_sec);
            if($menuDate <= $currentDatePlus)
                return 'is-close';
            return 'is-open';
        }
        return 'is-open';
    }

    public function _dorm_filter()
    {
        if(!is_numeric($this->user->std_no))
            return 'dorm';

        $dorm = $this->_dorm('all');
        if (!$dorm || $dorm->title == 'غیر خوابگاهی') {

            $de = DormException::where('user_id',$this->user->id)->first();
            if(isset($de->id))
                return 'dorm';

            $this->userHasNotDorm = true;
            return 'non-dorm';
        }
        return 'dorm';
    }

    public function _card_filter()
    {
        $card = Card::where('username',$this->user->std_no)
            ->orWhere('username',$this->user->username)
            ->first();
        if($card)
            return 'has-card';

        $userGroups = $this->_user_group('all','all');
        foreach ($userGroups as $userGroup) {
            if($userGroup->title == 'مهمان')
                return 'has-card';
        }

        $this->userHasNotCard = true;
        return 'has-no-card';
    }

    public function _user_group_filter($menu)
    {
        $userGroups = $this->_user_group('all','all');
        $permittedMenuUGs = $menu->user_groups;
        $permittedMenuUGsCollect = collect($permittedMenuUGs);
        foreach ($userGroups as $userGroup) {
            $checkIfNotPermitted = $permittedMenuUGsCollect->where('id',$userGroup->id)->first();
            if(!$checkIfNotPermitted)
                return $userGroup->title;
        }

        return null;
    }
}
