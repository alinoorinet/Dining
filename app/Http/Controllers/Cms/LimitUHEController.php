<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\LimitUHE;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LimitUHEController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        if(Rbac::check_access('luhe','index')) {
            $luhes = LimitUHE::orderBy('term', 'desc')->paginate(50);
            return view('cms.limitUHE.index', compact('luhes'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('luhe','store')) {
            Validator::make($request->all(), [
                'stdOrUid' => 'required|string',
                'term' => 'required|numeric|digits_between:3,3',
            ], [
                'stdOrUid.required' => 'شناسه کاربری/شماره دانشجویی را وارد کنید',
                'stdOrUid.string' => 'شناسه کاربری/شماره دانشجویی نامعتبر است',
                'term.required' => 'ترم جاری را وارد کنید',
                'term.numeric' => 'ترم جاری را به صورت رقم وارد کنید',
                'term.digits_between' => 'ترم جاری 3 رقم باشد',
            ])->validate();
            $credential = $request->stdOrUid;
            $term = $request->term;
            $user = User::where('username', $credential)->first();
            if (!$user) {
                $user = User::where('std_no', $credential)->first();
                if (!$user)
                    return redirect()->back()->withInput()->withErrors(['stdOrUid' => 'مشخصات دانشجو پیدا نشد']);
            }
            $luhe = LimitUHE::where('user_id', $user->id)->where('term', $term)->first();
            if ($luhe)
                return redirect()->back()->withInput()->withErrors(['stdOrUid' => 'این مورد قبلا ثبت گردیده است']);
            LimitUHE::create([
                'user_id' => $user->id,
                'term' => $term,
                'active' => 1,
            ]);
            return redirect('/home/luhe')->with('successMsg', 'رفع محدودیت انجام شد');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function de_active($id)
    {
        if(Rbac::check_access('luhe','de_active')) {
            $v = Validator::make(['id' => $id], [
                'id' => 'required|numeric|digits_between:1,11|exists:limit_unpaied_housing_exception,id',
            ]);
            if ($v->fails())
                return redirect()->back()->with('warningMsg', 'مشخصات ارسالی نامعتبر است');
            $luhe = LimitUHE::find($id);
            if ($luhe->active == 0)
                $luhe->active = 1;
            else
                $luhe->active = 0;
            $luhe->update();
            return redirect('/home/luhe')->with('successMsg', 'محدودیت بروزرسانی شد');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function delete($id)
    {
        if(Rbac::check_access('luhe','delete')) {
            $v = Validator::make(['id' => $id], [
                'id' => 'required|numeric|digits_between:1,11|exists:limit_unpaied_housing_exception,id',
            ]);
            if ($v->fails())
                return redirect()->back()->with('warningMsg', 'مشخصات ارسالی نامعتبر است');
            $luhe = LimitUHE::find($id);
            $luhe->delete();
            return redirect('/home/luhe')->with('successMsg', 'محدودیت حذف شد');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
