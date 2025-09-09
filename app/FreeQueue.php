<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class FreeQueue extends Model
{
    protected $table = 'free_queue';
    protected $fillable = [
        'queue_name',
        'user_id',
        'orders',
        'prepared',
        'bill_number',
        'date',
        'meal',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function bill_time_in()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'H:i');
    }

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }

    public function prepared_view()
    {
        $user = $this->user;
        $img  = $user->img == null? "/img/avatar.png": $user->img;
        $view = '<article class="card card--1">
                            <div class="card__info-hover">
                                <div class="card__clock-info">
                                    <span class="card__time">'.$this->bill_number.'</span>
                                </div>
                            </div>
                            <div class="card__img"></div>
                            <a href="#" class="card_link">
                                <div class="card__img--hover" style="background-image:url('.$img.')"></div>
                            </a>

                            <div class="card__info">
                                <span class="card__category"> </span>
                                <h3 class="card__title">'.$user->name.'</h3>
                                <span class="card__by">زمان ثبت فیش  <a href="#" class="card__author" title="author">ساعت '.$this->bill_time_in().' دقیقه</a></span>
                            </div>
                        </article>';
        return $view;
    }
}
