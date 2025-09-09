<?php


namespace App\Library;


use App\Collection;
use App\Event;
use App\Food;
use App\FoodPrice;
use App\Rest;
use App\Setting;
use App\UserGroup;
use Illuminate\Support\Facades\Config;

class Price
{
    public $collId;
    public $restId;
    public $food;
    public $foodId;

    public $setting;
    public $events;
    public $userGroups;
    public $opacity;

    public $onlyView = false;

    public $faMeal;

    public function __construct($data)
    {
        if($data->food_id && $data->coll_id && $data->rest_id) {
            $this->foodId = $data->food_id;
            $this->food = Food::find($this->foodId);
            $this->collId = $data->coll_id;
            $this->restId = $data->rest_id;
        }
        else
            $this->onlyView = true;

        $this->faMeal = config('app.meals');
    }

    public function get_prices()
    {
        $this->data_provider();
        return $this->basic_html();
    }

    public function data_provider()
    {
        $this->userGroups = UserGroup::all();
        $this->events     = Event::where('active', 1)->where('confirmed', 1)->get();
        $this->setting    = Setting::first();
        $this->opacity    = $this->onlyView ? 0.3 : 1;
    }

    public function basic_html()
    {
        $parts = $this->dynamic_content_meal();

        $mealTabs = "";
        $meals = config('app.meals');
        $setting = $this->setting;
        $counter = 0;
        foreach ($meals as $prefix => $meal) {
            $active = '';
            $field = $prefix.'_meal_is_active';
            if ($setting->$field == true) {
                if($counter == 0)
                    $active = 'active';
                $mealTabs .= "<li class='nav-item m-1'>
                                 <a class='btn btn-light btn-sm $active' href='#$prefix' role='tab' data-toggle='tab'>$meal</a>
                              </li>";
                $counter ++;
            }
        }

        $mealTabContent = "";
        $counter = 0;
        foreach ($meals as $prefix => $meal) {
            $active = '';
            if ($setting->$prefix.'_meal_is_active' == true) {
                if ($counter == 0)
                    $active = 'active';

                $p1 = $parts[$prefix][0];
                $p2 = $parts[$prefix][1];
                $p3 = $parts[$prefix][2];
                $mealTabContent .= "<div role='tabpanel' class='tab-pane $active' id='$prefix'>                                               
                                                <p class='text-danger'><i class='fa fa-filter'></i> گروه کاربری</p>
                                                <div class='table-responsive'>
                                                    <table class='table table-sm' style='width: auto'>
                                                        <thead>
                                                        <tr>
                                                            <th class='text-right'>گروه کاربری</th>
                                                            <th class='text-center'>قیمت(ریال)</th>
                                                            <th class='text-center'>درصد/مقدار تخفیف</th>
                                                            <th class='text-center'>برای چه تعداد رزرو اعمال شود</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                            $p1
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class='text-danger mt-5'><i class='fa fa-filter'></i> گروه کاربری + روز فروش</p>
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
                                                        <tbody>
                                                            $p2
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class='text-danger mt-5'><i class='fa fa-filter'></i> رویدادها و مراسمات</p>
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
                                                        <tbody>
                                                            $p3
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>";
                $counter ++;
            }
        }

        $view   = "<p class='text-danger mt-5'><i class='fa fa-filter'></i> وعده های غذایی</p>
                     <fieldset id='meal-separate' style='opacity: $this->opacity'>
                        <div class='row'>
                            <div class='col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='form-group'>
                                    <label class='d-block'>قیمت برای همه وعده ها یکسان است؟</label>
                                    <label for='meal-has-same-price1' class='text-muted'>
                                        <input type='radio' name='meal_has_same_price' class='align-middle' id='meal-has-same-price1' value='1'> بله
                                    </label>
                                    <label for='meal-has-same-price2' class='text-muted'>
                                        <input type='radio' name='meal_has_same_price' class='align-middle' id='meal-has-same-price2' value='0' checked> خیر
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='card text-right'>
                                    <div class='card-header'>
                                        <ul class='nav nav-tabs card-header-tabs p-1'>
                                            $mealTabs
                                        </ul>
                                    </div>
                                    <div class='card-body'>
                                        <div class='tab-content'>
                                            $mealTabContent
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>";
        return $view;
    }

    public function dynamic_content_meal()
    {
        $parts = [];
        $partCount = 3;

        foreach ($this->faMeal as $key => $meal) {
            for ($i = 0; $i<$partCount; $i++)
                $parts[$key][$i] = "";
        }

        $setting = $this->setting;

        foreach ($this->faMeal as $key => $meal) {
            $field = $key.'_meal_is_active';
            if (!$setting->$field)
                continue;
            // Phase 1: general Price
            foreach ($this->userGroups as $userGroup) {

                $priceValue   = 0;
                $priceDAmount = 0;
                $priceDCount  = 0;

                if(!$this->onlyView) {
                    $priceExists = $this->food->price()
                        ->where('usergroup_id', $userGroup->id)
                        ->where('meal', $meal)
                        ->where('rest_id', $this->restId)
                        ->where('collection_id', $this->collId)
                        ->where('type', 0)
                        ->first();

                    if ($priceExists) {
                        $priceValue = $priceExists->price;
                        $priceDAmount = $priceExists->discount;
                        $priceDCount = $priceExists->discount_count;
                    }
                }

                $parts[$key][0] .= "<tr>
                                    <td class='text-right align-middle'>$userGroup->title</td>
                                    <td class='text-right align-middle'>
                                        <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][general][$userGroup->id][price]' value='$priceValue'>
                                    </td>
                                    <td class='text-right align-middle'>
                                        <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][general][$userGroup->id][d_amount]' min='0' value='$priceDAmount'>
                                    </td>
                                    <td class='text-right align-middle'>
                                        <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][general][$userGroup->id][d_count]' value='$priceDCount'>
                                    </td>
                                </tr>";

                // Phase 2: Time price
                $row = "";
                if(!$this->onlyView) {
                    $priceExists = $this->food->price()
                        ->where('usergroup_id', $userGroup->id)
                        ->where('meal', $meal)
                        ->where('rest_id', $this->restId)
                        ->where('collection_id', $this->collId)
                        ->where('type', 1)
                        ->get();
                    foreach ($priceExists as $priceExist) {
                        $priceTimes = $priceExist->times;
                        foreach ($priceTimes as $priceTime) {
                            $row .= "<tr>
                                        <td class='text-right align-middle'>$userGroup->title</td>
                                        <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][from_time][]' id='time-from-saleday-$key-$userGroup->id' value='$priceTime->time_from' placeholder='00:00:00'></td>
                                        <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][to_time][]' id='time-to-saleday-$key-$userGroup->id' value='$priceTime->time_to' placeholder='00:00:00'></td>
                                        <td class='text-center align-middle'><input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][time][$userGroup->id][price][]' value='$priceExist->price'></td>
                                        <td class='text-center align-middle add-btn-wrapper'><button type='button' class='btn btn-info btn-sm add-saleday-time'><i class='fa fa-plus'></i></button></td>
                                    </tr>";
                        }
                    }
                }
                if($row == '')
                    $row = "<tr>
                                <td class='text-right align-middle'>$userGroup->title</td>
                                <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][from_time][]' id='time-from-saleday-bf-$userGroup->id' value='' placeholder='00:00:00'></td>
                                <td class='text-center align-middle'><input type='text' class='form-control d-inline-block ltr text-left' style='width: auto' name='price[$key][time][$userGroup->id][to_time][]' id='time-to-saleday-bf-$userGroup->id' value='' placeholder='00:00:00'></td>
                                <td class='text-center align-middle'><input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][time][$userGroup->id][price][]' value=''></td>
                                <td class='text-center align-middle add-btn-wrapper'><button type='button' class='btn btn-info btn-sm add-saleday-time'><i class='fa fa-plus'></i></button></td>
                            </tr>";
                $parts[$key][1] .= $row;
            }

            // Phase 3: event price
            if(isset($this->events[0]->id))
                foreach($this->events as $i => $event) {
                    $i++;
                    $createdAt = $event->created_at();
                    $parts[$key][2] .= "<tr>
                        <td class='text-center align-middle'>$i</td>
                        <td class='text-right align-middle'>$event->name</td>
                        <td class='text-right align-middle'>$event->organization</td>
                        <td class='text-right align-middle'>$event->guest_count</td>
                        <td class='text-right align-middle'>$createdAt</td>
                        <td class='text-center align-middle'><button type='button' class='btn btn-info btn-sm set-event-price' id='set-event-price-$event->id'>قیمت</button></td>
                    </tr>";

                    $eventGroups = $event->user_groups;

                    $parts[$key][2] .= "<tr id='tr-set-event-price-$event->id' class='d-none'>
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

                            if(!$this->onlyView) {
                                $eventPrice = $event->prices()
                                    ->where('foodmenu_id',$this->foodId)
                                    ->where('usergroup_id',$group->id)
                                    ->where('meal', $meal)
                                    ->where('rest_id', $this->restId)
                                    ->where('collection_id', $this->collId)
                                    ->where('type', 2)
                                    ->first();
                                if($eventPrice)
                                    $priceValue = $eventPrice->price;
                            }
                            $parts[$key][2] .= "<tr>
                                      <td class='text-right align-middle'>$group->title</td>
                                      <td class='text-right align-middle'>
                                          <input type='number' class='form-control text-center d-inline-block' style='width: auto' name='price[$key][event][$event->id][$group->id]' value='$priceValue'>
                                      </td>
                                  </tr>";
                        }
                    else
                        $parts[$key][2] .= "<tr>
                                <td class='text-center align-middle' colspan='2'>گروه کاربری برای این رویداد تعیین نشده است</td>
                            </tr>";

                    $parts[$key][2] .= "</tbody>
                            </table>
                        </div>
                    </td>
                </tr>";
                }
            else
                $parts[$key][2] .= "<tr>
                      <td class='text-center align-middle' colspan='6'>رویداد فعال و تایید شده ای وجود ندارد</td>
                  </tr>";
        }
        return $parts;
    }
}
