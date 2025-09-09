<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Activity;
use App\Facades\Rbac;
use App\FreeRTE;
use App\FreeSetting;
use App\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        if(Rbac::check_access('setting','index')) {
            $setting = Setting::first();
            return view('cms.setting.index', compact('setting'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نیست');
    }

    public function update(Request $request)
    {
        if(Rbac::check_access('setting','update')) {
            Validator::make($request->all(), [
                'day_type_cd' => 'required|string|max:8|in:عادی,امتحانات,رمضان,سایر',
            ])->validate();
            $setting = Setting::first();
            $setting->day_type_cd = $request->day_type_cd;
            $setting->update();

            Activity::create([
                'ip_address'  => \Request::ip(),
                'user_agent'  => \Request::header('user-agent'),
                'task'        => 'update_setting',
                'description' => "بروزرسانی تنظیمات",
                'user_id'     => Auth::user()->id,
            ]);
            return redirect()->back()->with('successMsg', 'بروزرسانی انجام شد');

        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نیست');
    }
}
