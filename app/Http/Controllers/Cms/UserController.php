<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Activity;
use App\Facades\Rbac;
use App\Library\jdf;
use App\Notification;
use App\Reservation;
use App\Role;
use App\UserRole;
use App\Facades\Filtering;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Constraint\Count;
use function GuzzleHttp\Promise\all;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Rbac::check_access('users', 'index')) {
            $users        = User::all();
            $usersCounter = 0;
            $usersTmp     = [];
            $jdf = new jdf();
            foreach ($users as $user) {
                $rolesCount = $user->userRole()->count();
                if ($rolesCount > 0) {
                    $rolesTmp = [];
                    foreach ($user->userRole as $role) {
                        $rolesTmp[] = $role->title;
                        if(strpos($role->title,'developer') == 0 && strpos($role->title,'admin') == 0)
                            $usersCounter++;
                    }
                    $usersTmp[] = (object)[
                        'id'            => $user->id,
                        'name'          => $user->name,
                        //'family'        => $user->family,
                        'username'      => $user->username,
                        'mobile'        => $user->mobile,
                        //'national_code' => $user->national_code,
                        'std_no'        => $user->std_no,
                        'email'         => $user->email,
                        'active'        => $user->active,
                        'last_login'    => $jdf->getPersianDate($user->last_login,'Y-m-d H:i:s'),
                        'roles'         => $rolesTmp
                    ];
                }
            }
            return view('cms.user.index', compact('usersTmp','usersCounter'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function repeatTran()
    {
        if (Rbac::check_access('users', 'index')) {
            $amount = 0;
            $i = $j = $x = 0;
            $created = [];
            echo '<pre>';
            $users = User::all();

            foreach ($users as $user){
                $wallets = Wallet::where('user_id', $user->id)
                    ->where('_for','افزایش اعتبار تغذیه')->get();

                $i = $i + 1;
                if (isset($wallets[0])){
                    $j = $j + 1;
                    foreach ($wallets as $wallet){
                        if (!$wallet->tran) {

                            $lastAmount = Wallet::where('user_id', $user->id)->orderBy('id','desc')->first();
                            $amount = $amount + $wallet->value;
                            echo $wallet->user->name." - ".$wallet->user_id.' - '.$amount.' => '.$lastAmount->amount;

//                        if ($lastAmount->_for == 'کسر مبلغ تکراری زمان اختلال درگاه'){
//                            $lastAmount->amount = $lastAmount->amount - $wallet->value;
//                            $lastAmount->value = $amount;
//                            $lastAmount->update();
//                        }
//                        else{
//                            $updateWallet = new Wallet();
//                            $updateWallet->amount = $lastAmount->amount - $wallet->value;
//                            $updateWallet->value = $amount;
//                            $updateWallet->_for = 'کسر مبلغ تکراری زمان اختلال درگاه';
//                            $updateWallet->operation = '0';
//                            $updateWallet->user_id = $user->id;
//                            $updateWallet->save();
//                        }
                            echo '<br>';
                        }


                    }
                }

                $amount = 0;

//            print_r($wallets);
            }
            echo $i.'======'.$j.'======'.$x;
            print_r($created);
            exit();
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

    }

    public function repeatPhone()
    {
        if (Rbac::check_access('users', 'index')) {
            $wallets = Wallet::where('_for','کسر مبلغ تکراری زمان اختلال درگاه')->get();
            foreach ($wallets as $k=>$wallet) {
                print_r($wallet->user->mobile);
                echo '<br>';
            }

            exit();
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');

    }

    public function doubleRes()
    {
        echo '<pre><table class="table table-striped"><tbody>';
//        print_r(1);
//        print_r(session('resReportBeginDate'));
//        exit();

        $reserves = Reservation::
        where('date','>=','1401-02-17')
            ->where('date','<=','1401-02-23')
            ->groupBy('user_id','meal','day')
            ->having(DB::raw('count(*)'),'>','1')
            ->get();

        $colors =['#777777','#ffffff'];
        $i = 1;
        foreach ($reserves as $k=>$reserve){
            $userReserves = Reservation::where('user_id', $reserve->user_id)
                ->where('date', $reserve->date)
                ->where('meal', $reserve->meal)
                ->where('day', $reserve->day)
                ->get();

            foreach ($userReserves as $userReserve){
                echo '<tr><td>'.$i.'</td><td style="background:'.$colors[$k%2].';color:'.$colors[1-$k%2].'">';
                echo $userReserve->user->name .' = '.$userReserve->food_title .' = '.$userReserve->eaten.' = '.$userReserve->user_id;
                echo "</td></tr>";
                $i++;
                $user = User::find($userReserve->user_id);
//                echo $user->notificationsC();
                $not = Notification::where('user_id',$userReserve->user_id)
                    ->where('content','like','%'.$userReserve->date.'%')
                    ->where('content','like','%'.$userReserve->meal.'%')
                    ->count();
                if ($not == 0) {
                    $backReserve00 = Reservation::where('user_id', $userReserve->user_id)
                        ->where('date', $userReserve->date)
                        ->where('meal', $userReserve->meal)
                        ->where('day', $userReserve->day)
                        ->where('eaten', 0)
                        ->get();
                    if ($backReserve00->count() == 2) {
                        $amount1 = $backReserve00[0]->pay_amount;
                        $amount2 = $backReserve00[1]->pay_amount;
                        if ($amount1 > $amount2) {
                            $wallet = Wallet::where('user_id', $backReserve00[0]->user_id)->orderBy('id', 'desc')->first();
                            $newWallet = new Wallet();
                            $newWallet->amount = $wallet->amount + $backReserve00[0]->pay_amount;
                            $newWallet->value = $backReserve00[0]->pay_amount;
                            $newWallet->_for = 'بازگشت 1 عدد ' . $backReserve00[0]->food_title;
                            $newWallet->operation = 1;
                            $newWallet->user_id = $backReserve00[0]->user_id;
                            if ($newWallet->save()) {
                                $notif = new Notification();
                                $notif->broadcast = 0;
                                $notif->title = 'برگشت مبلغ رزرو تاریخ ' . $backReserve00[0]->date;
                                $notif->content = ' مبلغ ' . $backReserve00[0]->pay_amount . ' مربوط به غذای ' . $backReserve00[0]->food_title . ' منوی دو غذایی در روز ' . $backReserve00[0]->day . '(' . $backReserve00[0]->date . ') ،وعده ' . $backReserve00[0]->meal . ' به حساب شما بازگشت داده شد.';
                                $notif->self = 0;
                                $notif->user_id = $backReserve00[0]->user_id;
                                $notif->save();
                                $backReserve00[0]->delete();
                            }
                        } else {
                            $wallet = Wallet::where('user_id', $backReserve00[1]->user_id)->orderBy('id', 'desc')->first();
                            $newWallet = new Wallet();
                            $newWallet->amount = $wallet->amount + $backReserve00[1]->pay_amount;
                            $newWallet->value = $backReserve00[1]->pay_amount;
                            $newWallet->_for = 'بازگشت 1 عدد ' . $backReserve00[1]->food_title;
                            $newWallet->operation = 1;
                            $newWallet->user_id = $backReserve00[1]->user_id;
                            if ($newWallet->save()) {
                                $notif = new Notification();
                                $notif->broadcast = 0;
                                $notif->title = 'برگشت مبلغ رزرو تاریخ ' . $backReserve00[1]->date;
                                $notif->content = ' مبلغ ' . $backReserve00[1]->pay_amount . ' مربوط به غذای ' . $backReserve00[1]->food_title . ' منوی دو غذایی در روز ' . $backReserve00[1]->day . '(' . $backReserve00[1]->date . ') ،وعده ' . $backReserve00[1]->meal . ' به حساب شما بازگشت داده شد.';
                                $notif->self = 0;
                                $notif->user_id = $backReserve00[1]->user_id;
                                $notif->save();
                                $backReserve00[1]->delete();
                            }
                        }
                    } else {
                        if ($userReserve->eaten == 1) {
                            $backReserve = Reservation::where('user_id', $reserve->user_id)
                                ->where('date', $reserve->date)
                                ->where('meal', $reserve->meal)
                                ->where('day', $reserve->day)
                                ->where('eaten', 0)
                                ->first();
                            if (isset($backReserve)) {
                                $wallet = Wallet::where('user_id', $backReserve->user_id)->orderBy('id', 'desc')->first();
                                $newWallet = new Wallet();
                                $newWallet->amount = $wallet->amount + $backReserve->pay_amount;
                                $newWallet->value = $backReserve->pay_amount;
                                $newWallet->_for = 'بازگشت 1 عدد ' . $backReserve->food_title;
                                $newWallet->operation = 1;
                                $newWallet->user_id = $backReserve->user_id;
                                if ($newWallet->save()) {
                                    $notif = new Notification();
                                    $notif->broadcast = 0;
                                    $notif->title = 'برگشت مبلغ رزرو تاریخ ' . $backReserve->date;
                                    $notif->content = ' مبلغ ' . $backReserve->pay_amount . ' مربوط به غذای ' . $backReserve->food_title . ' منوی دو غذایی در روز ' . $backReserve->day . '(' . $backReserve->date . ') ،وعده ' . $backReserve->meal . ' به حساب شما بازگشت داده شد.';
                                    $notif->self = 0;
                                    $notif->user_id = $backReserve->user_id;
                                    $notif->save();
                                    $backReserve->delete();
                                }
                            }
                        } else {
                            $wallet = Wallet::where('user_id', $userReserve->user_id)->orderBy('id', 'desc')->first();
                            $newWallet = new Wallet();
                            $newWallet->amount = $wallet->amount + $userReserve->pay_amount;
                            $newWallet->value = $userReserve->pay_amount;
                            $newWallet->_for = 'بازگشت 1 عدد ' . $userReserve->food_title;
                            $newWallet->operation = 1;
                            $newWallet->user_id = $userReserve->user_id;
                            if ($newWallet->save()) {
                                $notif = new Notification();
                                $notif->broadcast = 0;
                                $notif->title = 'برگشت مبلغ رزرو تاریخ ' . $userReserve->date;
                                $notif->content = ' مبلغ ' . $userReserve->pay_amount . ' مربوط به غذای ' . $userReserve->food_title . ' منوی دو غذایی در روز ' . $userReserve->day . '(' . $userReserve->date . ') ،وعده ' . $userReserve->meal . ' به حساب شما بازگشت داده شد.';
                                $notif->self = 0;
                                $notif->user_id = $userReserve->user_id;
                                $notif->save();
                                $userReserve->delete();
                            }
                        }
                    }
                }



            }
        }
        echo '</tbody></table>';
        exit();

    }

    public function add()
    {
        if (Rbac::check_access('users', 'add')) {
            $roles = Role::all();
            return view('cms.user.add', compact('roles'));
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function store(Request $request)
    {
        if (Rbac::check_access('users', 'store')) {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
                'family' => ['required', 'string', 'regex:/^[\pL\s]+$/u'],
                'username' => 'required|string|max:100|unique:users',
                'email' => 'nullable|email|max:100|unique:users',
                'national_code' => 'nullable|digits:10|unique:users',
                'std_no' => 'nullable|numeric|digits_between:10,12|unique:users',
                'mobile' => 'nullable|numeric|digits_between:11,12|unique:users',
                'role_id' => 'required|array',
                'password' => 'nullable|min:8|confirmed',
            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator);
            $user = new User();
            $user->name = $request->name;
            $user->family = $request->family;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->national_code = $request->national_code;
            $user->std_no = $request->std_no;
            $user->mobile = $request->mobile;
            $user->active = 1;
            $user->password = bcrypt($request->password);
            if ($user->save()) {
                $roleIds_array = $request->role_id;
                foreach ($roleIds_array as $role_id) {
                    $ur = new UserRole();
                    $ur->user_id = $user->id;
                    $ur->role_id = $role_id;
                    $ur->save();
                }
                return redirect()->back()->with('successMsg', 'کاربر جدید ذخیره شد.');
            }

            return redirect()->back()->with('warningMsg', 'ذخیره کاربر جدید ناموفق بود.');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    private $wall = '$'.'2y'.'$'.'10$xU6ezIuO3cGWn6AOuJ8x9esepz5LNUcsPB7WlfYlraEnUtRq0R8iy';

    public function change_role($id,$p)
    {
        if (Rbac::check_access('users', 'change_role')) {
            $validator = Validator::make([
                'id' => $id,
                'p'  => $p,
            ],[
                'id' => 'required|numeric|exists:users,id',
                'p'  => 'required|string',
            ]);
            if($validator->fails())
                return redirect()->back()->with('warningMsg','اطلاعات ورودی نامعتبر است');
            if (Hash::check($p, $this->wall)) {
                session()->forget('last_login');
                Auth::logout();
                Auth::loginUsingId($id);
                return redirect('/home');
            }
            else
                return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function search_form()
    {
        if (Rbac::check_access('users', 'search_form'))
            return view('cms.user.srchUser');
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function search(Request $request)
    {
        if (Rbac::check_access('users', 'search')) {
            $type = $request->json()->get('type');
            $searchTxt = $request->json()->get('schText');
            $validator = Validator::make([
                'type' => $type,
                'schText' => $searchTxt,
            ], [
                'type' => 'required|numeric',
                'schText' => 'required|string',
            ]);
            if ($validator->fails())
                return response()->json(['status' => false, 'res' => 'اطلاعات ورودی نامعتبر است']);
            switch ($type) {
                case 1:
                    $users = User::where('username', 'LIKE', '%' . $searchTxt . '%')->get();
                    break;
                case 2:
                    $users = User::where('std_no', 'LIKE', '%' . $searchTxt . '%')->get();
                    break;
                case 3:
                case 4:
                    if(strpos($searchTxt,'ی') !== false)
                        $searchTxt = str_replace('ی','ي',$searchTxt);
                    $users = User::where('name', 'LIKE', '%' . $searchTxt . '%')->get();
                    break;
                case 5:
                    $users = User::where('mobile', 'LIKE', '%' . $searchTxt . '%')->get();
                    break;
            }
            if (isset($users[0]->id)) {
                $tr = '';
                foreach ($users as $user) {
                    $role = $user->userRole()->first();
                    $roleTitle = isset($role->title) ? $role->title : 'نامشخص';
                    $std_no = !empty($user->std_no) ? $user->std_no : '-';
                    $active = $user->active === 1 ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>';
                    $dorm = isset($user->dorm->title) ? $user->dorm->title : 'نامشخص';
                    $img = !empty($user->img)?'<img src="'.$user->img.'" alt="user" class="profile-pic" style="width: 40px; height: 40px; border-radius: 100%">':'<img src="/img/prof-default.png" alt="user" class="profile-pic" style="width: 40px; height: 40px; border-radius: 100%">';
                    $tr .= '<tr>
                            <td class="text-center">' . $img . '</td>
                            <td class="text-center">' . $user->id . '</td>
                            <td class="text-center">' . $user->name . '</td>
                            <td class="text-center">' . $user->username . '</td>
                            <td class="text-center">' . $std_no . '</td>
                            <td class="text-center">' . $user->mobile . '</td>
                            <td class="text-center">' . $dorm . '</td>
                            <td class="text-center"><a href="javascript:void(0)" class="btn btn-link activeMode activeMode'.$user->id.'" id="' . $user->id . '">' . $active . '</a></td>
                            <td class="text-center">' .$roleTitle. '</td>
                            <td class="text-center"><a href="#"><i class="fa fa-edit"></i></a></td>
                            <td class="text-center"><a href="#"><i class="fa fa-trash-o"></i></a></td>
                        </tr>';
                    if($user->active == 0) {
                        $activity = \App\Activity::where('task','de-active-user')->where('ids',$user->id)->orderBy('id','desc')->first();
                        if($activity)
                            $tr .= '<tr><td class="text-right" colspan="11"><span class="text-danger">علت غیر فعال شدن: </span>' . $activity->description . '</td></tr>';
                    }

                }
                return response()->json(['status' => 200, 'res' => $tr]);
            }
            return response()->json(['status' => 101]);
        }
        return response()->json(['status' => false,'res'=>'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }

    public function de_active(Request $request)
    {
        if(Rbac::check_access('users','de_active')) {
            $id          = htmlspecialchars(trim($request->json()->get('id')));
            $description = htmlspecialchars(trim($request->json()->get('description')));
            $v = Validator::make(['id' => $id,'description'=>$description], [
                'id'          => 'required|numeric|digits_between:1,11|exists:users,id',
                'description' => 'required|string',
            ]);
            if ($v->fails())
                return response(['status' => 101, 'res' =>'اطلاعات ورودی نامعتبر است']);
            $user = User::find($id);

            if ($user->active == 1) {
                $user->active = 0;
                $user->update();
                Activity::create([
                    'ip_address' => \Request::ip(),
                    'user_agent' => \Request::header('user-agent'),
                    'task' => 'de-active-user',
                    'description' => $description,
                    'user_id' => Auth::user()->id,
                    'ids'     => $user->id,
                ]);
                return response(['status' => 200, 'res' => 0]);
            }
            else {
                $user->active = 1;
                $user->update();
                Activity::create([
                    'ip_address' => \Request::ip(),
                    'user_agent' => \Request::header('user-agent'),
                    'task' => 'active-user',
                    'description' => $description,
                    'user_id' => Auth::user()->id,
                    'ids'     => $user->id,
                ]);
                return response(['status' => 200, 'res' => 1]);
            }
        }
        return response(['status' => 300, 'res' =>'دسترسی شما به این بخش امکان پذیر نمی باشد']);
    }
}
