<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 15/09/2017
 * Time: 09:34 AM
 */

namespace App\Library;


use Illuminate\Support\Facades\Auth;

class Activity
{
    public function create($input)
    {
        \App\Activity::create($input);
    }

    public function save_log($task,$desc,$ids = null)
    {
        \App\Facades\Activity::create([
            'ip_address'  => \Request::ip(),
            'user_agent'  => \Request::header('user-agent'),
            'task'        => $task,
            'description' => $desc,
            'user_id'     => Auth::user()->id,
            'ids'         => $ids,
        ]);
    }
}
