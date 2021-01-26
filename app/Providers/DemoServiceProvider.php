<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DemoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function register()
    {
        //给这个接口一个别名
        $this->app->bind('demo', 'App\Contracts\Demo\FirstInterface');

        //将Contract接口和它的实现类绑定
        $this->app->bind('App\Contracts\Demo\FirstInterface', 'App\Services\Demo\First');
    }
}