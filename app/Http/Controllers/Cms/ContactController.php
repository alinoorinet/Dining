<?php
/**
 * Created by PhpStorm.
 * User: farshad
 * Date: 2/5/2018
 * Time: 9:10 AM
 */
namespace app\Http\Controllers\Cms;

use App\ContactUs;
use App\Facades\Activity;
use App\Facades\Filtering;
use App\Facades\Rbac;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        if(Rbac::check_access('contact-us','index')) {
            $cu = ContactUs::where('user_id', Auth::user()->id)->get();
            $cuTmp = [];
            foreach ($cu as $value) {
                $cuTmp[] = (object)[
                    'id' => $value->id,
                    'readed' => $value->readed,
                    'request' => $value->request,
                    'response' => $value->response,
                    'receiver' => ($value->receiver == 1) ? 'مدیران' : 'طراحان',
                ];
            }
            return view('cms.contact-us.index', [
                'cu' => $cuTmp,
            ]);
        }
        return redirect()->back()->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function readed(Request $request)
    {
        if(Rbac::check_access('contact-us','readed')) {
            $id = trim($request->json()->get('resp'));
            $validator = Validator::make(['id' => $id,], [
                'id' => 'required|numeric|exists:contact_us,id',
            ]);
            if (!$validator)
                return response()->json(['status' => false]);
            $cu = ContactUs::where('id', $id)->where('user_id', Auth::user()->id)->first();
            if (!$cu)
                return response()->json(['status' => false]);
            session()->forget('myNewMessage');
            $cu->readed = 1;
            $cu->update();
            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false]);
    }

    public function delete($id)
    {
        if(Rbac::check_access('contact-us','delete')) {
            $id = trim($id);
            $validator = Validator::make(['id' => $id,], [
                'id' => 'required|numeric|exists:contact_us,id',
            ]);
            if (!$validator)
                return redirect()->back()->with('dangerMsg', 'اطلاعات ورودی نامعتبر است');
            $cu = ContactUs::where('id', $id)->where('user_id', Auth::user()->id)->first();
            if (!$cu)
                return redirect()->back()->with('dangerMsg', 'اطلاعات ورودی نامعتبر است');
            $cu->delete();
            return redirect()->back()->with('successMsg', 'پیام شما حذف گردید');
        }
        return redirect()->back()->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if(Rbac::check_access('contact-us','store')) {
            //$filteredData = Filtering::filter($request->all(), ['_token',], ['xss', 'badCh']);
            Validator::make($request->all(), [
                'message' => 'required|string',
                'receiver' => 'required|numeric|in:0,1',
            ], [
                'receiver.numeric' => 'مخاطب پیام نامعتبر است',
                'receiver.in' => 'مخاطب پیام نامعتبر است',
            ])->validate();

            $cu = new ContactUs();
            $cu->request = $request->message;
            $cu->receiver = $request->receiver;
            $cu->user_id = Auth::user()->id;
            try {
                if (!$cu->save())
                    throw new \Exception;
                return redirect()->back()->with('successMsg', 'پیام شما در سیستم ثبت و در صورت نیاز،  پاسخ آن در همین سامانه به شما اطلاع داده خواهد شد');
            } catch (\Exception $exception) {
                return redirect()->back()->with('dangerMsg', 'پیام شما در سیستم ثبت نشد.این مورد را می توانید به اطلاع طراح نرم افزار برسانید. 1060-550-0918');
            }
        }
        return redirect()->back()->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store_from_out(Request $request)
    {
        //$filteredData = Filtering::filter($request->json()->all(), ['_token',], ['xss', 'badCh']);
        $validator = Validator::make($request->json()->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'mobile' => 'required|digits:11',
            'std_no' => 'required|numeric',
            'message' => 'required|string',
            'receiver' => 'required|numeric|in:0,1',
        ], [
            'name.required' => 'نام و نام خانوادگی را وارد کنید',
            'name.string' => 'نام نامعتبر است',
            'email.required' => 'ایمیل را وارد کنید',
            'email.email' => 'ایمیل نامعتبر است',
            'mobile.required' => 'شماره همراه را وارد کنید',
            'mobile.digits' => 'شماره همراه باید 11 رقم باشد',
            'std_no.required' => 'شماره دانشجویی را وارد کنید',
            'std_no.numeric' => 'شماره دانشجویی باید عدد باشد',
            'receiver.numeric' => 'مخاطب پیام نامعتبر است',
            'receiver.in' => 'مخاطب پیام نامعتبر است',
        ]);
        if ($validator->fails())
            return response()->json(['status' => 101, 'errors' => $validator->errors()]);

        $cu = new ContactUs();
        $cu->name = $request->json()->get('name');
        $cu->request = $request->json()->get('message');
        $cu->email = $request->json()->get('email');
        $cu->std_no = $request->json()->get('std_no');
        $cu->mobile = $request->json()->get('mobile');
        $cu->receiver = $request->json()->get('receiver');
        try {
            if (!$cu->save())
                throw new \Exception;
            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'contact-us',
                'description' => 'ارسال پیام خارج از سامانه',
                'user_id' => null,
            ]);
            return response()->json(['status' => 200, 'res' => 'پیام شما ارسال و در صورت نیاز، پاسخ آن به ایمیل شما ارسال می گردد']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 102, 'res' => 'پیام شما در سیستم ثبت نشد.این مورد را می توانید به اطلاع طراح نرم افزار برسانید. 1060-550-0918']);
        }
    }

    public function show()
    {
        if(Rbac::check_access('contact-us','show')) {
            if (Rbac::get_access('developer')) {
                $cu = ContactUs::all();
                $cuTmp = [];
                foreach ($cu as $value) {
                    if ($value->user_id != null) {
                        $user = $value->user;
                        $cuTmp[] = (object)[
                            'id' => $value->id,
                            'answered' => $value->answered,
                            'name' => $user->name,
                            'email' => $user->email,
                            'std_no' => $user->std_no,
                            'username' => $user->username,
                            'mobile' => $user->mobile,
                            'request' => $value->request,
                            'receiver' => ($value->receiver == 1) ? 'مدیران' : 'طراحان',
                            'created_at' => $value->GetCreateDate(),
                            'inOrOut' => 'داخلی',
                        ];
                    } else {
                        $cuTmp[] = (object)[
                            'id' => $value->id,
                            'answered' => $value->answered,
                            'name' => $value->name,
                            'email' => $value->email,
                            'std_no' => $value->std_no,
                            'username' => '',
                            'mobile' => $value->mobile,
                            'request' => $value->request,
                            'receiver' => ($value->receiver == 1) ? 'مدیران' : 'طراحان',
                            'created_at' => $value->GetCreateDate(),
                            'inOrOut' => 'خارجی',
                        ];
                    }
                }
                return view('cms.contact-us.show', [
                    'cu' => $cuTmp,
                ]);
            }
            elseif (Rbac::get_access('admin')) {
                $cu = ContactUs::where('receiver', 1)->get();
                $cuTmp = [];
                foreach ($cu as $value) {
                    if ($value->user_id != null) {
                        $user = $value->user;
                        $cuTmp[] = (object)[
                            'id' => $value->id,
                            'answered' => $value->answered,
                            'name' => $user->name,
                            'email' => $user->email,
                            'std_no' => $user->std_no,
                            'username' => $user->username,
                            'mobile' => $user->mobile,
                            'request' => $value->request,
                            'receiver' => ($value->receiver == 1) ? 'مدیران' : 'طراحان',
                            'created_at' => $value->GetCreateDate(),
                            'inOrOut' => 'داخلی',
                        ];
                    } else {
                        $cuTmp[] = (object)[
                            'id' => $value->id,
                            'answered' => $value->answered,
                            'name' => $value->name,
                            'email' => $value->email,
                            'std_no' => $value->std_no,
                            'username' => '',
                            'mobile' => $value->mobile,
                            'request' => $value->request,
                            'receiver' => ($value->receiver == 1) ? 'مدیران' : 'طراحان',
                            'created_at' => $value->GetCreateDate(),
                            'inOrOut' => 'خارجی',
                        ];
                    }
                }
                return view('cms.contact-us.show', [
                    'cu' => $cuTmp,
                ]);
            }
            return redirect()->back()->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نمی باشد');
        }
        return redirect()->back()->with('dangerMsg', 'دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function response(Request $request)
    {
        if(Rbac::check_access('contact-us','response')) {
            $cuId = $request->json()->get('id');
            $response = $request->json()->get('message');
            $validator = Validator::make([
                'message' => $request->json()->get('message'),
            ], [
                'message' => 'required|string',
            ]);
            if ($validator->fails())
                return response()->json(['status' => 101, 'errors' => $validator->errors()]);
            $cu = ContactUs::find($cuId);
            if (!$cu)
                return response()->json(['status' => 102, 'res' => 'اطلاعات ورودی نامعتبر است']);
            $cu->answered = 1;
            $cu->response = $response;
            $cu->update();
            return response()->json(['status' => 200, 'res' => 'پاسخ ارسال شد']);
        }
        return response()->json(['status' => 102, 'res' => 'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }
}