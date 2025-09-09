<?php

namespace App\Http\Controllers\Cms;

use App\Collection;
use App\Event;
use App\Facades\Rbac;
use App\Food;
use App\FoodPrice;
use App\FoodStuffs;
use App\FreeFoodMenu;
use App\FreeFoodPrice;
use App\FreeUserGroup;
use App\Library\Price;
use App\PriceEvent;
use App\PriceTime;
use App\Rest;
use App\Setting;
use App\StoreGoods;
use App\UserGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public $mealConvert = [
        1 => "صبحانه",
        2 => "نهار",
        3 => "شام",
    ];

    public $porsTypes = [
        'پرس',
        'نیم پرس'
    ];

    public function index()
    {
        if(Rbac::check_access('foodmenu','index')) {
            $storeGoodNames   = StoreGoods::all();

            $userGroups     = UserGroup::all();
            $events         = Event::where('active', 1)->where('confirmed', 1)->get();

            $firstCollection = Collection::first();
            $collections     = Collection::all();
            $collList = "";
            $restList = "";
            if($firstCollection) {
                $collList .= '<div class="form-control" style="height: 180px; max-height: 180px; overflow: auto"><table class="table table-bordered table-sm">
                                <tbody>';
                $restList .= '<div class="form-control" style="height: 180px; max-height: 180px; overflow: auto" id="restlist-'.$firstCollection->id.'"><table class="table table-bordered table-sm">
                                <tbody>';

                $restsFirstColl = $firstCollection->rests;
                $collList .= '<tr>
                                      <td class="align-middle"><input type="checkbox" class="collection-check" name="collection[]" id="collection-'.$firstCollection->id.'" value="'.$firstCollection->id.'" checked>'.$firstCollection->name.'</td>
                                  </tr>';
                foreach ($restsFirstColl as $i => $value) {
                    $checked = '';
                    $dNone = 'd-none';
                    if($i == 0) {
                        $checked = 'checked';
                        $dNone = '';
                    }
                    $restList .= '<tr>
                                        <td class="align-middle"><input type="checkbox" name="rest['.$firstCollection->id.'][]" class="rest-check" id="rest-col-'.$value->id.'" value="'.$value->id.'" '.$checked.'>'.$value->name.'</td>
                                        <td class="align-middle text-center"><button type="button" id="get-price-rest-'.$firstCollection->id.'-'.$value->id.'" class="btn btn-secondary '.$dNone.' btn-sm get-price-rest">قیمت ها</button></td>
                                   </tr>';
                }
                $restList .= '</tbody></table></div>';

                foreach ($collections as $collection) {
                    if($collection->id == $firstCollection->id)
                        continue;
                    $rests = $collection->rests;
                    $collList .= '<tr>
                                      <td class="align-middle"><input type="checkbox" name="collection[]" class="collection-check" id="collection-'.$collection->id.'" value="'.$collection->id.'">'.$collection->name.'</td>
                                  </tr>';

                    $restList .= '<div class="form-control mt-1" style="height: 180px; max-height: 180px; display:none; overflow: auto" id="restlist-'.$collection->id.'"><table class="table table-bordered table-sm">
                                <tbody>';
                    foreach ($rests as $value) {
                        $restList .= '<tr>
                                        <td class="align-middle"><input type="checkbox" name="rest['.$collection->id.'][]" class="rest-check" id="rest-col-'.$value->id.'" value="'.$value->id.'">'.$value->name.'</td>
                                        <td class="align-middle text-center"><button type="button" id="get-price-rest-'.$collection->id.'-'.$value->id.'" class="btn btn-secondary btn-sm get-price-rest">قیمت ها</button></td>
                                   </tr>';
                    }
                    $restList .= '</tbody></table></div>';
                }
                $collList .= '</tbody></table></div>';
            }

            // in here we need just empty view
            $data   = new \stdClass();
            $data->food_id = null;
            $data->coll_id = null;
            $data->rest_id = null;
            $priceClass    = new Price($data);
            $view = $priceClass->get_prices();

            return view('cms.foodmenu.index', [
                'goodNames'  => $storeGoodNames,
                'collects'   => $collList,
                'rests'      => $restList,
                'userGroups' => $userGroups,
                'events'     => $events,
                'priceView'  => $view,
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('foodmenu','store')) {
            $v = Validator::make($request->all(), [
                'title'     => 'required|string|max:190',
                'caption'   => 'nullable|string|max:100',
                'food_type' => 'required|in:0,1',
                'pic'       => 'nullable|image|max:1000',
                'swf_code'  => 'nullable|numeric|digits_between:0,200',
            ]);
            if($v->fails())
                return response()->json(['status' => 102, 'res' => 'داده های ورودی نامعتبر است']);

            $title   = $request->title;
            $type    = $request->food_type;
            $caption = $request->caption;
            $swfCode = $request->swf_code;

            $foodStuffs = $request->has('food_stuff') ? $request->food_stuff : [];
            $foodStuffsIds = isset($foodStuffs['id']) ? $foodStuffs['id'] : [];

            foreach ($foodStuffsIds as $foodStuffsId) {
                if(!is_numeric($foodStuffsId) || $foodStuffsId <= 0)
                    return response()->json(['status' => 102, 'res' => 'مشخصات مواد اولیه صحیح نیست']);
                $goodStore = StoreGoods::find($foodStuffsId);
                if(!$goodStore)
                    return response()->json(['status' => 102, 'res' => 'مشخصات مواد اولیه صحیح نیست']);

                $foodStuffsAmount = $foodStuffs[$foodStuffsId]['amount'];
                if(!is_numeric($foodStuffsAmount) || $foodStuffsAmount <= 0)
                    return response()->json(['status' => 102, 'res' => 'مشخصات مقدار مواد اولیه صحیح نیست']);
            }

            $fd  = Food::where('title',$title)->first();
            if($request->has('pic')) {
                if($fd) {
                    $filename = mb_substr($fd->pic,1,strlen($fd->pic));
                    File::delete($filename);
                }

                $time      = time();
                $extension = $request->file('pic')->getClientOriginalExtension();
                $filename  = 'food_pic_' .$time . '.' . $extension;
                if(env('APP_ENV') == "local")
                    $destination = public_path('img/pic/');
                else
                    $destination = base_path('img/pic/');
                $request->file('pic')->move($destination, $filename);
                $filename = '/img/pic/' . $filename;
            }

            $updateOrCreate = 'update';
            if(!$fd) {
                $fd = new Food();
                $updateOrCreate = 'create';
            }

            $fd->title    = $title;
            $fd->caption  = $caption;
            $fd->type     = $type;
            $fd->swf_code = $swfCode;
            $fd->pic      = isset($filename) ? $filename : $fd->pic;
            $fd->save();

            $weightConverter = [
                'گرم' => [
                    'گرم'     => 1,
                    'کیلوگرم' => 0.001,
                ],
                'کیلوگرم' => [
                    'گرم'     => 1000,
                    'کیلوگرم' => 1,
                ],
            ];

            $newGoodNames = [];
            foreach ($foodStuffsIds as $foodStuffsId) {
                $foodStuffsUnit   = $foodStuffs[$foodStuffsId]['unit'];
                $foodStuffsAmount = $foodStuffs[$foodStuffsId]['amount'];

                $goodStore       = StoreGoods::find($foodStuffsId);
                $foodStaffExists = $fd->stuffs()->where('stuff_name',$goodStore->goods_name)->first();

                $unit         = $weightConverter[$foodStuffsUnit][$goodStore->amount_unit];
                $convertedW   = $unit * $foodStuffsAmount;
                $nutValue     = $convertedW * (float)$goodStore->nut_value;
                $nutValueStr  = "$nutValue کالری ";

                if($foodStaffExists) {
                    $foodStaffExists->amount         = $foodStuffsAmount;
                    $foodStaffExists->amount_unit    = $foodStuffsUnit;
                    $foodStaffExists->nut_value      = $nutValueStr;
                    $foodStaffExists->store_goods_id = $foodStuffsId;
                    $foodStaffExists->save();
                }
                else {
                    $fs = new FoodStuffs();
                    $fs->food_id     = $fd->id;
                    $fs->stuff_name  = $goodStore->goods_name;
                    $fs->amount      = $foodStuffsAmount;
                    $fs->amount_unit = $foodStuffsUnit;
                    $fs->nut_value   = $nutValueStr;
                    $fs->store_goods_id = $foodStuffsId;
                    $fs->save();
                }
                array_push($newGoodNames,$goodStore->goods_name);
            }

            if($updateOrCreate == 'update') {
                $alreadyFoodStuffIds = $fd->stuffs()->get(['id','stuff_name']);
                foreach ($alreadyFoodStuffIds as $alreadyFoodStuffId) {
                    if(!in_array($alreadyFoodStuffId->stuff_name,$newGoodNames))
                        FoodStuffs::destroy($alreadyFoodStuffId->id);
                }

                return response()->json(['status' => 200, 'res' => $title . ' بروزرسانی شد','id' => $fd->id]);
            }
            return response()->json(['status' => 200, 'res' => $title.' ایجاد شد','id' => $fd->id]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function search(Request $request)
    {
        if(Rbac::check_access('foodmenu','index')) {
            $validator = Validator::make($request->all(), [
                'food_title' => 'required|string',
            ], [
                'food_title.required' => 'عنوان غذا را وارد کنید',
                'food_title.string' => 'عنوان غذا نامعتبر است',
                'food_title.exists' => 'عنوان غذا پیدا نشد',
            ]);
            if ($validator->fails())
                return response()->json(['status' => 102, 'res' => 'مشخصات ورودی نامعتبر است']);

            $title = $request->food_title;
            $foods = Food::where('title', 'like', '%' . $title . '%')
                ->where('is_active', 1)
                ->get();
            if (isset($foods[0]->id)) {
                $res = "<table class='table table-striped table-bordered table-sm'>
                            <thead>
                            <tr>
                                <th class=\"text-center\">#</th>
                                <th class=\"text-center\">تصویر</th>
                                <th class=\"text-center\">عنوان</th>
                                <th class=\"text-center\">نوع</th>
                                <th class=\"text-center\">کپشن</th>
                                <th class=\"text-center\">کد swf</th>
                                <th class=\"text-center\">ویرایش</th>
                                <th class=\"text-center\">حذف</th>
                            </tr>
                            </thead>
                            <tbody>";
                foreach ($foods as $i => $food) {
                    $i++;
                    $type = $food->type == 0 ? 'غذا' : 'دسر';
                    $stuffs = $food->stuffs;
                    $res .= "<tr>
                            <td class=\"text-center align-middle\">$i</td>
                            <td class=\"text-center align-middle\"><img src=\"$food->pic\" style=\"width: 40px; height: 40px; border-radius: 100%\"></td>
                            <td class=\"text-center align-middle\">$food->title</td>
                            <td class=\"text-center align-middle\">$type</td>
                            <td class=\"text-center align-middle\">$food->caption</td>
                            <td class=\"text-center align-middle\">$food->swf_code</td>
                            <td class=\"text-center align-middle\">
                                <a class=\"btn btn-light btn-sm edit-food-link\" data-id='$food->id' href=\"javascript:void(0)\"><i class=\"fa fa-edit text-warning\"></i></a>
                            </td>
                            <td class=\"text-center align-middle\">
                                <a class=\"btn btn-light btn-sm del-food-link\" href=\"/home/foods/delete/$food->id\"><i class=\"fa fa-trash text-danger\"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='5'>
                                <table class='table table-sm'>
                                    <tbody>";
                    foreach ($stuffs as $k => $stuff) {
                        $k++;
                        $res .= "<tr class='stuff-row'>
                                 <td>$k</td>
                                 <td>$stuff->stuff_name</td>
                                 <td>$stuff->amount</td>
                                 <td>$stuff->amount_unit</td>
                             </tr>";
                    }
                    $res .= "           </tbody>
                                </table>
                            </td>
                            <td colspan='3'></td>
                            </td>
                            </tr>
                        ";
                }
                $res .= "</tbody></table>";
                return response()->json(['status' => 200, 'res' => $res]);
            }
            $res = "<p>هیچ غذایی مطابق با جست و جو شما پیدا نشد</p>";
            return response()->json(['status' => 200, 'res' => $res]);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);
    }

    public function price_store(Request $request)
    {
        if(!Rbac::check_access('foodmenu','store'))
            return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);

        $v = Validator::make($request->all(),[
            'food_id'             => 'required|numeric|exists:food,id',
            'meal_has_same_price' => 'required|in:0,1',
            'collection'          => 'required|array',
            'rest'                => 'required|array',
        ]);
        if($v->fails())
            return response()->json(['status' => 101,'res' => $v->errors()]);

        $faMeal = config('app.meals');

        $mhsp        = $request->meal_has_same_price;
        $collectionRests = $request->rest;
        $prices      = $request->price;
        $foodId      = $request->food_id;

        if($mhsp) {
            foreach ($faMeal as $prefix => $meal)
                $prices[$prefix] = $prices['bf'];
        }

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

                foreach ($prices as $enMeal => $data) {
                    $meal = $faMeal[$enMeal];

                    // Phase 1 - save general price
                    foreach ($data['general'] as $userGroupId => $someData) {
                        if(is_numeric($someData['price']) &&
                            is_numeric($someData['d_amount']) &&
                            is_numeric($someData['d_count']))
                        {
                            $priceExists = FoodPrice::where('foodmenu_id', $foodId)
                                ->where('usergroup_id', $userGroupId)
                                ->where('meal', $meal)
                                ->where('rest_id', $rest)
                                ->where('collection_id', $collection)
                                ->where('type', 0)
                                ->first();
                            if($priceExists) {
                                $priceExists->price          = $someData['price'];
                                $priceExists->discount_count = $someData['d_count'];
                                $priceExists->discount       = $someData['d_amount'];
                                $priceExists->save();
                            }
                            else {
                                $priceNew = new FoodPrice();
                                $priceNew->foodmenu_id = $foodId;
                                $priceNew->usergroup_id = $userGroupId;
                                $priceNew->meal = $meal;
                                $priceNew->rest_id = $rest;
                                $priceNew->collection_id = $collection;
                                $priceNew->type = 0;
                                $priceNew->price          = $someData['price'];
                                $priceNew->discount_count = $someData['d_count'];
                                $priceNew->discount       = $someData['d_amount'];
                                $priceNew->save();
                            }
                        }
                    }

                    // Phase 2 - save price time
                    foreach ($data['time'] as $userGroupId => $someDate) {
                        for ($i = 0; $i < count($someDate['from_time']); $i++) {
                            if(isset($someDate['to_time'][$i]) && isset($someDate['price'][$i]) && $someDate['to_time'][$i]  && $someDate['price'][$i]) {
                                $priceExists = FoodPrice::where('foodmenu_id', $foodId)
                                    ->where('usergroup_id', $userGroupId)
                                    ->where('meal', $meal)
                                    ->where('rest_id', $rest)
                                    ->where('collection_id', $collection)
                                    ->where('type', 1)
                                    ->where('price', $someDate['price'][$i])
                                    ->first();
                                if($priceExists)
                                    $priceId = $priceExists->id;
                                else {
                                    $priceNew = new FoodPrice();
                                    $priceNew->foodmenu_id = $foodId;
                                    $priceNew->usergroup_id = $userGroupId;
                                    $priceNew->meal = $meal;
                                    $priceNew->rest_id = $rest;
                                    $priceNew->collection_id = $collection;
                                    $priceNew->type = 1;
                                    $priceNew->price = $someDate['price'][$i];
                                    $priceNew->save();
                                    $priceId = $priceNew->id;
                                }

                                $pTimeExists = PriceTime::where('price_id',$priceId)
                                    ->where('time_from',$someDate['from_time'][$i])
                                    ->where('time_to',$someDate['to_time'][$i])
                                    ->first();
                                if(!$pTimeExists) {
                                    $pTime = new PriceTime();
                                    $pTime->time_from = $someDate['from_time'][$i];
                                    $pTime->time_to = $someDate['to_time'][$i];
                                    $pTime->price_id = $priceId;
                                    $pTime->save();
                                }
                            }
                        }
                    }

                    // Phase 3 - save event price
                    if(isset($data['event'])) {
                        foreach ($data['event'] as $eventId => $someDate) {
                            foreach ($someDate as $userGroupId => $priceValue) {
                                if ($priceValue) {
                                    $event = Event::find($eventId);
                                    if (!$event)
                                        return response()->json(['status' => 102, 'res' => '#خطای رویداد: مشخصات رویداد نامعتبر است']);

                                    $eventPrice = $event->prices()->where('foodmenu_id', $foodId)
                                        ->where('usergroup_id', $userGroupId)
                                        ->where('meal', $meal)
                                        ->where('rest_id', $rest)
                                        ->where('collection_id', $collection)
                                        ->where('type', 2)
                                        ->first();
                                    if ($eventPrice) {
                                        $eventPrice->price = $priceValue;
                                        $eventPrice->save();
                                    } else {
                                        $priceNew = new FoodPrice();
                                        $priceNew->foodmenu_id = $foodId;
                                        $priceNew->usergroup_id = $userGroupId;
                                        $priceNew->meal = $meal;
                                        $priceNew->rest_id = $rest;
                                        $priceNew->collection_id = $collection;
                                        $priceNew->type = 2;
                                        $priceNew->price = $priceValue;
                                        $priceNew->save();

                                        PriceEvent::create([
                                            'price_id' => $priceNew->id,
                                            'event_id' => $eventId,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json(['status' => 200,'res' => 'تغییرات قیمت با موفقیت اعمال شد']);
    }

    public function price_edit(Request $request)
    {
        if(Rbac::check_access('foodmenu','store')) {
            $v = Validator::make($request->json()->all(), [
                'food_id' => 'required|numeric|exists:food,id',
            ]);
            if($v->fails())
                return response()->json(['status' => 101, 'res' => 'اطلاعات ورودی نامعتبر است']);
            $foodId = $request->json()->get('food_id');
            $firstCollection = Collection::first();
            if(!$firstCollection)
                return response()->json(['status' => 101, 'res' => 'حداقل یک مجموعه در سیستم باید تعریف شده باشد']);
            $rest = $firstCollection->rests()->first();
            if(!$rest)
                return response()->json(['status' => 101, 'res' => 'حداقل یک رستوران یا سلف سرویس در سیستم باید تعریف شده باشد']);

            $data   = new \stdClass();
            $data->food_id = $foodId;
            $data->coll_id = $firstCollection->id;
            $data->rest_id = $rest->id;
            $priceClass    = new Price($data);
            $view = $priceClass->get_prices();
            return response()->json(['status' => 200, 'res' => $view]);
        }
        return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);
    }

    public function get_rest_price(Request $request)
    {
        if(!Rbac::check_access('foodmenu','store'))
            return response()->json(['status' => 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست']);

        $v = Validator::make($request->json()->all(), [
            'f_id' => 'required|numeric|exists:food,id',
            'r_id' => 'required|numeric|exists:t_rest,id',
        ]);
        if($v->fails())
            return response()->json(['status' => 101, 'res' => 'اطلاعات ورودی نامعتبر است']);


        $foodId = $request->json()->get('f_id');
        $restId = $request->json()->get('r_id');
        $rest = Rest::find($restId);
        $coll = $rest->collection;

        $data   = new \stdClass();
        $data->food_id = $foodId;
        $data->coll_id = $coll->id;
        $data->rest_id = $restId;

        $priceClass = new Price($data);
        $view = $priceClass->get_prices();
        return response()->json(['status' => 200, 'res' => $view]);
    }

    public function delete($id)
    {
        if(Rbac::check_access('foodmenu','delete')) {
            Validator::make(['id' => $id], [
                'id' => 'required|numeric|exists:food',
            ])->validate();
            $fp = Food::find($id);
            $fp->is_active = 0;
            if ($fp->save())
                return redirect()->back()->with('successMsg', 'غذا غیرفعال شد');
            return redirect()->back()->with('dangerMsg', 'غیرفعال کردن غذا ناموفق بود');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
