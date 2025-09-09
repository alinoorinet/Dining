<?php

namespace App\Http\Controllers\Cms;

use App\Collection;
use App\Facades\Rbac;
use App\Rest;
use App\RestInfo;
use App\Role;
use App\Store;
use App\User;
use App\UsersRests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        $ca = Rbac::check_access('rest', 'view_rest');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');

        $rests  = Rest::all();
        $stores = Store::all();

        $contractors = new \stdClass();
        $role = Role::where('title','پیمانکار')->first();
        if($role)
            $contractors = $role->roleUser;

        $collections = Collection::all();

        return view('cms.rest.index', compact('rests','stores','contractors','collections'));
    }

    public function store(Request $request)
    {
        $ca = Rbac::check_access('rest', 'add_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|unique:t_rest,name',
            'type'          => 'required|in:آزاد,دولتی,مکمل',
            'sex'           => 'required|in:برادران,خواهران,مختلط',
            'collection_id' => 'nullable|numeric|exists:t_collection,id',
            'store_id'      => 'nullable|numeric|exists:t_store,id',
            'contractor_id' => 'nullable|numeric|exists:users,id',
            'ips'           => 'nullable|array',
            'ips.*'         => 'nullable|ip',
            'description'   => 'nullable|array',
            'description.*' => 'nullable|string|max:255',
            'close_at'      => 'required|numeric',
        ],[
            'name.required'         => 'نام را وارد کنید',
            'name.string'           => 'فیلد نام نامعتبر است',
            'name.unique'           => 'نام قبلاً ثبت شده است',
            'type.required'         => 'نوع را انتخاب کنید',
            'type.in'               => 'نوع نامعتبر است',
            'sex.required'          => 'جنسیت را انتخاب کنید',
            'sex.in'                => 'جنسیت نامعتبر است',
            'collection_id.numeric' => 'نام مجموعه نامعتبر است',
            'collection_id.exists'  => 'نام مجموعه نامعتبر است',
            'store_id.numeric'      => 'نام انبار نامعتبر است',
            'store_id.exists'       => 'فیلد نام انبار نامعتبر است',
            'contractor_id.numeric' => 'نام پیمانکار نامعتبر است',
            'contractor_id.exists'  => 'فیلد نام پیمانکار نامعتبر است',
            'ips.array'             => 'فیلد آدرس Ip نامعتبر است',
            'ips.*.ip'              => 'آدرس Ip نامعتبر است',
            'description.array'     => 'فیلد توضیحات نامعتبر است',
            'description.*.string'  => 'توضیحات نامعتبر است',
            'description.*.max'     => 'توضیحات حداکثر 255 کاراکتر باشد',
            'close_at.required'     => 'زمان محدود شدن قابلیت رزرو را وارد کنید',
            'close_at.numeric'      => 'زمان محدود شدن قابلیت رزرو باید عدد باشد',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $rest = new Rest();
        $rest->name = $request->name;
        $rest->type = $request->type;
        $rest->sex  = $request->sex;
        $rest->close_at      = $request->close_at;
        $rest->collection_id = $request->collection_id == "" ? null : $request->collection_id;
        $rest->store_id      = $request->store_id      == "" ? null : $request->store_id;
        $rest->contractor_id = $request->contractor_id == "" ? null : $request->contractor_id;
        $rest->save();

        $ips   = $request->has('ips') ? $request->ips: [];
        $descs = $request->has('description') ? $request->description: [];
        foreach ($ips as $i => $ip) {
            if($ip != "") {
                $restInfo = new RestInfo();
                $restInfo->ip = $ip;
                $restInfo->description = isset($descs[$i]) ? $descs[$i] : null;
                $restInfo->rest_id = $rest->id;
                $restInfo->save();
            }
        }

        return redirect()->back()->with('successMsg', 'رستوران/سلف سرویس جدید ذخیره شد.');
    }

    public function edit($id)
    {
        $ca = Rbac::check_access('rest', 'edit_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_rest,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات رستوران نامعتبر است');

        $rest = Rest::find($id);
        $restInfo = $rest->info;

        $stores = Store::all();

        $contractors = new \stdClass();
        $role = Role::where('title','پیمانکار')->first();
        if($role)
            $contractors = $role->roleUser;

        $collections = Collection::all();

        return view('cms.rest.edit', [
            'rest'        => $rest,
            'restInfo'    => $restInfo,
            'stores'      => $stores,
            'contractors' => $contractors,
            'collections' => $collections,
        ]);
    }

    public function update(Request $request)
    {
        $ca = Rbac::check_access('rest', 'add_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
        /*echo "<pre>";
        print_r($request->all());
        exit();*/
        $validator = Validator::make($request->all(), [
            'rest_id'       => 'required|string|exists:t_rest,id',
            'name'          => 'required|string|unique:t_rest,name,'.$request->rest_id,
            'type'          => 'required|in:آزاد,دولتی,مکمل',
            'sex'           => 'required|in:برادران,خواهران,مختلط',
            'collection_id' => 'nullable|numeric|exists:t_collection,id',
            'store_id'      => 'nullable|numeric|exists:t_store,id',
            'contractor_id' => 'nullable|numeric|exists:users,id',
            'ips'           => 'nullable|array',
            'ips.*'         => 'nullable|ip',
            'description'   => 'nullable|array',
            'description.*' => 'nullable|string|max:255',
            'close_at'      => 'required|numeric',
        ],[
            'name.required'         => 'نام را وارد کنید',
            'name.string'           => 'فیلد نام نامعتبر است',
            'name.unique'           => 'نام قبلاً ثبت شده است',
            'type.required'         => 'نوع را انتخاب کنید',
            'type.in'               => 'نوع نامعتبر است',
            'sex.required'          => 'جنسیت را انتخاب کنید',
            'sex.in'                => 'جنسیت نامعتبر است',
            'collection_id.numeric' => 'نام مجموعه نامعتبر است',
            'collection_id.exists'  => 'نام مجموعه نامعتبر است',
            'store_id.numeric'      => 'نام انبار نامعتبر است',
            'store_id.exists'       => 'فیلد نام انبار نامعتبر است',
            'contractor_id.numeric' => 'نام پیمانکار نامعتبر است',
            'contractor_id.exists'  => 'فیلد نام پیمانکار نامعتبر است',
            'ips.array'             => 'فیلد آدرس Ip نامعتبر است',
            'ips.*.ip'              => 'آدرس Ip نامعتبر است',
            'description.array'     => 'فیلد توضیحات نامعتبر است',
            'description.*.string'  => 'توضیحات نامعتبر است',
            'description.*.max'     => 'توضیحات حداکثر 255 کاراکتر باشد',
            'close_at.required'     => 'زمان محدود شدن قابلیت رزرو را وارد کنید',
            'close_at.numeric'      => 'زمان محدود شدن قابلیت رزرو باید عدد باشد',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $rest = Rest::find($request->rest_id);
        $rest->name = $request->name;
        $rest->type = $request->type;
        $rest->sex  = $request->sex;
        $rest->close_at      = $request->close_at;
        $rest->collection_id = $request->collection_id == "" ? null : $request->collection_id;
        $rest->store_id      = $request->store_id      == "" ? null : $request->store_id;
        $rest->contractor_id = $request->contractor_id == "" ? null : $request->contractor_id;
        $rest->save();

        $ips   = $request->has('ips') ? $request->ips: [];
        $descs = $request->has('description') ? $request->description: [];
        foreach ($ips as $i => $ip) {
            if($ip != "") {
                $checkInfo = $rest->info()->where('ip', $ip)->first();
                if(!$checkInfo) {
                    $restInfo = new RestInfo();
                    $restInfo->ip = $ip;
                    $restInfo->description = isset($descs[$i]) ? $descs[$i] : null;
                    $restInfo->rest_id = $rest->id;
                    $restInfo->save();
                }
                else {
                    $checkInfo->ip = $ip;
                    $checkInfo->description = isset($descs[$i]) ? $descs[$i] : null;
                    $checkInfo->save();
                }
            }
        }

        return redirect()->back()->with('successMsg', 'رستوران/سلف سرویس بروزرسانی شد.');
    }

    public function de_active($id)
    {
        $ca = Rbac::check_access('rest', 'delete_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

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
        $ca = Rbac::check_access('rest', 'delete_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_goal'
        ]);
        if ($validator->fails())
            return redirect()->back()->with('dangerMsg', 'اطلاعات ورودی نامعتبر است');
        Goal::destroy($id);
        return redirect()->back()->with('successMsg', 'اطلاعات هدف حذف گردید');
    }

    public function info($id)
    {
        $ca = Rbac::check_access('rest', 'view_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_rest,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات رستوران نامعتبر است');

        $rest = Rest::find($id);
        $users = $rest->users()->paginate(50);
        return view('cms.rest.info', [
            'rest' => $rest,
            'users' => $users,
        ]);
    }

    public function info_store(Request $request)
    {
        $ca = Rbac::check_access('rest', 'add_rest');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|exists:users,username',
            'rest_id'  => 'required|string|exists:t_rest,id',
        ],[
            'username.required' => 'شناسه کاربری را وارد کنید',
            'username.string'   => 'فیلد شناسه کاربری نامعتبر است',
            'username.unique'   => 'شناسه کاربری پیدا نشد',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $user = User::where('username',$request->username)->first();

        $checkExists = UsersRests::where('user_id',$user->id)->where('rest_id',$request->rest_id)->first();
        if($checkExists)
            return redirect()->back()->with('warningMsg', 'دسترسی قبلا به کاربر تخصیص داده شده است.');

        $rest = Rest::find($request->rest_id);
        if($rest->sex != 3 && $rest->sex != $user->sex)
            return redirect()->back()->with('warningMsg', 'جنسیت تعیین شده برای رستوران با جنسیت کاربر تناقض دارد.');

        $ur = new UsersRests();
        $ur->user_id = $user->id;
        $ur->rest_id = $rest->id;
        $ur->save();

        return redirect()->back()->with('successMsg', 'دسترسی ایجاد شد.');
    }
}
