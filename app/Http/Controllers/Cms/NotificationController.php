<?php
/**
 * Created by PhpStorm.
 * User: farshad
 * Date: 11/20/2019
 * Time: 1:32 PM
 */

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\Http\Controllers\Controller;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function index()
    {
        if(Rbac::check_access('notification','index')) {

            $notifications = Notification::where('broadcast',1)->get();

            return view('cms.notification.index',[
                'notifications' => $notifications
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function add()
    {
        if(Rbac::check_access('notification','index')) {

            return view('cms.notification.add');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('notification','store')) {

            $validator = Validator::make($request->all(), [
                'title'         => 'required',
                'self'          => 'required',
                'contents'      => 'required',
            ],[
                'title.required'    => 'عنوان را وارد نمایید.',
                'self.required'     => 'محل درج را انتخاب نمایید.',
                'contents.required' => 'متن را وارد نمایید.',

            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator->errors());

            $notification = new Notification();
            $notification->broadcast    = 1;
            $notification->title        = $request->title;
            $notification->self         = $request->self;
            $notification->content      = $request->contents;
            $notification->user_id      = Auth::user()->id;
            $notification->active       = 1;

            if ($notification->save())
                return redirect('/home/notification')->with('successMsg', 'اطلاعیه ثبت شد');
            return redirect()->back()->with('warningMsg', 'اطلاعیه ثبت نشد.');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function edit($notifyId)
    {
        if(Rbac::check_access('notification','update')) {
            $v = Validator::make(['id' => $notifyId],[
                'id' => 'required|numeric|exists:notification'
            ]);
            if($v->fails())
                return redirect()->back()->with('dangerMsg','مشخصات نامعتبر است');

            $notify_temp = Notification::find($notifyId);

            return view('cms.notification.edit', [
                'notification' => $notify_temp,
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function update(Request $request)
    {
        if(Rbac::check_access('notification','update')) {

            $validator = Validator::make($request->all(), [
                'title'     => 'required',
                'self'      => 'required',
                'contents'   => 'required',
            ],[
                'title.required'    => 'عنوان را وارد نمایید.',
                'self.required'     => 'محل درج را انتخاب نمایید.',
                'contents.required'  => 'متن را وارد نمایید.',

            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator->errors());

            $notification = Notification::find($request->notificationId);
            if (!$notification)
                return redirect()->back()->with('dangerMsg', 'مشخصات نامعتبر است.');
            $notification->broadcast    = 1;
            $notification->title    = $request->title;
            $notification->user_id  = Auth::user()->id;
            $notification->content  = $request->contents;
            $notification->self     = $request->self;

            $notification->update();
            return redirect('/home/notification')->with('successMsg', 'اطلاعیه بروزرسانی گردید.');
        }

        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function allow_this(Request $request)
    {
        if(!Rbac::check_access('notification','delete'))
            return response([
                'status'=> 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست',
            ]);

        $v = Validator::make($request->all(),[
            'nId' => 'required|numeric|exists:notification,id'
        ]);
        if($v->fails())
            return response([
                'status'=> 101,
                'res' => 'مشخصات نامعتبر است',
            ]);

        $notification = Notification::find($request->nId);
        $notification->active = 1;
        $notification->update();
        return response([
            'status'=> 200,
        ]);

    }

    public function disallow_this(Request $request)
    {
        if(!Rbac::check_access('notification','delete'))
            return response([
                'status'=> 300, 'res' => 'دسترسی شما به این بخش امکان پذیر نیست',
            ]);

        $v = Validator::make($request->all(),[
            'nId' => 'required|numeric|exists:notification,id'
        ]);
        if($v->fails())
            return response([
                'status' => 101,
                'res'    => 'مشخصات نامعتبر است',
            ]);

        $notification = Notification::find($request->nId);
        $notification->active = 0;
        $notification->update();

        return response([
            'status'=> 200,
        ]);
    }

    public function delete($id)
    {
        if(!Rbac::check_access('notification','delete'))
            return redirect('/')->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نیست');

        $v = Validator::make(['id' => $id],[
            'id' => 'required|numeric|exists:notification,id'
        ]);
        if($v->fails())
            return redirect()->back()->with('warningMsg', 'مشخصات نامعتبر است.');

        Notification::destroy($id);
        return redirect()->back()->with('successMsg', 'اطلاعیه حذف گردید.');
    }
}
