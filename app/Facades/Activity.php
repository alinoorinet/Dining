<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 15/09/2017
 * Time: 10:32 AM
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Activity extends Facade {
    protected static function getFacadeAccessor() { return 'Activity'; }
}