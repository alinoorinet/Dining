<?php


namespace App\Library;


use App\Menu;

class Wasteful
{
    public function checking()
    {
        $jdf = new jdf();
        $today     = $jdf->jdate('Y-m-d');
        $yesterday = $jdf->jdate('Y-m-d',time() - 86400);
        $ddfs      = Menu::where('date',$yesterday)->where('meal','نهار')->get();
        foreach ($ddfs as $ddf) {
            $reserves = $ddf->reservation()->where('active',1)->get();
            foreach ($reserves as $reserve) {
                $user = $reserve->user;
                $userWasteful = $user->wastefuls()->where('active',1)->orderBy('id','desc')->first();
                if($reserve->eaten == 1) {

                }
                else {

                }
            }
        }
    }
}
