<?php

namespace App;

use App\Library\jdf;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'family',
        'std_no',
        'national_code',
        'mobile',
        'username',
        'email',
        'kindid',
        'active',
        'kind',
        'ou',
        'sex',
        'dorm_id',
        'last_login',
        'img',
        'sso',
        'borse',
        'maghta_id',
        'dep',
        //'wallet',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function userRole()
    {
        return $this->belongsToMany('App\Role','users_roles','user_id','role_id');
    }

    public function dorm()
    {
        return $this->belongsTo('App\Dorm','dorm_id');
    }

    public function last_login($lastLogin = null)
    {
        $jdate = new jdf();
        if($lastLogin)
            return $jdate->getPersianDate($lastLogin,'d F Y ساعت H:i');
        else
            return $jdate->getPersianDate($this->last_login,'d F Y ساعت H:i');
    }

    public function img()
    {
        return $this->img ? $this->img:'/img/avatar.png';
    }

    public function rests()
    {
        return $this->belongsToMany('App\Rest','t_users_rests','user_id','rest_id')->withPivot('active')->wherePivot('active',1);
    }

    public function user_groups()
    {
        return $this->belongsToMany('App\UserGroup','user_group_users','user_id','user_group_id')->withPivot('id','priority','is_primary');
    }

    public function reserves()
    {
        return $this->hasMany('App\Reservation','user_id');
    }

    public function wallet()
    {
        return $this->hasMany('App\Wallet','user_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification','user_id')->where('broadcast',0)->orderBy('id','desc');
    }
    public function notificationsC()
    {
        return $this->hasMany('App\Notification','user_id')->where('broadcast',0)->count();
    }

    public function broadcasts()
    {
        $broadcasts = Notification::where('broadcast',1)->where('active',1)->orderBy('id','desc')->get();
        return $broadcasts;
    }

    public function update_wallet($amount)
    {
        $currWallet   = $this->wallet;
        $this->wallet = $currWallet + $amount;
        $this->save();
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
