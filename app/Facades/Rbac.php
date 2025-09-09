<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 14/09/2017
 * Time: 10:33 AM
 */

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class Rbac extends Facade {
    protected static function getFacadeAccessor() { return 'Rbac'; }
}