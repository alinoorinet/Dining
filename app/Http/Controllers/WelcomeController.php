<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Dorm;
use App\Food;
use App\Rest;
use App\Transaction;
use App\User;
use App\UserGroup;
use App\UserGroupUsers;
use App\UsersRests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Facades\Activity;
use App\Role;
use App\UserRole;
use App\Wallet;
use App\WalletTmp;
use Illuminate\Support\Facades\Validator;

class WelcomeController extends Controller
{
    /*public function __construct()
    {
        //return $this->middleware('Filter');
    }*/

    protected $redirectTo = '/home';

    public function testcode()
    {
        Auth::loginUsingId(9);

        $user = User::find(9);
        $user->ou = "bs";
        $user->std_no = '881285115';
        $user->borse = 0;
        $user->maghta_id = 1;
        $user->kindid = 2;

        $res = new \stdClass();
        $res->dep = 10;

        $this->set_role(9,$user->ou);
        $this->set_user_group($user);
        $user->sex = 2;
        $this->set_collection_rest($res,$user);

        session()->put('origin_ip',\Request::ip());
        session()->regenerate();
        return redirect('/home');
    }

    public function index(Request $request)
    {
        /*session()->forget('locInUni');
        $userIp = \Request::ip();
        $ipFilterRange = [
            '164.215.206/23',
            '89.37.12.0/24',
        ];
        if (filter_var($userIp, FILTER_VALIDATE_IP)) {
            $iir = new ip_in_range();
            foreach ($ipFilterRange as $item) {
                if($iir->ip_in_range($userIp,$item)){
                    session()->put('locInUni',true);
                    break;
                }
            }
        }*/

        /*Auth::loginUsingId(1);
        session()->put('origin_ip',\Request::ip());
        session()->regenerate();
        return redirect('/home');*/

        if(Auth::check())
            return redirect('/home');

        if($request->query('tk')) {
            $tk = $request->query('tk');
            $url  = env('AUTH_URL');
            $url .= "/login-api/token/".$tk;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url,
                //CURLOPT_SSL_VERIFYPEER => False,
            ));
            $res = json_decode(curl_exec($curl));
			$err = curl_error($curl);
            curl_close($curl);

            if(!$res) {	
				echo "loading...";
				//print_r($err);
				exit();
                Auth::logout();
                return redirect(env('AUTH_URL').'/logout');
            }
            if ($res->return->status == '200') {
                $username = isset($res->info->uid)       ? $res->info->uid       : null;
                $ntCode   = isset($res->info->melli)     ? $res->info->melli     : null;
                $stdNo    = isset($res->info->studentid) ? $res->info->studentid : null;
                $email    = isset($res->info->email)     ? $res->info->email     : null;
                $mobile   = isset($res->info->mobile)    ? $res->info->mobile    : null;
                // first validation
                $v = Validator::make([
                    'username'      => $username,
                    'national_code' => $ntCode,
                    'std_no'        => $stdNo,
                    'email'         => $email,
                    'mobile'        => $mobile,
                ],[
                    'username'      => 'required|string|max:190',
                    'national_code' => 'required|numeric|digits_between:6,15',
                    'std_no'        => 'nullable|numeric|digits_between:9,14',
                    'email'         => 'required|email|max:190',
                    'mobile'        => 'required|numeric|digits_between:9,15',
                ]);
                if($v->fails()) {
		    //echo "<pre>";  
                    //print_r($v->errors());
                    //exit("loading...");
                    return redirect(env('AUTH_URL').'/logout');
                }
                
                $user = \App\User::where('username', $username)->first();
                if (isset($user->id)) {
                    session()->put('last_login',$user->last_login);
                    $user->mobile        = $mobile;
                    $user->std_no        = $stdNo;
                    $user->national_code = $ntCode;
                    $user->email         = $email;
                    $user->name     = isset($res->info->fname)     ? $res->info->fname    : '';
                    $user->family   = isset($res->info->ename)     ? $res->info->ename    : '';
                    $user->kindid   = isset($res->info->kindid) && $res->info->kindid != '' ? $res->info->kindid   : 0;
                    $user->kind     = isset($res->info->kind)      ? $res->info->kind     : null;
                    $user->ou       = isset($res->info->ou)        ? $res->info->ou       : null;
                    $user->sex      = isset($res->info->sex)       ? $res->info->sex      : null;
                    $user->img      = isset($res->info->img)       ? $res->info->img      : null;
                    $user->borse     = isset($res->student->borse) && $res->student->borse != '' ? $res->student->borse : 0;
                    $user->maghta_id = isset($res->student->maghtaid) && $res->student->maghtaid != '' ? $res->student->maghtaid :null;
                    $user->dep       = isset($res->student->dep) && $res->student->dep != '' ? $res->student->dep : null;

                    $hUser = DB::connection('housing')->table('users')->select('id','username')->where('username',$username)->first();
                    if($hUser) {
                        $hSetting = DB::connection('housing')->table('t_setting')->select('current_term')->first();
                        $hReserve = DB::connection('housing')->table('t_reserve')->select('id','room_id','user_id','status','active','term')->where('user_id',$hUser->id)->where('active',1)->where('term',$hSetting->current_term)->first();
                        if($hReserve) {
                            $hRoom = DB::connection('housing')->table('t_room')->select('id', 'dorm_id')->where('id', $hReserve->room_id)->first();
                            $dorm  = Dorm::where('housing_id',$hRoom->dorm_id)->first();
                        }
                        else
                            $dorm  = Dorm::where('title','غیر خوابگاهی')->first();
                    }
                    else
                        $dorm = Dorm::where('title','غیر خوابگاهی')->first();
                    $user->dorm_id    = isset($dorm->id) ? $dorm->id : null;
                    $user->last_login = date('Y-m-d H:i:s');
                    $user->sso = 1;
                    $user->update();

                    $this->set_role($user->id,$res->info->ou);
                    $this->set_user_group($user);
                    $this->set_collection_rest($res,$user);

                    Auth::loginUsingId($user->id, $request->remember == 'on' ? true : false);
                    session()->put('origin_ip',\Request::ip());
                    if(isset($res->info->studentid))
                        $this->wallet_tmp_transfer($user->id, $res->info->studentid);
                    Activity::create([
                        'ip_address' => \Request::ip(),
                        'user_agent' => \Request::header('user-agent'),
                        'task' => 'login',
                        'description' => 'ورود به سیستم',
                        'user_id' => $user->id,
                    ]);

                    session()->regenerate();
                    return redirect()->to($this->redirectTo);
                }
                else {
                    // second validation
                    if(is_numeric($stdNo)) {
                        $checkUnique = User::where('std_no',$stdNo)->first();
                        if($checkUnique) // std-no is non-unique
                            return redirect(env('AUTH_URL').'/logout');
                    }

                    $user = new \App\User();
                    $user->username      = $username;
                    $user->mobile        = $mobile;
                    $user->std_no        = $stdNo;
                    $user->national_code = $ntCode;
                    $user->email         = $email;
                    $user->name     = isset($res->info->fname)     ? $res->info->fname      : '';
                    $user->family   = isset($res->info->ename)     ? $res->info->ename      : '';
                    $user->kindid   = isset($res->info->kindid) && $res->info->kindid != '' ? $res->info->kindid     : 0;
                    $user->kind     = isset($res->info->kind)      ? $res->info->kind       : null;
                    $user->ou       = isset($res->info->ou)        ? $res->info->ou         : null;
                    $user->sex      = isset($res->info->sex)       ? $res->info->sex        : null;
                    $user->img      = isset($res->info->img)       ? $res->info->img        : null;
                    $user->borse    = isset($res->student->borse) && $res->student->borse != '' ? $res->student->borse   : 0;
                    $user->maghta_id = isset($res->student->maghtaid) && $res->student->maghtaid != '' ? $res->student->maghtaid :null;
                    $user->dep       = isset($res->student->dep) && $res->student->dep != '' ? $res->student->dep : null;

                    $hUser = DB::connection('housing')->table('users')->select('id','username')->where('username',$username)->first();
                    if($hUser) {
                        $hSetting = DB::connection('housing')->table('t_setting')->select('current_term')->first();
                        $hReserve = DB::connection('housing')->table('t_reserve')->select('id','room_id','user_id','status','active','term')->where('user_id',$hUser->id)->where('active',1)->where('term',$hSetting->current_term)->first();
                        if($hReserve) {
                            $hRoom = DB::connection('housing')->table('t_room')->select('id', 'dorm_id')->where('id', $hReserve->room_id)->first();
                            $dorm  = Dorm::where('housing_id',$hRoom->dorm_id)->first();
                        }
                        else
                            $dorm  = Dorm::where('title','غیر خوابگاهی')->first();
                    }
                    else
                        $dorm  = Dorm::where('title','غیر خوابگاهی')->first();

                    $user->dorm_id  = isset($dorm->id) ? $dorm->id : null;
                    $user->active   = 1;
                    $last_login       = date('Y-m-d H:i:s');
                    $user->last_login = $last_login;
                    $user->sso = 1;
                    session()->put('last_login',$last_login);
                    if($user->save()) {
                        $this->set_role($user->id,$res->info->ou);
                        if(isset($res->info->studentid))
                            $this->wallet_tmp_transfer($user->id, $res->info->studentid);

                        $this->set_user_group($user);
                        $this->set_collection_rest($res,$user);

                        Auth::loginUsingId($user->id, $request->remember == 'on' ? true : false);
                        session()->put('origin_ip',\Request::ip());
                        Activity::create([
                            'ip_address'  => \Request::ip(),
                            'user_agent'  => \Request::header('user-agent'),
                            'task'        => 'login',
                            'description' => 'ورود به سیستم',
                            'user_id'     => $user->id,
                        ]);
                        session()->regenerate();
                        return redirect()->to($this->redirectTo);
                    }
                    else {
                        Auth::logout();
                        return redirect(env('AUTH_URL').'/logout');
                    }
                }
            }
            else {
                Auth::logout();
                return redirect(env('AUTH_URL').'/logout');
            }
        }
        else
            return redirect(env('AUTH_URL').'/login/?su='.env('APP_URL'));
    }

    protected function set_role($userId,$ou)
    {
        $userRole = UserRole::where('user_id',$userId)->first();
        if(!$userRole) {
            $roleTitle = 'user';
            $role = Role::where('title', $roleTitle)->first();
            if($role) {
                UserRole::create([
                    'user_id' => $userId,
                    'role_id' => $role->id,
                ]);
                return true;
            }
            return false;
        }
        return true;
    }

    protected function set_user_group($user)
    {
        if(is_numeric($user->std_no)) { // دانشجو
            $ouConverter = [
                'bs'        => 'کارشناسی',
                'ou-bs'     => 'کارشناسی',
                'ms'        => 'کارشناسی ارشد',
                'ou-ms'     => 'کارشناسی ارشد',
                'phd'       => 'دکترا',
                'ou-phd'    => 'دکترا',
                'guest'     => 'مهمان',
                'ou-guest'  => 'مهمان',
            ];

            $maghtaConverter = [
                0 => 'نامشخص',
                1 => 'کاردانی',
                2 => 'کارشناسی',
                3 => 'کارشناسی ارشد',
                5 => 'کارشناسی',
                6 => 'دکترا',
            ];

            // 1. Doreh : shabane, roozane ...
            $userKindId = $user->kindid;
            $userGroup  = UserGroup::where('kindid',$userKindId)->first();
            if($userGroup) {
                $checkExistsUGU = UserGroupUsers::where('user_id',$user->id)
                    ->where('user_group_id',$userGroup->id)
                    ->first();
                if(!$checkExistsUGU && $userKindId != 0) // نامشخص
                    UserGroupUsers::create([
                        'user_id'       => $user->id,
                        'user_group_id' => $userGroup->id,
                        'priority'      => 1,
                        'is_primary'    => 1,
                    ]);
            }

            // 2. boorse
            if($user->borse) {
                $userGroup = UserGroup::where('title','دانشجو بورسیه')->first();
                if($userGroup) {
                    $checkExistsUGU = UserGroupUsers::where('user_id',$user->id)
                        ->where('user_group_id',$userGroup->id)->first();
                    if(!$checkExistsUGU)
                        UserGroupUsers::create([
                            'user_id'       => $user->id,
                            'user_group_id' => $userGroup->id,
                            'priority'      => 2,
                        ]);
                }
            }

            // 3. Maghta: karshenasi,...
            if(isset($ouConverter[strtolower($user->ou)])) {
                $maghta = $ouConverter[strtolower($user->ou)];
                if($maghta == 'مهمان') {
                    // First: Set Guest Group
                    $userGroup  = UserGroup::where('title','مهمان')->first();
                    if($userGroup) {
                        $checkExistsUGU = UserGroupUsers::where('user_id',$user->id)
                            ->where('user_group_id',$userGroup->id)
                            ->first();
                        if(!$checkExistsUGU)
                            UserGroupUsers::create([
                                'user_id'       => $user->id,
                                'user_group_id' => $userGroup->id,
                                'priority'      => 1,
                                'is_primary'    => 1,
                            ]);
                    }

                    // Then: set maghta
                    $ssoMaghtaId = $user->maghta_id;
                    if (isset($maghtaConverter[$ssoMaghtaId])) {
                        $maghtaConverted = $maghtaConverter[$ssoMaghtaId];
                        $userGroup  = UserGroup::where('title',$maghtaConverted)->first();
                        if($userGroup) {
                            $checkExistsUGU = UserGroupUsers::where('user_id',$user->id)
                                ->where('user_group_id',$userGroup->id)
                                ->first();
                            if(!$checkExistsUGU)
                                UserGroupUsers::create([
                                    'user_id'       => $user->id,
                                    'user_group_id' => $userGroup->id,
                                    'priority'      => 2,
                                ]);
                        }
                    }
                }
                else {
                    $userGroup  = UserGroup::where('title',$maghta)->first();
                    if($userGroup) {
                        $checkExistsUGU = UserGroupUsers::where('user_id',$user->id)
                            ->where('user_group_id',$userGroup->id)
                            ->first();
                        if(!$checkExistsUGU)
                            UserGroupUsers::create([
                                'user_id'       => $user->id,
                                'user_group_id' => $userGroup->id,
                                'priority'      => 2,
                            ]);
                    }
                }
            }
        }
        else { // غیر دانشجو
            $ouConverter = [
                'staff'     => 'کارکنان',
                'ou-staff'  => 'کارکنان',
                'prof-temp' => 'اساتید',
                'prof'      => 'اساتید',
                'ou-prof'   => 'اساتید',
            ];

            if(isset($ouConverter[strtolower($user->ou)])) {
                $userMainGroup = $ouConverter[strtolower($user->ou)];
                switch ($userMainGroup) {
                    case 'کارکنان':
                    case 'اساتید':
                        $userGroup = UserGroup::where('title','LIKE','%'.$userMainGroup.'%')->first();
                        if($userGroup) {
                            $checkExistsUGU = UserGroupUsers::where('user_id',$user->id)
                                ->where('user_group_id',$userGroup->id)
                                ->first();
                            if(!$checkExistsUGU)
                                UserGroupUsers::create([
                                    'user_id'       => $user->id,
                                    'user_group_id' => $userGroup->id,
                                    'priority'      => 1,
                                    'is_primary'    => 1,
                                ]);
                        }
                }
            }
        }
    }

    protected function set_collection_rest($res, $user)
    {
        $sexConverter = [
            0 => 'نامشخص',
            1 => 'خواهران',
            2 => 'برادران',
            3 => 'مختلط',
        ];

        if (!isset($res->dep) || $res->dep != 11)
            $equalParamAuth = 0;
        else
            $equalParamAuth = $res->dep;

        $collections = Collection::where('equal_param_auth', $equalParamAuth)->get();
        foreach ($collections as $collection) {
            $rests = DB::table('t_rest')
                ->where('collection_id', $collection->id)
                ->whereIn('sex', [$sexConverter[$user->sex], 'مختلط'])
                ->get();

            foreach ($rests as $rest) {
                $checkExists = UsersRests::where('user_id', $user->id)
                    ->where('rest_id', $rest->id)
                    ->first();

                if (!$checkExists)
                    UsersRests::create([
                        'user_id' => $user->id,
                        'rest_id' => $rest->id,
                    ]);
            }
        }
    }

    protected function wallet_tmp_transfer($userId,$stdNo)
    {
        $walletTmp = WalletTmp::where('std_no',$stdNo)->where('active',1)->first();
        if($walletTmp) {
            $wallet = Wallet::where('user_id', $userId)->first();
            if ($wallet) {
                $lastAmount = $wallet->amount;
                $newWallet  = new Wallet();
                $newWallet->amount         = $lastAmount + $walletTmp->amount;
                $newWallet->transaction_id = null;
                $newWallet->user_id        = $userId;
                $newWallet->save();
            }
            else {
                $newWallet = new Wallet();
                $newWallet->amount         = $walletTmp->amount;
                $newWallet->transaction_id = null;
                $newWallet->user_id        = $userId;
                $newWallet->save();
            }
            $walletTmp->active = 0;
            $walletTmp->update();
        }
    }

    public function force_logout()
    {
        if(Auth::check())
            Auth::logout();
        return redirect('/');
    }

    public function sso_force_logout($token,$username)
    {
        $v = Validator::make(['username'=>$username],[
            'username' => 'required|string'
        ]);
        if(!$v->fails()) {
            //$referer = parse_url($_SERVER['HTTP_HOST']);
            if($token == 'token'){
                $user = User::where('username', $username)->first();
                if ($user) {
                    $user->sso = 0;
                    $user->update();
                    echo 'Done!';
                    exit();
                }
            }
        }
    }
}
