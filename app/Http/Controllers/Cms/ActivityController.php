<?php

namespace App\Http\Controllers\Cms;

use App\Activity;
use App\Facades\Rbac;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(Rbac::check_access('activity','index')) {
            $time = time();
            $thirtyDayAgo = Date('Y-m-d', $time - 1 * 86400);
            $activitys = Activity::where('created_at', '>=', $thirtyDayAgo)->get();
            $acTmp = [];
            foreach ($activitys as $activity) {
                $user = null;
                if ($activity->user_id != null)
                    $user = $activity->user;
                /*elseif ($activity->ids != null)
                    $user = User::where('username', $activity->ids)->first();*/
                $acTmp[] = (object)[
                    'username' => isset($user->username) ? $user->username : '',
                    'name'     => isset($user->name) ? $user->name : '',
                    'family'   => isset($user->family) ? $user->family : '',
                    'task'     => $activity->task,
                    'description' => $activity->description,
                    'ids' => $activity->ids,
                    'ip_address' => $activity->ip_address,
                    'created_at' => $activity->GetCreateDate(),
                ];
            }
            return view('cms.activity.index', [
                'activitys' => $acTmp,
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }
}
