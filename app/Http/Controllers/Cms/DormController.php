<?php

namespace App\Http\Controllers\Cms;

use App\DormException;
use App\Facades\Rbac;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DormController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function dorm_exception_index()
    {
        if(Rbac::check_access('dorm','dorm_exception_index')) {
            $dExceptions = DormException::paginate(100);
            return view('cms.dorm.dorm-exception', compact('dExceptions'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function dorm_exception_store(Request $request)
    {
        if(Rbac::check_access('dorm','dorm_exception_store')) {
            Validator::make($request->all(), [
                'stdOrUid' => 'required|string',
            ], [
                'stdOrUid.required' => 'شناسه کاربری/شماره دانشجویی را وارد کنید',
                'stdOrUid.string'   => 'شناسه کاربری/شماره دانشجویی نامعتبر است',
            ])->validate();
            $credential = $request->stdOrUid;
            $user       = User::where('username', $credential)->first();
            if (!$user) {
                $user = User::where('std_no', $credential)->first();
                if (!$user)
                    return redirect()->back()->withInput()->withErrors(['stdOrUid' => ['مشخصات دانشجو پیدا نشد']]);
            }
            $de = DormException::where('user_id', $user->id)->first();
            if (isset($de->id))
                return redirect()->back()->withInput()->withErrors(['stdOrUid' => ['این مورد قبلا ثبت گردیده است']]);
            DormException::create([
                'user_id'    => $user->id,
                'creator_id' => Auth::user()->id,
            ]);
            return redirect('/home/dorm/dorm-exception')->with('successMsg', 'رفع محدودیت انجام شد');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function dorm_exception_delete($id)
    {
        if(Rbac::check_access('dorm','dorm_exception_delete')) {
            $v = Validator::make(['id' => $id], [
                'id' => 'required|numeric|digits_between:1,11|exists:dorm_exception,id',
            ]);
            if ($v->fails())
                return redirect()->back()->with('warningMsg', 'مشخصات ارسالی نامعتبر است');
            $luhe = DormException::find($id);
            $luhe->delete();
            return redirect('/home/dorm/dorm-exception')->with('successMsg', 'محدودیت حذف شد');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
