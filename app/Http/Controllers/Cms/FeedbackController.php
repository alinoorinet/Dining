<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 25/11/2019
 * Time: 02:40 PM
 */

namespace App\Http\Controllers\Cms;


use App\Facades\Activity;
use App\Facades\Rbac;
use App\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        if(Rbac::check_access('contact-us','index')) {

            $unreads = Feedback::where('seen', 0)->orderBy('id','desc')->paginate(10);
            $reads = Feedback::where('seen', 1)->where('active', 1)->orderBy('id','desc')->paginate(10);

            return view('cms.feedback.index',[
                'unreads'   => $unreads,
                'reads'     => $reads
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contents'          => 'required|string|max:300',
            'type'              => 'required|in:کیفیت غذا,مقدار غذا,برنامه غذایی,تحویل غذا,امور تغذیه,وب سایت',
        ],[
            'contents.required' => 'متن را وارد نمایید.',
            'contents.string'   => 'فرمت متن اشتباه می باشد.',
            'contents.max'      => 'متن می بایست حداکثر 300 کاراکتر باشد.',
            'type.required'     => 'یک مورد انتخاب شود.',
            'type.in'           => 'مقدار ورودی جزء موارد مجاز نمی باشد.',

        ]);
        if ($validator->fails())
            return redirect()->back()->withInput()->withErrors($validator->errors())->with('show_collapse_feedback','show');

        $isPublic = 0;
        if ($request->has('private'))
            $isPublic = 1;

        $feedback = new Feedback();
        $feedback->content  = $request->contents;
        $feedback->private  = $isPublic;
        $feedback->type     = $request->type;
        $feedback->user_id  = Auth::user()->id;

        if ($feedback->save())
            return redirect()->back()->with('successMsg','انتقاد/پیشنهاد ثبت شد.');

        return redirect()->back()->with('dangerMsg','ثبت انتقاد/پیشنهاد با خطا مواجه شد.');
    }

    public function checked(Request $request)
    {

        $feedback = Feedback::find($request->fId);
        $feedback->seen  = 1;

        if ($feedback->update()){
            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'seen-feedback',
                'description' => 'مشاهده انتقاد پیشنهاد',
                'user_id' => Auth::user()->id,
                'ids' => $request->fId ,
            ]);

            return response()->json([
                'status'    => 200,
            ]);
        }

        return response()->json([
            'status'    => 300,
            'res'       => 'انجام نشد.'
        ]);

    }

    public function delete(Request $request)
    {

        $feedback = Feedback::find($request->fId);
        $feedback->active  = 0;

        if ($feedback->update()){
            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'delete-feedback',
                'description' => 'حذف انتقاد پیشنهاد',
                'user_id' => Auth::user()->id,
                'ids' => $request->fId ,
            ]);

            return response()->json([
                'status'    => 200,
            ]);
        }

        return response()->json([
            'status'    => 300,
            'res'       => 'انجام نشد.'
        ]);

    }

}
