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

class PollQuestion extends Model
{
    protected $table = 'a_question';
    protected $fillable = [
        'title',
        'poll_id',
        'pos',
        'active',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function records()
    {
        $records = 0;
        $records = PollAnswer::where('question_id', $this->id)->count();
        return $records;
    }
}