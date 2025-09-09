<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\UserGroup;
use App\UserGroupUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        $groups = UserGroup::all();
        $count = count($groups);
        return view('cms.user-group.index', compact('groups','count'));
    }

    public function store(Request $request)
    {
        /*$ca = Rbac::check_access('goal', 'ذخیره هدف');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');*/

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|unique:user_group,title',
            'kindid'    => 'nullable|numeric',
            'max_reserve_simultaneous' => 'required|numeric',
            'max_discount'             => 'required|numeric',
            'parent_id' => 'nullable|numeric|exists:user_group,id',
        ],[
            'title.required'    => 'نام گروه کاربری را وارد کنید',
            'title.string'      => 'فیلد نام گروه نامعتبر است',
            'title.unique'      => 'نام گروه قبلاً ثبت شده است',
            'kindid.numeric'    => 'شناسه kind نامعتبر است',
            'parent_id.numeric' => 'زیرمجموعه گروه کاربری نامعتبر است',
            'parent_id.exists'  => 'زیرمجموعه گروه کاربری نامعتبر است',
            'max_reserve_simultaneous.required' => 'تعداد رزرو همزمان را وارد کنید',
            'max_reserve_simultaneous.numeric'  => 'تعداد رزرو همزمان باید عدد باشد',
            'max_discount.required' => 'تعداد غذای قابل تخفیف را وارد کنید',
            'max_discount.numeric'  => 'تعداد غذای قابل تخفیف باید عدد باشد',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $ug = new UserGroup();
        $ug->title     = $request->title;
        $ug->kindid    = $request->kindid;
        $ug->max_reserve_simultaneous = $request->max_reserve_simultaneous;
        $ug->max_discount             = $request->max_discount;
        $ug->parent_id = $request->parent_id == "" ? null : $request->parent_id;
        $ug->save();
        return redirect()->back()->with('successMsg', 'گروه کاربری جدید ذخیره شد.');
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

    public function delete_user_group_users($id)
    {
        if (Rbac::check_access('users', 'index')) {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|numeric|exists:user_group_users'
            ]);
            if ($validator->fails())
                return redirect()->back()->with('dangerMsg', 'اطلاعات ورودی نامعتبر است');
            UserGroupUsers::destroy($id);
            return redirect()->back()->with('successMsg', 'اطلاعات گروه کاربری حذف گردید');
        }
        return redirect()->back()->with('dangerMsg',__('Your access to this section is not possible'));
    }

    public function add_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential'    => 'required|string',
            'user_groups'   => 'required|array',
            'user_groups.*' => 'required|numeric|exists:user_group,id',
        ],[
            'credential.required' => 'مشخصات کاربر را وارد کنید',
            'credential.string'   => 'فیلد نامعتبر است',
        ]);
        if ($validator->fails())
            return redirect()->back()->withErrors($validator->errors())->withInput();

        $credential = $request->credential;
        $user = DB::table('users')
            ->where('username',        $credential)
            ->orWhere('std_no',        $credential)
            ->orWhere('national_code', $credential)
            ->first();

        if(!isset($user->id))
            return redirect()->back()->withErrors(['credential' =>['مشخصات کاربر پیدا نشد']]);

        $userGroups = $request->user_groups;
        foreach ($userGroups as $userGroup) {
            $priority = $request->get("priority_".$userGroup);
            if (!is_numeric($priority))
                return redirect()->back()->withErrors(['credential' =>['اولویت نامعتبر است']]);
            $checkUGU = UserGroupUsers::where('user_group_id',$userGroup)
                ->where('user_id',$user->id)
                ->first();
            if(!isset($checkUGU->id))
                UserGroupUsers::create([
                    'user_group_id' => $userGroup,
                    'user_id'       => $user->id,
                    'priority'      => $priority,
                    'created_at'    => date('Y-m-d H-i-s'),
                ]);
            else {
                $checkUGU->priority = $priority;
                $checkUGU->save();
            }
        }
        return redirect()->back()->with('successMsg','تخصیص کاربر به گروه های کاربری انجام شد');
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential' => 'required|string',
        ],[
            'credential.required' => 'مشخصات کاربر را وارد کنید',
            'credential.string'   => 'فیلد نامعتبر است',
        ]);
        if ($validator->fails())
            return response()->json(['status' => 101,'res' => $validator->errors()]);

        $credential = $request->credential;
        $user = DB::table('users')
            ->where('username',        $credential)
            ->orWhere('std_no',        $credential)
            ->orWhere('national_code', $credential)
            ->first();

        if(!isset($user->id))
            return response()->json(['status' => 101, 'res' => ['credential' =>['مشخصات کاربر پیدا نشد']]]);

        $groups = UserGroupUsers::where('user_id',$user->id)->orderBy('priority','desc')->get();
        if(isset($groups[0]->id)) {
            $res = "<div class='table-responsive'>
                        <table class='table table-bordered table-striped table-sm'>
                            <thead>
                            <tr>
                                <th class='text-center'>#</th>
                                <th class='text-right'>گروه کاربری</th>
                                <th class='text-center'>اولویت</th>
                                <th class='text-center'></th>
                            </tr>
                            </thead>
                            <tbody>";
            foreach ($groups as $i=>$group) {
                $i++;
                $priority = $group->priority;
                $userGroup = $group->user_group;
                $res .= "<tr>
                            <td class='text-center'>$i</td>
                            <td class='text-right'>$userGroup->title</td>
                            <td class='text-center'>$priority</td>
                            <td class='text-center'><a href='/home/user-group/user-group-users/delete/$group->id' class='btn btn-light btn-sm'><i class='fa fa-trash text-danger'></i></a></td>
                        </tr>";
            }
            $res .= "</tbody>
            </table></div>";
            return response()->json(['status' => 200, 'res' => $res]);
        }
        $res = "<p>هیچ گروه کاربری برای کاربر ثبت نشده است</p>";
        return response()->json(['status' => 200, 'res' => $res]);
    }
}
