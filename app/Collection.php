<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = 't_collection';

    protected $fillable = [
        'name',
        'parent_id',
        'independent',
        'equal_param_auth',
        'active',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'d F Y ساعت H:i');
    }

    public function parent()
    {
        return $this->belongsTo('App\Collection','parent_id');
    }

    public function rests()
    {
        return $this->hasMany('App\Rest','collection_id');
    }
}
