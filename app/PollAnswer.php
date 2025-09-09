<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 27/11/2019
 * Time: 01:42 PM
 */

namespace App;


use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    protected $table = 'a_answer';
    protected $fillable = [
        'user_id',
        'question_id',
        'active',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function title()
    {
        return $this->belongsTo('App\PollQuestion','question_id');
    }
}