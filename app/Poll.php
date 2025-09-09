<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 27/11/2019
 * Time: 01:41 PM
 */

namespace App;


use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'a_poll';
    protected $fillable = [
        'title',
        'user_id',
        'active',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function have_records()
    {
        $total = 0;
        $questions = PollQuestion::where('poll_id', $this->id)->orderBy('pos' ,'asc')->get();
        foreach ($questions as $question) {
            $total = $total + PollAnswer::where('question_id' ,$question->id)->count();
        }
        return $total;
    }
}