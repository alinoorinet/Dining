<?php

namespace App;

use App\Library\jdf;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';

    protected $fillable = [
        'min_possible_cash',
        'limit_unpaied_housing_exception',
        'bf_meal_is_active',
        'lu_meal_is_active',
        'dn_meal_is_active',
        'sh_meal_is_active',
        'ft_meal_is_active',
        'mv_meal_is_active',
        'ar_meal_is_active',
        'block_bf_non_dorm',
        'block_lu_non_dorm',
        'block_dn_non_dorm',
        'block_bf_no_card',
        'block_lu_no_card',
        'block_dn_no_card',
        'discount_type',
        'day_type_cd',
    ];

    public function created_at()
    {
        $jdate = new jdf();
        if($this->created_at == '')
            return '';
        return $jdate->getPersianDate($this->created_at,'Y-m-d H:i:s');
    }
}
