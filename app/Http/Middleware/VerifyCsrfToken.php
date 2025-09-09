<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/home/callback',
        '/home/card/write',
        '/home/card/checking-card',
        '/home/card/check-reserve',
        '/home/card/free-check-reserve',
        '/home/card/eatened-counter',
        '/home/card/free-eatened-counter',
    ];
}
