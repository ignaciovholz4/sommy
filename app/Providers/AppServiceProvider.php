<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
/**ADD PAGINATION ROLE AND PERMISSIONS */
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Http\ViewComposers\DataComposer;
use App\Http\ViewComposers\RedirectComposer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Trust all proxies (useful when behind load balancer/reverse proxy)
        // This ensures Laravel reads the X-Forwarded-Proto header correctly
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX);
        }

        View::composer(
            ['cliente','compras.ingreso.create','admin.role.create','admin.role.edit','quotes.create'],
            'App\Http\ViewComposers\DataComposer'
        );

        View::composer(
            ['*'],
            'App\Http\ViewComposers\RedirectComposer'
        );

        Paginator::useBootstrap();
    }
}
