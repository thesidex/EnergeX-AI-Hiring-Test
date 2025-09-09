<?php

// Health endpoints (non-API)
$router->get('/', function () {
    return response()->json(['ok' => true, 'service' => 'lumen-api']);
});
$router->get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// API v1
$router->group(['prefix' => 'api'], function () use ($router) {

    // Basic ping
    $router->get('ping', function () {
        return response()->json(['message' => 'pong']);
    });

    // Auth
    $router->post('register', 'AuthController@register');
    $router->post('login',    'AuthController@login');

    // Public (or you can protect these with 'jwt' if desired)
    $router->get('posts',      'PostController@index');
    $router->get('posts/{id}', 'PostController@show');

    // Protected write operations
    $router->group(['middleware' => 'jwt'], function () use ($router) {
        $router->post('posts',          'PostController@store');
        $router->put('posts/{id}',      'PostController@update');
        $router->patch('posts/{id}',    'PostController@update'); // partial updates
        $router->delete('posts/{id}',   'PostController@destroy');
    });

    // CORS preflight for any /api/* path
    $router->options('{any:.*}', function () {
        return response('', 204);
    });
});
