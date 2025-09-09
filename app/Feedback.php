<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 25/11/2019
 * Time: 02:42 PM
 */

namespace App;


use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $fillable = [
        'content',
        'private',
        'type',
        'active',
        'seen',
        'user_id',
        'reply',
        'replier_id',
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
}