<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\Role;
use App\Store;
use App\StoreGoods;
use App\StoreInventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        $stores = Store::all();

        $users = new \stdClass();
        $role = Role::where('title','انباردار')->first();
        if($role)
            $users = $role->roleUser;

        return view('cms.store.index', compact('stores','users'));
    }

    public function store(Request $request)
    {
        /*$ca = Rbac::check_access('goal', 'ذخیره هدف');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');*/

        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|unique:t_store,name',
            'user_id' => 'nullable|numeric|exists:users,id',
        ],[
            'name.required'    => 'نام انبار را وارد کنید',
            'name.string'      => 'فیلد نام انبار نامعتبر است',
            'name.unique'      => 'نام انبار قبلاً ثبت شده است',
            'user_id.numeric' => 'انباردار نامعتبر است',
            'user_id.exists'  => 'انباردار نامعتبر است',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $st = new Store();
        $st->name    = $request->name;
        $st->user_id = $request->user_id == "" ? null : $request->user_id;
        $st->save();
        return redirect()->back()->with('successMsg', 'انبار جدید ذخیره شد.');
    }

    public function edit($id)
    {
        $ca = Rbac::check_access('goal', 'ویرایش هدف');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_goal,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات هدف نامعتبر است');

//        $strategiesTemp = Strategy::all();
        $goal = Goal::find($id);

        return view('cms.goal.edit', [
//            'strategies' => $strategiesTemp,
            'goal' => $goal,
        ]);
    }

    public function update(Request $request)
    {
        $ca = Rbac::check_access('goal', 'بروزرسانی هدف');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');

        $v = Validator::make($request->all(), [
            'goalTitle' => 'required|string|unique:t_goal,title,'. $request->goalId,
            'goalYear'  => 'required|numeric|digits:4'
        ],[
            'goalTitle.required'     => 'عنوان را وارد کنید',
            'goalTitle.string'       => 'فیلد عنوان نامعتبر است',
            'goalTitle.unique'       => 'فیلد عنوان قبلاً ثبت شده است',
            'goalYear.required'      => 'عنوان را وارد کنید',
            'goalYear.numeric'       => 'سال باید عدد باشد',
            'goalYear.digit'         => 'سال باید 4 رقم باشد',
        ]);
        if ($v->fails())
            return redirect()->back()->withErrors($v->errors())->withInput();

        $goal = Goal::find($request->goalId);
        if (!$goal)
            return redirect()->back()->with('dangerMsg', 'مشخصات نامعتبر است');
        $goal->title         = $request->goalTitle;
        $goal->year          = $request->goalYear;
//        $goal->strategy_id   = $request->goalRelatedTo;
        try {
            $goal->update();
            return redirect('/home/goal')->with('successMsg', 'هدف بروزرسانی گردید');
        } catch (\Exception $exception) {
            return redirect()->back()->with('dangerMsg', $exception->getMessage());
        }
    }

    public function de_active($id)
    {
        $ca = Rbac::check_access('goal', 'غیرفعال کردن هدف');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_goal,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات هدف نامعتبر است');

        $goal = Goal::find($id);
        $goal->active = !$goal->active;
        $goal->update();
        return redirect()->back()->with("successMsg", 'فرآیند تغییر وضعیت انجام شد');
    }

    public function delete($id)
    {
        if(Rbac::check_access('goal','حذف هدف')) {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|numeric|exists:t_goal'
            ]);
            if ($validator->fails())
                return redirect()->back()->with('dangerMsg', 'اطلاعات ورودی نامعتبر است');
            Goal::destroy($id);
            return redirect()->back()->with('successMsg', 'اطلاعات هدف حذف گردید');
        }
        return redirect()->back()->with('dangerMsg',__('Your access to this section is not possible'));
    }

    public function goods_index($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_store',
        ]);
        if ($validator->fails())
            return redirect()->back()->with('warningMsg','مشخصات نامعتبر است');

        $store = Store::find($id);
        $storeGoods = $store->goods;

        return view('cms.store.goods_index',compact('store','storeGoods'));
    }

    public function goods_store(Request $request)
    {
        /*$ca = Rbac::check_access('goal', 'ذخیره هدف');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');*/

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:70',
            'brand'       => 'required|string|max:30',
            'amount_unit' => 'required|string|max:30',
            'last_price'  => 'required|numeric|digits_between:0,1000000000',
            'last_amount' => 'required|numeric|digits_between:0,1000000000',
            'nut_value'   => 'required|numeric|digits_between:0,1000000000',
            'store_id'    => 'required|numeric|exists:t_store,id',
        ],[
            'name.required'              => 'نام کالا را وارد کنید',
            'name.string'                => 'فیلد نام کالا نامعتبر است',
            'name.max'                   => 'فیلد نام کالا حداکثر 70 حرف باشد',
            'brand.required'             => 'برند را وارد کنید',
            'brand.string'               => 'فیلد برند نامعتبر است',
            'brand.max'                  => 'فیلد برند حداکثر 30 حرف باشد',
            'amount_unit.required'       => 'واحد مقدار را وارد کنید',
            'amount_unit.string'         => 'فیلد واحد مقدار نامعتبر است',
            'amount_unit.max'            => 'فیلد واحد مقدار حداکثر 30 حرف باشد',
            'last_price.required'        => 'آخرین قیمت را وارد کنید',
            'last_price.numeric'         => 'فیلد آخرین قیمت به صورت عددی باشد',
            'last_price.digits_between'  => 'مقدار آخرین قیمت خارج از بازه عددی مجاز است',
            'last_amount.required'       => 'آخرین مقدار موجودی را وارد کنید',
            'last_amount.numeric'        => 'فیلد آخرین مقدار موجودی به صورت عددی باشد',
            'last_amount.digits_between' => 'آخرین مقدار موجودی خارج از بازه عددی مجاز است',
            'nut_value.required'         => 'ارزش غذایی را وارد کنید',
            'nut_value.numeric'          => 'فیلد ارزش غذایی به صورت عددی باشد',
            'nut_value.digits_between'   => 'ارزش غذایی خارج از بازه عددی مجاز است',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $sg = new StoreGoods();
        $sg->goods_name  = $request->name;
        $sg->brand       = $request->brand;
        $sg->amount_unit = $request->amount_unit;
        $sg->last_price  = $request->last_price;
        $sg->last_amount = $request->last_amount;
        $sg->nut_value   = $request->nut_value;
        $sg->store_id    = $request->store_id;
        $sg->save();
        return redirect()->back()->with('successMsg', 'کالای جدید ذخیره شد.');
    }

    public function goods_sync_reserves(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|numeric|exists:t_store,id',
        ]);
        if ($validator->fails())
            return response()->json(['status' => 101,'res' => 'مشخصات ورودی نامعتبر است']);

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

        $storeId    = $request->store_id;
        $store      = Store::find($storeId);
        $storeRests = $store->rests;
        foreach ($storeRests as $rest) {
            $unSyncedReserves = $rest->reserves()->where('sync_with_store',0)->limit(500)->get();
            if (!isset($unSyncedReserves[0]->id))
                return response()->json(['status' => 102, 'res' => 'رزرو کسر نشده از انبار موجود نیست']);

            foreach ($unSyncedReserves as $unSyncedReserve) {
                $menu   = $unSyncedReserve->menu;
                $food   = $menu->food_menu;
                $stuffs = $food->stuffs;
                foreach ($stuffs as $stuff) {
                    $stuffUnit   = $stuff->amount_unit;
                    $stuffAmount = $stuff->amount;

                    $goods = StoreGoods::where('store_id',$storeId)->where('goods_name',$stuff->stuff_name)->first();
                    if(!$goods)
                        return response()->json(['status' => 102, 'res' => 'کالای '.$stuff->stuff_name.' در این انبار ثبت نشده است']);


                    $unit       = $weightConverter[$stuffUnit][$goods->amount_unit];
                    $convertedW = $unit * $stuffAmount;

                    $newInventory = new StoreInventory();
                    $newInventory->amount   = $convertedW;
                    $newInventory->operator = 'کاهش';
                    $newInventory->goods_id = $goods->id;
                    $newInventory->save();

                    $goods->last_amount = $goods->last_amount - $convertedW;
                    $goods->update();
                }
                $unSyncedReserve->sync_with_store = 1;
                $unSyncedReserve->update();
            }
        }
        if(!isset($storeRests[0]->id))
            return response()->json(['status' => 102, 'res' => 'هیچ کدام از رستوران ها/سلف سرویس ها به این انبار نسبت داده نشده اند']);

        return response()->json(['status' => 200, 'res' => 'فرآیند با موفقیت انجام شد']);
    }

    public function inventory_index($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_store_goods',
        ]);
        if ($validator->fails())
            return redirect()->back()->with('warningMsg','مشخصات نامعتبر است');

        $storeGoods = StoreGoods::find($id);
        $goodsInventory = $storeGoods->inventory;

        return view('cms.store.inventory_index',compact('goodsInventory','storeGoods'));
    }

    public function inventory_store(Request $request)
    {
        /*$ca = Rbac::check_access('goal', 'ذخیره هدف');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');*/

        $validator = Validator::make($request->all(), [
            'operator' => 'required|in:افزایش,کاهش',
            'amount'   => 'required|numeric|digits_between:0,1000000000',
            'price'    => 'required|numeric|digits_between:0,1000000000',
            'goods_id' => 'required|numeric|exists:t_store_goods,id',
        ],[
            'operator.required'     => 'نوع فرآیند را انتخاب کنید',
            'operator.in'           => 'نوع فرآیند نامعتبر است',
            'amount.required'       => 'مقدار را وارد کنید',
            'amount.numeric'        => 'فیلد مقدار به صورت عددی باشد',
            'amount.digits_between' => 'مقدار خارج از بازه عددی مجاز است',
            'price.required'        => 'قیمت خرید را وارد کنید',
            'price.numeric'         => 'قیمت خرید به صورت عددی باشد',
            'price.digits_between'  => 'قیمت خرید خارج از بازه عددی مجاز است',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $amount = $request->amount;
        $price  = $request->price;

        $sg = StoreGoods::find($request->goods_id);
        $lastAmount = $sg->last_amount;
        $sg->last_amount = $request->operator == "افزایش" ?
            $lastAmount + $amount :
            $lastAmount - $amount;
        $sg->last_price = $price;
        $sg->update();


        $si = new StoreInventory();
        $si->amount   = $amount;
        $si->price    = $price;
        $si->operator = $request->operator;
        $si->goods_id = $sg->id;
        $si->save();
        return redirect()->back()->with('successMsg', 'اطلاعات موجودی ذخیره شد.');
    }
}
