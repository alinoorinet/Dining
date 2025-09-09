<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('includes.user-nav','App\Http\ViewComposers\NavComposer@index');
        view()->composer('includes.cms-nav','App\Http\ViewComposers\NavComposer@wallet');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
