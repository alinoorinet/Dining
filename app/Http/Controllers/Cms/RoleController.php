<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\Module;
use App\ModuleAction;
use App\ModuleActionRole;
use App\Role;
use App\Facades\Filtering;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Rbac::check_access('roles', 'index')) {
            $roles = Role::all();
            return view('cms.role.index', compact('roles'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function add()
    {
        if (Rbac::check_access('roles', 'add')) {
            return view('cms.role.add');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if (Rbac::check_access('roles', 'store')) {
            $filteredData = Filtering::filter($request->all(), ['_token',], ['xss', 'badCh']);
            $validator = Validator::make($filteredData, [
                'title' => array('required', 'string', 'regex:/^[\pL\s]+$/u'),
                'description' => 'required|string|max:255',
                'locked' => 'required|numeric|digits:1',
            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator);
            $role = new Role();
            $role->title = $filteredData['title'];
            $role->description = $filteredData['description'];
            $role->locked = $filteredData['locked'];
            $role->status = 'تأیید شده';
            if ($role->save())
                return redirect()->back()->with('successMsg', 'گروه کاربری جدید ذخیره شد.');
            return redirect()->back()->with('warningMsg', 'ذخیره گروه کاربری جدید ناموفق شد.');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function roles_actions()
    {
        if (Rbac::check_access('roles', 'roles_actions')) {
            $modules = Module::all();
            $roles = Role::all();
            return view('cms.roles-actions.index', [
                'modules' => $modules,
                'roles' => $roles,
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function get_actions(Request $request)
    {
        if (Rbac::check_access('roles', 'get_actions')) {
            $module = Module::find($request->json()->get('module_id'));
            $role = Role::find($request->json()->get('role_id'));
            if (!$role || !$module)
                return response()->json(['status' => false, 'res' => 'نقش یا ماژول نامعتبر است']);
            $actions = $module->action;
            $tmp = [];
            foreach ($actions as $action) {
                $actionRoles = $role->roleAction()->where('action_id', $action->id)->get();
                if (isset($actionRoles[0]->id)) {
                    foreach ($actionRoles as $actionRole)
                        $tmp[] = [
                            'id' => $actionRole->id,
                            'title' => $actionRole->title,
                            'checked' => 'checked',
                        ];
                } else
                    $tmp[] = [
                        'id' => $action->id,
                        'title' => $action->title,
                        'checked' => '',
                    ];
            }
            if ($actions)
                return response()->json(['status' => true, 'res' => $tmp]);
            return response()->json(['status' => false]);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست!!!']);
    }

    public function set_actions(Request $request)
    {
        if (Rbac::check_access('roles', 'set_actions')) {
            $module = Module::find($request->json()->get('module_id'));
            $role = Role::find($request->json()->get('role_id'));
            $myAction = ModuleAction::find($request->json()->get('action_id'));
            $checked = $request->json()->get('checked');
            if (!$role || !$module || !$myAction)
                return response()->json(['status' => false, 'res' => 'نقش یا ماژول یا اکشن نامعتبر است']);

            $actions = $module->action;
            foreach ($actions as $action) {
                $actionRoles = $role->roleAction()->where('action_id', $action->id)->get();
                if (isset($actionRoles[0]->id)) {
                    foreach ($actionRoles as $actionRole) {
                        if (($actionRole->id == $myAction->id) && !$checked) {
                            ModuleActionRole::destroy($actionRole->pivot->id);
                            return response()->json(['status' => true, 'res' => 'حذف گردید']);
                        }
                    }
                } else
                    if ($checked) {
                        ModuleActionRole::create([
                            'role_id' => $role->id,
                            'action_id' => $myAction->id,
                        ]);
                        return response()->json(['status' => true, 'res' => 'تخصیص داده شد']);
                    }
            }
            return response()->json(['status' => false, 'res' => 'انجام عمل خواسته شده ناموفق بود']);
        }
        return response()->json(['status' => false, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست!!!']);
    }
}
