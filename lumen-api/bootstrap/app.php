<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(dirname(__DIR__));

$app->withFacades();
$app->withEloquent();

// configs
$app->configure('app');
$app->configure('database');
$app->configure('cache');
$app->configure('cors');

// providers
$app->register(Illuminate\Validation\ValidationServiceProvider::class);
$app->register(Illuminate\Cache\CacheServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Hashing\HashServiceProvider::class);
$app->register(Illuminate\Database\MigrationServiceProvider::class);

// route middleware
$app->routeMiddleware([
    'jwt' => App\Http\Middleware\JwtMiddleware::class,
]);

// global middleware
$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
]);

// **bind console kernel & exception handler**
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// routes
$app->router->group(['namespace' => 'App\Http\Controllers'], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
