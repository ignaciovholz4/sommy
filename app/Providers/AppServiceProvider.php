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
use App\Services\CreativeStudio\AiProvider\ImageProviderInterface;
use App\Services\CreativeStudio\AiProvider\GeminiImageProvider;
use App\Services\CreativeStudio\AiProvider\OpenAiImageProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Estudio de Publicaciones: proveedor de imagenes intercambiable (config('services.publicaciones.image_provider')).
        $this->app->bind(ImageProviderInterface::class, function () {
            return config('services.publicaciones.image_provider') === 'gemini'
                ? new GeminiImageProvider()
                : new OpenAiImageProvider();
        });
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
