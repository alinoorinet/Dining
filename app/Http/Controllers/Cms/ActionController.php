<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\Module;
use App\ModuleAction;
use App\Library\jdf;
use App\Facades\Filtering;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ActionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        if(Rbac::check_access('actions','index')) {
            $actions = ModuleAction::orderBy('module_id')->get();
            $jdate = new jdf();
            $tmp = [];
            foreach ($actions as $action) {
                $module = $action->module;
                $tmp[] = (object)[
                    'action_id'    => $action->id,
                    'action_title' => $action->title,
                    'action_desc'  => $action->description,
                    'created_at'   => $jdate->getPersianDate($action->created_at, 'Y-m-d H:i:s'),
                    'module_title' => isset($module->title)?$module->title:'تعریف نشده',
                    'module_desc'  => isset($module->description)?$module->description:'تعریف نشده',
                ];
            }
            return view('cms.action.index', ['actions' => $tmp]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function add()
    {
        if(Rbac::check_access('actions','index')) {
            $modules = Module::all();
            return view('cms.action.add', compact('modules'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('actions','index')) {
            $validator = Validator::make($request->all(), [
                'title' => array('required', 'string'),
                'description' => 'required|string|max:255',
                'module_id' => 'required|numeric',
            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator);
            $action = new ModuleAction();
            $action->title = $request->title;
            $action->description = $request->description;
            $action->module_id = $request->module_id;
            if ($action->save())
                return redirect()->back()->with('successMsg', 'اکشن جدید ذخیره شد.');
            return redirect()->back()->with('warningMsg', 'ذخیره اکشن جدید ناموفق شد.');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function delete($id)
    {
        if(Rbac::check_access('actions','delete')) {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|numeric|exists:modules_actions'
            ]);
            if ($validator->fails())
                return redirect()->back()->with('dangerMsg', 'اطلاعات ورودی نامعتبر است');
            ModuleAction::destroy($id);
            return redirect()->back()->with('successMsg', 'اطلاعات اکشن حذف گردید');
        }
        return redirect()->back()->with('dangerMsg',__('Your access to this section is not possible'));
    }
}
