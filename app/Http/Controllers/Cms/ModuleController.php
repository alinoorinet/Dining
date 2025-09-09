<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Activity;
use App\Facades\Rbac;
use App\Module;
use App\Facades\Filtering;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(Rbac::check_access('modules','index')) {
            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'modules',
                'description' => 'کاربر وارد لیست ماژول ها می شود',
                'user_id' => Auth::user()->id,
            ]);

            if (Rbac::check_access('modules', 'index')) {
                $modules = Module::all();
                return view('cms.module.index', compact('modules'));
            }
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function add()
    {
        if(Rbac::check_access('modules','index')) {
            return view('cms.module.add');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('modules','index')) {
            $validator = Validator::make($request->all(), [
                //'title' => array('required', 'string', 'regex:/^[\pL\s]+$/u'),
                'title' => array('required', 'string',),
                'description' => 'required|string|max:255',
            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator);
            $module = new Module();
            $module->title = $request->title;
            $module->description = $request->description;
            if ($module->save())
                return redirect()->back()->with('successMsg', 'ماژول جدید ذخیره شد.');
            return redirect()->back()->with('warningMsg', 'ذخیره ماژول جدید ناموفق شد.');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
