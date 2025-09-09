<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'card';

    protected $fillable = [
        'cardNumber',
        'cardUid',
        'username',
        'type',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function updated_at()
    {
        $jdate = new jdf();
        if($this->updated_at == '')
            return '';
        return $jdate->getPersianDate($this->updated_at,'d F Y ساعت H:i');
    }
}
