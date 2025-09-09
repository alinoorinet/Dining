<?php

namespace App\Http\Controllers\Cms;

use App\Event;
use App\EventUserGroup;
use App\Facades\Rbac;
use App\UserGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        $userGroups = UserGroup::where('parent_id',null)->get();
        //if(admin)
            $events = Event::orderBy('id','desc')->paginate(10);
        //else
            $events = Event::where('organizer_id',Auth::user()->id)->orderBy('id','desc')->paginate(10);

        return view('cms.event.index', compact('events','userGroups'));
    }

    public function store(Request $request)
    {
        /*$ca = Rbac::check_access('goal', 'ذخیره هدف');
        if(!$ca)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');*/

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:191|unique:t_event,name',
            'guest_count'       => 'required|numeric',
            'from_date'         => 'required|date_format:Y-m-d',
            'to_date'           => 'required|date_format:Y-m-d',
            'organization'      => 'required|string|max:100',
            'guest_type'        => 'required|in:داخلی,خارجی,داخلی و خارجی',
            'max_user_reserve'  => 'required|numeric',
            'description'       => 'nullable|string',
            'user_group'        => 'required|array',
            'user_group.*'      => 'required|numeric|exists:user_group,id',
        ],[
            'name.required'             => 'نام را وارد کنید',
            'name.string'               => 'فیلد نام رویداد نامعتبر است',
            'name.max'                  => 'فیلد نام رویداد حداکثر 191 کاراکتر باشد',
            'name.unique'               => 'نام رویداد قبلاً ثبت شده است',
            'guest_count.required'      => 'تعداد مهمانان را وارد کنید',
            'guest_count.numeric'       => 'تعداد مهمانان باید به صورت عدد وارد شود',
            'from_date.required'        => 'تاریخ شروع برگزاری را وارد کنید',
            'from_date.date_format'     => 'تاریخ شروع برگزاری به فرمت 1xxx-xx-xx وارد شود',
            'to_date.required'          => 'تاریخ پایان برگزاری را وارد کنید',
            'to_date.date_format'       => 'تاریخ پایان برگزاری به فرمت 1xxx-xx-xx وارد شود',
            'organization.required'     => 'مجموعه برگزارکننده را وارد کنید',
            'organization.string'       => 'فیلد مجموعه برگزارکننده نامعتبر است',
            'organization.max'          => 'فیلد مجموعه برگزارکننده حداکثر 100 کاراکتر باشد',
            'guest_type.required'       => 'نوع مهمان را انتخاب کنید',
            'guest_type.in'             => 'نوع فیلد مهمان نامعتبر است',
            'max_user_reserve.required' => 'حداکثر تعداد رزرو را وارد کنید',
            'max_user_reserve.numeric'  => 'حداکثر تعداد رزرو باید به صورت عدد وارد شود',
            'user_group.required'       => 'حداقل یک گروه کاربری را انتخاب کنید',
            'user_group.array'          => 'فیلد گروه کاربری نامعتبر است',
            'user_group.*.required'     => 'حداقل یک گروه کاربری را انتخاب کنید',
            'user_group.*.numeric'      => 'گروه کاربری نامعتبر است',
            'user_group.*.exists'       => 'گروه کاربری نامعتبر است',
        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors());

        $e = new Event();
        $e->name             = $request->name;
        $e->organizer_id     = Auth::user()->id;
        $e->guest_count      = $request->guest_count;
        $e->from_date        = $request->from_date;
        $e->to_date          = $request->to_date;
        $e->organization     = $request->organization;
        $e->guest_type       = $request->guest_type;
        $e->max_user_reserve = $request->max_user_reserve;
        $e->description      = $request->description;
        $e->save();

        $userGroups = $request->has('user_group') ? $request->user_group : [];
        foreach ($userGroups as $userGroup) {
            $eu = new EventUserGroup();
            $eu->event_id       = $e->id;
            $eu->user_group_id  = $userGroup;
            $eu->max_reserve    = $request->get('max_reserve'.$userGroup);
            $eu->save();
        }
        return redirect()->back()->with('successMsg', 'رویداد جدید ذخیره و در انتظار بررسی کارشناسان امور تغذیه قرار گرفت.');
    }

    public function de_active($id)
    {
        /*$ca = Rbac::check_access('goal', 'غیرفعال کردن هدف');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');*/

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_event,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات رویداد نامعتبر است');

        $event = Event::find($id);
        $event->active = !$event->active;
        $event->update();
        return redirect()->back()->with("successMsg", 'فرآیند تغییر وضعیت انجام شد');
    }

    public function confirm($id)
    {
        /*$ca = Rbac::check_access('goal', 'غیرفعال کردن هدف');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');*/

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_event,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات رویداد نامعتبر است');

        $event = Event::find($id);
        $event->confirmed = !$event->confirmed;
        $event->update();
        return redirect()->back()->with("successMsg", 'فرآیند تغییر تایید انجام شد');
    }

    public function details($id)
    {
        /*$ca = Rbac::check_access('goal', 'غیرفعال کردن هدف');
        if($ca === false)
            return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');*/

        $v = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:t_event,id',
        ]);
        if ($v->fails())
            return redirect()->back()->with("dangerMsg", 'مشخصات رویداد نامعتبر است');

        $event = Event::find($id);
        return view('cms.event.details',compact('event'));
    }
}
