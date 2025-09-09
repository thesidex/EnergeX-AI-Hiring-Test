<?php

return [
    'name' => env('APP_NAME', 'energeX-api'),
    'env'  => env('APP_ENV', 'local'),
    'debug'=> (bool) env('APP_DEBUG', true),
    'url'  => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'key' => env('APP_KEY'), // not required for Lumen unless you use encryption
];
