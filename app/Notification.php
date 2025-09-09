<?php
/**
 * Created by PhpStorm.
 * User: farshad
 * Date: 11/20/2019
 * Time: 1:55 PM
 */

namespace App;


use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $fillable = [
        'title',
        'content',
        'self',
        'user_id',
        'broadcast',
        'active'
    ];


    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'Y-m-d');
    }
    public function updated_at()
    {
        $jdate = new jdf();
        if($this->updated_at == '')
            return '';
        return $jdate->getPersianDate($this->updated_at,'Y-m-d');
    }
    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
