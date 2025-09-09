<?php


namespace App\Library;


use App\Event;
use App\Food;
use App\Menu;
use App\Reservation;
use App\Rest;
use App\Setting;
use App\UserGroup;
use App\Wallet;

class Ddf
{
    public $collection;
    public $rest;
    public $foodIds;
    public $meal;

    public $events;
    public $userGroups;
    public $enMeal;

    public $date;
    public $menus;
    public $menu;
    public $collapse = [];
    public $freeDesserts = [];
    public $desserts;

    public $payBack;
    public $menuType;
    public $elDb;
    public $setting;

    public function __construct($data)
    {
        $this->foodIds = isset($data->food_ids) ? $data->food_ids : null;
        $this->meal    = isset($data->meal)     ? $data->meal : null;
        $this->date    = isset($data->date)     ? $data->date : null;


        if(isset($data->rest_id)) {
            $this->rest = Rest::find($data->rest_id);
            $this->collection = $this->rest->collection;
        }

        //for canceling
        $this->menu     = isset($data->menu)       ? $data->menu : null;
        $this->elDb     = isset($data->el_db)      ? $data->el_db : null;
        $this->payBack  = isset($data->pay_back)   ? $data->pay_back : null;
        $this->menuType = isset($data->menu_type)  ? $data->menu_type : null;

        $this->setting = Setting::first();
    }

    public function get_prices()
    {
        $this->gp_data_provider();
        return $this->gp_basic_html();
    }

    public function gp_data_provider()
    {
        $this->userGroups = UserGroup::all();
        $this->events     = Event::where('active', 1)->where('confirmed', 1)->get();
        $this->enMeal = config('app.rMeals');
    }

    public function gp_basic_html()
    {
        $parts = $this->gp_dynamic_content();
        $cName = $this->collection->name;
        $rName = $this->rest->name;
        $view   = "<div class='row'>
                       <div class='col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                            <div class='card text-right'>
                                <div class='card-header'>
                                    <span class='bg-info'>مجموعه: $cName</span>
                                    <span class='bg-white'>رستوران/سلف سرویس: $rName</span>
                                    <span class='bg-info'>وعده: $this->meal</span>
                                    <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 close-btn float-left'><i class='fa fa-times'></i></button>
                                </div>
                                <div class='card-body'>
                                    $parts
                                </div>
                           </div>
                       </div>
                   </div>";
        return $view;
    }

    public function gp_dynamic_content()
    {
        $parts = "";
        $key   = $this->enMeal[$this->meal];

        foreach ($this->foodIds as $foodId) {
            $food  = Food::find($foodId);

            $parts .= "<div class='card border-0'>
                        <div class='card-body'>
                            <div class='card-title bg-light'>لیست قیمت های $food->title</div>";

            $ph1 = "<p class='text-danger'><i class='fa fa-filter'></i> گروه کاربری</p>
                                    <div class='table-responsive'>
                                        <table class='table table-sm' style='width: auto'>
                                            <thead>
                                            <tr>
                                                <th class='text-right'>گروه کاربری</th>
                                                <th class='text-center'>قیمت(ریال)</th>
                                                <th class='text-center'>درصد تخفیف %</th>
                                                <th class='text-center'>برای چه تعداد رزرو اعمال شود</th>
                                            </tr>
                                            </thead>
                                            <tbody>";

            $ph2 = "<p class='text-danger mt-5'><i class='fa fa-filter'></i> گروه کاربری + روز فروش</p>
                                    <div class='table-responsive'>
                                        <table class='table table-sm' style='width: auto'>
                                            <thead>
                                            <tr>
                                                <th class='text-right'>گروه کاربری</th>
                                                <th class='text-right'>از ساعت</th>
                                                <th class='text-right'>تا ساعت</th>
                                                <th class='text-center'>قیمت</th>
                                                <th class='text-center'></th>
                                            </tr>
                                            </thead>
                                            <tbody>";

            $ph3 = "<p class='text-danger mt-5'><i class='fa fa-filter'></i> رویدادها و مراسمات</p>
                                    <div class='table-responsive'>
                                        <table class='table table-striped table-bordered table-sm'>
                                            <thead>
                                            <tr>
                                                <th class='text-center'>#</th>
                                                <th class='text-right'>رویداد/مراسم</th>
                                                <th class='text-right'>برگزارکننده</th>
                                                <th class='text-right'>تعداد مهمان</th>
                                                <th class='text-right'>تاریخ برگزاری</th>
                                                <th class='text-center'></th>
                                            </tr>
                                            </thead>
                                            <tbody>";

            // Phase 1: general Price
            foreach ($this->userGroups as $userGroup) {
                $priceValue   = 0;
                $priceDAmount = 0;
                $priceDCount  = 0;

                $priceExists = $food->price()
                    ->where('usergroup_id', $userGroup->id)
                    ->where('meal', $this->meal)
                    ->where('rest_id', $this->rest->id)
                    ->where('collection_id', $this->collection->id)
                    ->where('type', 0)
                    ->first();

                if ($priceExists) {
                    $priceValue = $priceExists->price;
                    $priceDAmount = $priceExists->discount;
                    $priceDCount = $priceExists->discount_count;
                }

                $ph1 .= "<tr>
                                <td class='text-right align-middle'>$userGroup->title</td>
                                <td class='text-right align-middle'>
                                    <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][general][$userGroup->id][price]' value='$priceValue' disabled>
                                </td>
                                <td class='text-right align-middle'>
                                    <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][general][$userGroup->id][d_amount]' min='0' value='$priceDAmount' disabled>
                                </td>
                                <td class='text-right align-middle'>
                                    <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][general][$userGroup->id][d_count]' value='$priceDCount' disabled>
                                </td>
                            </tr>";

                // Phase 2: Time price
                $row = "";
                $priceExists = $food->price()
                    ->where('usergroup_id', $userGroup->id)
                    ->where('meal', $this->meal)
                    ->where('rest_id', $this->rest->id)
                    ->where('collection_id', $this->collection->id)
                    ->where('type', 1)
                    ->get();
                foreach ($priceExists as $priceExist) {
                    $priceTimes = $priceExist->times;
                    foreach ($priceTimes as $priceTime) {
                        $row .= "<tr>
                                        <td class='text-right align-middle'>$userGroup->title</td>
                                        <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][from_time][]' value='$priceTime->time_from' placeholder='00:00:00' disabled></td>
                                        <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][to_time][]'  value='$priceTime->time_to' placeholder='00:00:00' disabled></td>
                                        <td class='text-center align-middle'><input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][time][$userGroup->id][price][]' value='$priceExist->price' disabled></td>
                                        <td class='text-center align-middle add-btn-wrapper'><button type='button' class='btn btn-info btn-sm add-saleday-time'><i class='fa fa-plus'></i></button></td>
                                    </tr>";
                    }
                }

                if($row == '')
                    $row = "<tr>
                                <td class='text-right align-middle'>$userGroup->title</td>
                                <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][from_time][]' value='' placeholder='00:00:00' disabled></td>
                                <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][to_time][]' value='' placeholder='00:00:00' disabled></td>
                                <td class='text-center align-middle'><input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][time][$userGroup->id][price][]' value='' disabled></td>
                                <td class='text-center align-middle add-btn-wrapper'><button type='button' class='btn btn-info btn-sm add-saleday-time'><i class='fa fa-plus'></i></button></td>
                            </tr>";
                $ph2 .= $row;
            }

            $ph1 .= "</tbody>
                    </table>
                </div>";
            $ph2 .= "</tbody>
                    </table>
                </div>";

            // Phase 3: event price
            if(isset($this->events[0]->id))
                foreach($this->events as $i => $event) {
                    $i++;
                    $createdAt = $event->created_at();
                    $ph3 .= "<tr>
                        <td class='text-center align-middle'>$i</td>
                        <td class='text-right align-middle'>$event->name</td>
                        <td class='text-right align-middle'>$event->organization</td>
                        <td class='text-right align-middle'>$event->guest_count</td>
                        <td class='text-right align-middle'>$createdAt</td>
                        <td class='text-center align-middle'><button type='button' id='set-event-price-$event->id-$foodId' class='btn btn-info btn-sm set-event-price' >قیمت</button></td>
                    </tr>";

                    $eventGroups = $event->user_groups;

                    $ph3 .= "<tr id='tr-set-event-price-$event->id' class='d-none'>
                            <td colspan='6'>
                                <div class='table-responsive'>
                                    <table class='table table-sm' style='width: auto'>
                                        <thead>
                                        <tr>
                                            <th class='text-right'>گروه کاربری</th>
                                            <th class='text-right'>قیمت(ریال)</th>
                                        </tr>
                                        </thead>
                                        <tbody>";
                    if (isset($eventGroups[0]->id))
                        foreach ($eventGroups as $group) {
                            $priceValue = '';

                            $eventPrice = $event->prices()
                                ->where('foodmenu_id',$foodId)
                                ->where('usergroup_id',$group->id)
                                ->where('meal', $this->meal)
                                ->where('rest_id', $this->rest->id)
                                ->where('collection_id', $this->collection->id)
                                ->where('type', 2)
                                ->first();
                            if($eventPrice)
                                $priceValue = $eventPrice->price;
                                $ph3 .= "<tr>
                                              <td class='text-right align-middle'>$group->title</td>
                                              <td class='text-right align-middle'>
                                                  <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][event][$event->id][$group->id]' value='$priceValue' disabled>
                                              </td>
                                            </tr>";
                        }
                    else
                        $ph3 .= "<tr>
                                <td class='text-center align-middle' colspan='2'>گروه کاربری برای این رویداد تعیین نشده است</td>
                            </tr>";

                    $ph3 .= "</tbody>
                            </table>
                        </div>
                    </td>
                </tr>";
                }
            else
                $ph3 .= "<tr>
                      <td class='text-center align-middle' colspan='6'>رویداد فعال و تایید شده ای وجود ندارد</td>
                  </tr>";
            $ph3 .= "</tbody>
                    </table>
                </div>";

            $parts .= $ph1.$ph2.$ph3;
            $parts .= "</div>
                     </div>";
        }
        return $parts;
    }


    /*
     * Get menu that already created
     * ali nouri 1396/02/16
     * */

    public function get_menus()
    {
        $this->gm_data_provider();
        $result = $this->gm_kernel();
        return $result;
    }

    public function gm_data_provider()
    {
        $this->menus = Menu::where('date',$this->date)
            ->where('collection_id',$this->collection->id)
            ->where('rest_id',$this->rest->id)
            ->get();

        $this->events = Event::where('active', 1)
            ->where('confirmed', 1)
            ->get();

        $this->userGroups = UserGroup::all();
        $this->desserts   = Food::where('type',1)->orderBy('title')->get();
    }

    public function gm_kernel()
    {
        $enMeal = config('app.rMeals');

        // here we separate free dessert to another array
        $collect = collect($this->menus);
        $this->freeDesserts = $collect->where('food_type',1)->all();

        foreach ($this->menus as $menu) {
            $eMeal      = $enMeal[$menu->meal];
            $this->menu = $menu;
            $this->gm_collapse($eMeal);
        }

        return $this->gm_join_parts();
    }

    public function gm_collapse($enMeal)
    {
        if($this->menu->food_type == 0)
            $this->gm_head($enMeal);

        $this->gm_body($enMeal);
    }

    public function gm_head($enMeal)
    {
        $panelId   = $enMeal;
        $menuId    = $this->menu->id;
        $foodId    = $this->menu->food_id;
        $foodTitle = $this->menu->food_title;

        $head = "<div class='card-header food-title pt-1 pb-1' id='chosen-accord-$foodId' style='background-color: #2dde98'>\n" .
            "       <button class='btn btn-link btn-sm text-white' type='button' data-toggle='collapse' data-target='#collapse$foodId' aria-expanded='false' aria-controls='collapse$foodId'>\n" .
            "           $foodTitle \n" .
            "       </button>\n" .
            "       <button type='button' class='btn btn-light btn-sm remove-chosen-food float-left' data-id='$foodId' data-parent='$panelId' data-menu='$menuId'><i class='fa fa-times'></i></button>\n" .
            "    </div>";

        $this->collapse[$enMeal][$foodId]['head'] = $head;
    }

    public function gm_body($enMeal)
    {
        $foodId = $this->menu->food_id;
        if($this->menu->food_type == 0) {
            $active   = $this->gm_body_active($enMeal);
            $common   = $this->gm_body_common($enMeal);
            $halfRes  = $this->gm_body_half_res($enMeal);
            $events   = $this->gm_body_events($enMeal);
            $groups   = $this->gm_body_groups($enMeal);
            $maxRT    = $this->gm_body_max_res_t($enMeal);
            $maxRU    = $this->gm_body_max_res_u($enMeal);
            $dSelect  = $this->gm_body_dessert_select($enMeal);
            $hGarnish = $this->gm_body_garnish($enMeal);
            $dessert  = $this->gm_body_desserts($enMeal);
            $closeAt  = $this->gm_body_close_at($enMeal);

            $this->collapse[$enMeal][$foodId]['body']['active']       = $active;
            $this->collapse[$enMeal][$foodId]['body']['common']       = $common;
            $this->collapse[$enMeal][$foodId]['body']['half_res']     = $halfRes;
            $this->collapse[$enMeal][$foodId]['body']['events']       = $events;
            $this->collapse[$enMeal][$foodId]['body']['groups']       = $groups;
            $this->collapse[$enMeal][$foodId]['body']['max-rt']       = $maxRT;
            $this->collapse[$enMeal][$foodId]['body']['max-ru']       = $maxRU;
            $this->collapse[$enMeal][$foodId]['body']['d_select']     = $dSelect;
            $this->collapse[$enMeal][$foodId]['body']['has_garnish']  = $hGarnish;
            $this->collapse[$enMeal][$foodId]['body']['dessert']      = $dessert;
            $this->collapse[$enMeal][$foodId]['body']['close-at']     = $closeAt;
        }
    }

    public function gm_body_active($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $checked = $this->menu->active == 1 ? 'checked' :'';

        $active = "<div class='form-group'>\n".
                "      <label>\n".
                "          <input type='checkbox' class='align-middle' name='ddf[$this->date][$panelId][$foodId][active]' $checked>\n".
                " فعال/غیرفعال کردن امکان رزرو این غذا          ".
                "      </label>\n".
                "</div><hr> \n";
        return $active;
    }

    public function gm_body_common($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $checked = $this->menu->menu_type == 0 || $this->menu->menu_type == 2 ? 'checked' :'';

        $common = "<div class='form-group'>\n".
                "      <label>\n".
                "          <input type='checkbox' class='align-middle' name='ddf[$this->date][$panelId][$foodId][common]' $checked>\n".
                " انتخاب برای منو عادی          ".
                "      </label>\n".
                "</div><hr> \n";
        return $common;
    }

    public function gm_body_half_res($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $checked = $this->menu->half_reserve == 0 || $this->menu->half_reserve == 2 ? 'checked' :'';

        $hf = "<div class='form-group'>\n".
                "      <label>\n".
                "          <input type='checkbox' class='align-middle' name='ddf[$this->date][$panelId][$foodId][half_res]' $checked>\n".
                " به صورت نیم پرس قابل رزرو باشد          ".
                "      </label>\n".
                "</div><hr> \n";
        return $hf;
    }

    public function gm_body_events($enMeal)
    {
        $eventList = "<table class='table table-bordered table-sm mt-3' style='width: auto'>
                                <thead>
                                <tr>
                                    <th class='text-center'>#</th>
                                    <th class='text-right'>رویداد/مراسم</th>
                                    <th class='text-right'>برگزارکننده</th>
                                    <th class='text-right'>تعداد مهمان</th>
                                    <th class='text-right'>تاریخ برگزاری</th>
                                </tr>
                                </thead>
                                <tbody>";

        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;

        foreach ($this->events as $event) {
            $checked = '';
            $menu = $event->menu()->where('menu.id', $this->menu->id)->first();
            if($menu)
                $checked = 'checked';

            $createdAt = $event->created_at();
            $eventList .= "<tr>
                                <td class='text-center'>
                                    <input type='checkbox' value='$event->id' name='ddf[$this->date][$panelId][$foodId][event][$event->id]' $checked>
                                </td>
                                <td>$event->name</td>
                                <td class='text-right align-middle'>$event->organization</td>
                                <td class='text-right align-middle'>$event->guest_count</td>
                                <td class='text-right align-middle'>$createdAt</td>
                           </tr>";
        }
        $eventList .= '</tbody></table>';

        $ev = "<div class='form-group'>\n".
                "      <label>انتخاب برای مراسمات</label>\n".
                "          <div class='table-responsive'>\n".
                "          $eventList".
                "      </div>\n".
                "      <div class='card card-body bg-warning text-dark p-1'>\n".
                "          <p class='mb-0'><i class='fa fa-warning'></i>  در صورتی که رویداد انتخاب شده ای را اکنون از حالت انتخاب خارج کنید و منو را ذخیره کنید رزرو های صورت گرفته آن رویداد حذف نخواهند شد. </p>\n".
                "      </div>\n".
                "</div><hr> \n";
        return $ev;
    }

    public function gm_body_groups($enMeal)
    {
        $groupsList = "<table class='table table-bordered table-sm mt-3' style='width: auto'>
                            <tbody>";

        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        foreach ($this->userGroups as $userGroup) {
            $checked = '';
            $value = 0;
            $menu = $userGroup->menu()->where('menu.id', $this->menu->id)->first();
            if($menu) {
                $checked = 'checked';
                $value   = $menu->pivot->max_res;
            }

            $groupsList .= "<tr>
                                <td>
                                    <input type='checkbox' value='$userGroup->id' name='ddf[$this->date][$panelId][$foodId][user_group][$userGroup->id][is]' $checked>
                                </td>
                                <td>$userGroup->title</td>
                                <td>سقف مجموع تعداد رزرو ها ازین غذا:</td>
                                <td>
                                    <input type='number' class='text-center' value='$value' name='ddf[$this->date][$panelId][$foodId][user_group][$userGroup->id][count]' style='width: 50px'>
                                </td>
                           </tr>";
        }
        $groupsList .= '</tbody></table>';

        $groups = "<div class='form-group'>\n".
                "      <label>تنظیمات گروه های کاربری</label>\n".
                "          <div class='table-responsive'>\n".
                "          $groupsList".
                "      </div>\n".
                "      <div class='card card-body bg-warning text-dark p-1'>\n".
                "          <p class='mb-0'><i class='fa fa-warning'></i> در صورتی که گروه انتخاب شده ای را اکنون از حالت انتخاب خارج کنید و منو را ذخیره کنید رزرو های صورت گرفته آن گروه حذف نخواهند شد. </p>\n".
                "      </div>\n".
                "      <div class='card card-body bg-warning text-dark p-1'>\n".
                "          <p class='mb-0'><i class='fa fa-warning'></i> برداشتن تیک به معنای عدم امکان رزرو برای آن گروه کاربری است.مقدار صفر 0 به معنای تعداد نامحدود است. </p>\n".
                "      </div>\n".
                "</div><hr> \n";
        return $groups;
    }

    public function gm_body_max_res_t($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $value   = $this->menu->max_reserve_total;
        $maxResTotal = "<input type='number' class='form-control' style='width: 80px; text-align: center' name='ddf[$this->date][$panelId][$foodId][max_res_total]' value='$value'>";

        $mrt = "<div class='form-group'>\n".
            "    <label>سقف مجموع غذای قابل رزرو</label> \n" .
            "    <span class='small text-muted'>برابر تعداد غذایی که اراءه می شود| 0=نامحدود</span> \n".
            "    $maxResTotal \n ".
            "    <span class='text-muted small'>این محدودیت فقط برای غذا ها اعمال میشود.دسر آزاد محدودیت رزرو ندارد</span> \n".
            "</div><hr> \n";
        return $mrt;
    }

    public function gm_body_max_res_u($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $value   = $this->menu->max_reserve_user;
        $maxResUser = "<input type='number' class='form-control' style='width: 80px; text-align: center' name='ddf[$this->date][$panelId][$foodId][max_res]' value='$value'>";

        $mru = "<div class='form-group'>\n".
            "    <label>حداکثر رزرو هر کاربر</label> \n" .
            "    $maxResUser \n ".
            "    <span class='text-muted small'>این محدودیت فقط برای غذا ها اعمال میشود.دسر آزاد محدودیت رزرو ندارد</span> \n".
            "</div><hr> \n";
        return $mru;
    }

    public function gm_body_dessert_select($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $freeChecked    = '';
        $nonFreeChecked = '';
        if($this->menu->dessert_type == 0)
            $freeChecked = 'checked';
        else
            $nonFreeChecked = 'checked';

        $dessertSelectType = "<div class='form-group'>\n".
            "<label class='d-block'>\n".
            "   <input type='radio' class='align-middle' name='ddf[$this->date][$panelId][$foodId][dessert_select]' $freeChecked value='0'> انتخاب دسر با انتخاب کاربر(سلف سرویس) \n".
            "</label> \n".
            "<label class='d-block'>\n".
            "   <input type='radio' class='align-middle' name='ddf[$this->date][$panelId][$foodId][dessert_select]' $nonFreeChecked value='1'> دسر ثابت همراه غذا \n".
            "</label> \n".
            "</div><hr>";

        return $dessertSelectType;
    }

    public function gm_body_garnish($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $hasGarnishValue = $this->menu->has_garnish;

        $garnishTypes = [
            1 => "دارد - رایگان",
            2 => "دارد - اخذ بخشی از قیمت تمام شده",
            3 => "دارد - اخذ تمام قیمت تمام شده",
            4 => "ندارد",
        ];

        $hasGarnish = "<div class='row'>\n".
                      "    <div class='col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12'>\n".
                      "        <div class='form-group'>\n".
                      "            <label>نوع دورچین <strong class='text-danger'>*</strong></label>\n".
                      "            <select class='form-control' name='ddf[$this->date][$panelId][$foodId][has_garnish]'>";
        foreach ($garnishTypes as $key => $garnishType) {
            $selected = '';
            if($key == $hasGarnishValue)
                $selected = 'selected';
            $hasGarnish .= "<option value='$key' $selected>$garnishType</option>\n";
        }

        $hasGarnish .= "       </select>\n".
                      "    </div>\n".
                      "</div>"; //col-6

        return $hasGarnish;
    }

    public function gm_body_desserts($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;

        $dessertsList   = '<table class="table table-bordered table-sm dessert-tbl" style="width: auto">
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
        if($this->menu->dessert_type == 0)
            // means we must build free dessert
            foreach ($this->desserts as $dessert) {
                $checked = '';
                foreach ($this->freeDesserts as $freeDessert) {
                    if($freeDessert->food_id == $dessert->id) {
                        $checked = 'checked';
                        break;
                    }
                }
                $dessertsList .= "<tr>
                                    <td><input type='checkbox' name='ddf[$this->date][$panelId][$foodId][dessert][$dessert->id]' value='$dessert->id' $checked></td>
                                    <td class='text-center'>$dessert->title</td>
                                    <td class='text-center'>$dessert->caption</td>
                               </tr>";
            }

        else
            foreach ($this->desserts as $dessert) {
                $checked = '';
                $menuDessert = $this->menu->desserts()->where('menu_dessert.dessert_id',$dessert->id)->first();
                if($menuDessert)
                    $checked = 'checked';
                $dessertsList .= "<tr>
                                        <td><input type='checkbox' value='$dessert->id' name='ddf[$this->date][$panelId][$foodId][dessert][$dessert->id]' $checked></td>
                                        <td class='text-center'>$dessert->title</td>
                                        <td class='text-center'>$dessert->caption</td>
                                   </tr>";
            }
        $dessertsList .= "</tbody></table>";

        $dView = "<div class='col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12'>\n".
            "        <div class='form-group'>\n".
            "            <label>منو دسر</label> \n".
            "            <div class='table-responsive'>\n".
            "            $dessertsList \n".
            "            </div>\n".
            "        </div>\n".
            "    </div>\n".
            "</div><hr> \n";
        return $dView;
    }

    public function gm_body_close_at($enMeal)
    {
        $panelId = $enMeal;
        $foodId  = $this->menu->food_id;
        $value   = $this->menu->close_at;
        $closeAt = "<input type='text' class='form-control close-at-time ltr text-left' style='width: 250px' placeholder='0000-00-00 00:00:00 فرمت' name='ddf[$this->date][$panelId][$foodId][close_at]' value='$value'>";

        $cAt = "<div class='form-group'>\n".
            "    <label>زمان غیر فعال سازی این منو</label> \n" .
            "    $closeAt \n ".
            "</div><hr> \n";
        return $cAt;
    }

    public function gm_join_parts()
    {
        $result = new \stdClass();
        $meals = config('app.meals');
        $setting = $this->setting;
        foreach ($meals as $prefix=>$meal) {
            $field = $prefix.'_meal_is_active';
            if (!$setting->$field)
                continue;
            $result->$prefix = "";
        }

        //return $this->freeDesserts;
        foreach ($this->collapse as $panelId => $someData1) {
            foreach ($someData1 as $foodId => $someData2) {
                $result->$panelId .= "<div class='card'>";
                $result->$panelId .= $someData2['head'];
                $result->$panelId .= "<div id='collapse$foodId' class='collapse' aria-labelledby='chosen-accord-$foodId' data-parent='#$panelId-chosen-foods'>\n";
                $result->$panelId .="     <div class='card-body'>\n";

                foreach ($someData2['body'] as $item) // e.g $item = ['active' => some html]
                    $result->$panelId .= $item;

                $result->$panelId .= "</div></div></div>";
            }
        }

        $undefinedMenuMsg = "<div class='card card-body bg-light text-dark undefined-food-alert'>\n".
                            "    <p class='mb-0'><i class='fa fa-warning'></i> برای این وعده منو غذا تعریف نشده است. </p>\n".
                            "</div>";

        foreach ($meals as $prefix=>$meal) {
            $field = $prefix.'_meal_is_active';
            if (!$setting->$field)
                continue;
            $result->$prefix = $result->$prefix == "" ? $undefinedMenuMsg : $result->$prefix;
        }

        return $result;
    }


    /*
     * Delete menu
     * Ali Nouri
     * 1399/02/18
     *
     * */

    public function cancel_menu()
    {
        return $this->cm_kernel();
    }

    public function cm_kernel()
    {
        if($this->elDb == 'el')
            $reserves = $this->menu->reservation()
                ->where('eaten',0)
                ->get();
        else
            $reserves = Reservation::where('menu_id',$this->menu->id)
                ->where('eaten',0)
                ->get();

        $menuId = $this->menu->id;
        switch ($this->menuType) {
            case 'food':
                $result = $this->cm_delete_food($reserves,$menuId);
                break;
            case 'dessert':
                $result = $this->cm_delete_dessert($reserves,$menuId);
                break;
            case 'both':
                $this->freeDesserts = Menu::where('date',$this->menu->date)
                    ->where('collection_id',$this->menu->collection_id)
                    ->where('rest_id',$this->menu->rest_id)
                    ->where('meal',$this->menu->meal)
                    ->where('food_type',1)
                    ->get();
                $result = $this->cm_delete_both($reserves,$menuId);
                break;
            default:
                $result = [
                    "status" => 103,
                    "message" => "پارامتر های ورودی را چک کنید",
                ];
        }
        if($this->elDb == 'el')
            $this->menu->delete();
        else
            Menu::destroy($this->menu->id);

        return $result;
    }

    public function cm_pay_back($user, $amount, $cause)
    {
        $wallet = $user->wallet()->orderBy('id','desc')->first();
        if(isset($wallet->id)) {
            $walletAmount = $wallet->amount;
            try {
                $newWallet = new Wallet();
                $newWallet->user_id = $user->id;
                $newWallet->amount = $walletAmount + $amount;
                $newWallet->value = $amount;
                $newWallet->_for = $cause;
                $newWallet->operation = 1;
                if(!$newWallet->save())
                    throw new \Exception;
                return true;
            }
            catch (\Exception $exception) {
                return false;
            }
        }
        else {
            try {
                $newWallet = new Wallet();
                $newWallet->user_id = $user->id;
                $newWallet->amount = $amount;
                $newWallet->value = $amount;
                $newWallet->_for = $cause;
                $newWallet->operation = 1;
                if(!$newWallet->save())
                    throw new \Exception;
                return true;
            }
            catch (\Exception $exception) {
                return false;
            }
        }
    }

    public function cm_delete_food($reserves, $menuId)
    {
        foreach ($reserves as $reserve) {
            $amount = $reserve->pay_amount;
            $user   = $reserve->user;

            $fTitle = $this->menu->food_title;
            $meal   = $this->menu->meal;
            $date   = $this->menu->date;
            $cause  = 'برگشت مبلغ رزرو به کیف پول به دلیل لغو منو. '. $date.'-'. $meal.'-'. $fTitle;

            if($this->payBack) {
                $res = $this->cm_pay_back($user, $amount, $cause);
                if ($res)
                    $reserve->delete();
                else {
                    $ids = "شناسه کاربر: $user->id شناسه منو: $menuId شناسه رزرو: $reserve->id";
                    Activity::save_log('_dev_ddf-payback-fail','برگشت مبلغ به کاربر ناموفق بود',$ids);
                    return [
                        "status" => 102,
                        "message" => "برگشت مبالغ به کاربران کامل نشد.لطفا این مورد را به اطلاع کارشناس سیستم برسانید.کد 001"
                    ];
                }
            }
            else
                $reserve->delete();
        }

        return [
            "status" => 200,
            "message" => "فرآیند انجام شد",
        ];
    }

    public function cm_delete_dessert($reserves, $menuId)
    {
        foreach ($reserves as $reserve) {
            $amount = $reserve->pay_amount;
            $user   = $reserve->user;

            $fTitle = $this->menu->food_title;
            $meal   = $this->menu->meal;
            $date   = $this->menu->date;
            $cause  = 'برگشت مبلغ رزرو به کیف پول به دلیل لغو دسر. '. $date.'-'. $meal.'-'. $fTitle;

            if($this->payBack) {
                $res = $this->cm_pay_back($user, $amount, $cause);
                if ($res)
                    $reserve->delete();
                else {
                    $ids = "شناسه کاربر: $user->id شناسه منو: $menuId شناسه رزرو: $reserve->id";
                    Activity::save_log('_dev_ddf-payback-fail','برگشت مبلغ به کاربر ناموفق بود',$ids);
                    return [
                        "status" => 102,
                        "message" => "برگشت مبالغ به کاربران کامل نشد.لطفا این مورد را به اطلاع کارشناس سیستم برسانید.کد 001"
                    ];
                }
            }
            else
                $reserve->delete();
        }

        return [
            "status" => 200,
            "message" => "فرآیند انجام شد",
        ];
    }

    public function cm_delete_both($reserves, $menuId)
    {
        foreach ($reserves as $reserve) {
            $amount = $reserve->pay_amount;
            $user   = $reserve->user;

            $fTitle = $this->menu->food_title;
            $meal   = $this->menu->meal;
            $date   = $this->menu->date;
            $cause  = 'برگشت مبلغ رزرو به کیف پول به دلیل لغو منو. '. $date.'-'. $meal.'-'. $fTitle;

            if($this->payBack) {
                $res = $this->cm_pay_back($user, $amount, $cause);
                if ($res)
                    $reserve->delete();
                else {
                    $ids = "شناسه کاربر: $user->id شناسه منو: $menuId شناسه رزرو: $reserve->id";
                    Activity::save_log('_dev_ddf-payback-fail','برگشت مبلغ به کاربر ناموفق بود',$ids);
                    return [
                        "status" => 102,
                        "message" => "برگشت مبالغ به کاربران کامل نشد.لطفا این مورد را به اطلاع کارشناس سیستم برسانید.کد 001"
                    ];
                }
            }
            else
                $reserve->delete();
        }

        // حذف دسرهای آزاد با تاریخ و مشخصات شبیه این غذا
        foreach ($this->freeDesserts as $freeDessert) {
            $reserves = $freeDessert->reservation()
                ->where('eaten',0)
                ->get();
            foreach ($reserves as $reserve) {
                $amount = $reserve->pay_amount;
                $user   = $reserve->user;

                $fTitle = $freeDessert->food_title;
                $meal   = $freeDessert->meal;
                $date   = $freeDessert->date;
                $cause  = 'برگشت مبلغ رزرو به کیف پول به دلیل لغو دسر. ' . $date . '-' . $meal . '-' . $fTitle;

                if($this->payBack) {
                    $res = $this->cm_pay_back($user, $amount, $cause);
                    if ($res)
                        $reserve->delete();
                    else
                        return [
                            "status" => 102,
                            "message" => "برگشت مبالغ به کاربران کامل نشد.لطفا این مورد را به اطلاع کارشناس سیستم برسانید.کد 001"
                        ];
                }
                else
                    $reserve->delete();
            }
            $freeDessert->delete();
        }

        return [
            "status" => 200,
            "message" => "فرآیند انجام شد",
        ];
    }
}
