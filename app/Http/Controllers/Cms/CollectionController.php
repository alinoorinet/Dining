<?php

namespace App\Http\Controllers\Cms;

use App\Collection;
use App\Facades\Rbac;
use App\Role;
use App\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        $collections = Collection::all();

        return view('cms.collection.index', compact('collections'));
    }

    public function store(Request $request)
    {
        /*$ca = Rbac::check_access('goal', 'ذخیره هدف');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');*/

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|unique:t_collection,name',
            'independent' => 'required|in:0,1',
            'parent_id'   => 'nullable|string',
            'equal_param_auth' => 'nullable|numeric|max:100',
        ],[
            'name.required'        => 'نام مجموعه را وارد کنید',
            'name.string'          => 'فیلد نام مجموعه نامعتبر است',
            'name.unique'          => 'نام مجموعه قبلاً ثبت شده است',
            'independent.required' => 'نوع مجموعه را انتخاب کنید',
            'independent.in'       => 'نوع مجموعه نامعتبر است',
            'parent_id.required'   => 'وابستگی مجموعه را مشخص کنید',
            'parent_id.string'     => 'وابستگی مجموعه نامعتبر است',
            'equal_param_auth.numeric' => 'مقدار معادل در سیستم احراز هویت خارجی باید عدد باشد',
            'equal_param_auth.max'     => 'مقدار معادل در سیستم احراز هویت خارجی بیش از حد مجاز است',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $collection = new Collection();
        $collection->name        = $request->name;
        $collection->independent = $request->independent;
        $collection->parent_id   = $request->parent_id == "" ? null : $request->parent_id;
        $collection->equal_param_auth  = $request->equal_param_auth;
        $collection->save();
        return redirect()->back()->with('successMsg', 'مجموعه جدید ذخیره شد.');
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
}
