<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 1396-06-20
 * Time: 11:32 PM
 */

namespace app\Library;

use App\Module;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Rbac
{
    public function get_access($search)
    {
        if(!Auth::check())
            return false;
        $user = User::find(Auth::user()->id);
        if(!$user)
            return false;
        if($user->active == 0)
            return false;
        $roles = $user->userRole()->where('active',1)->get();
        foreach ($roles as $role) {
            if ($role->title == $search)
                return true;
        }
        return false;
    }

    public function check_access($mod,$act)
    {
        if(!Auth::check())
            return false;

        $user = User::find(Auth::user()->id);
        if($user->active == 0)
            return false;

        $roles = $user->userRole()->orderBy('priority','desc')->where('active',1)->get();

        foreach ($roles as $role) {
            $roleAction = $role->roleAction()->where('active', 1)->get();
            foreach ($roleAction as $action) {
                $module = $action->module()->where('active', 1)->first();
                if (!$module)
                    continue;

                if ($module->title == $mod && $action->title == $act) {
                    /*$lua = $user->limit_user_actions()->where('action_id', $action->id)->first();
                    if (!$lua)*/
                        return true;
                }
            }
        }
        return false;
    }

    /*public function check_priority()
    {
        if(!Auth::check())
            return false;
        $user = User::find(Auth::user()->id);
        if(!$user)
            return false;
        if($user->active == 0)
            return false;

        $role = $user->userRole()->where('active',1)->first();
        if(!$role)
            return false;
        return $role->priority;
    }*/

    public function check_high_priority()
    {
        if(!Auth::check())
            return false;
        $user = User::find(Auth::user()->id);
        if(!$user)
            return false;
        if($user->active == 0)
            return false;

        $role = $user->userRole()->orderBy('priority','desc')->where('active',1)->first();
        if(!$role)
            return false;
        return $role->priority;
    }

    public function user_role($user = null)
    {
        if(!Auth::check())
            return false;
        if(!$user)
            $user  = User::find(Auth::user()->id);
        $role = $user->userRole()->orderBy('priority','desc')->where('active',1)->first();
        if(!$role)
            return "undefined";

        return $role->title;
    }

    public function user_roles($user = null)
    {
        if(!Auth::check())
            return false;
        if(!$user)
            $user  = User::find(Auth::user()->id);

        $roles = $user->userRole()->where('active',1)->get();
        if(!isset($roles[0]->id))
            return ["undefined"];

        $rolesArr = [];
        foreach ($roles as $role)
            array_push($rolesArr,$role->title);

        return $rolesArr;
    }

    public function module_is_active($moduleTitle)
    {
        $module = Module::where('title',$moduleTitle)->first();
        if(!$module)
            return $module->active;
        return false;
    }
}
