<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Auto-Detect Environment (.env vs .env.local)
|--------------------------------------------------------------------------
|
| Load .env.local when running on localhost or when the file exists in CLI
| (local dev). Falls back to .env (production/VPS) otherwise.
| This allows the same codebase to work seamlessly in both environments
| without manually swapping .env files.
|
*/
$isLocal = false;

if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    if (str_contains($host, ':')) {
        $host = explode(':', $host)[0];
    }
    $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
} elseif (php_sapi_name() === 'cli' || php_sapi_name() === 'cli-server') {
    $isLocal = file_exists(__DIR__.'/../.env.local');
}

if ($isLocal && file_exists(__DIR__.'/../.env.local')) {
    $app->loadEnvironmentFrom('.env.local');
}

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);


/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
