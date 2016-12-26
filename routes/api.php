<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api){
    $api->post('authenticate', 'App\Http\Controllers\ApiAuthController@authenticate');
    $api->post('register', 'App\Http\Controllers\ApiAuthController@register');
});

$api->version('v1', ['middleware' => 'jwt.auth'], function ($api){
    $api->get('refresh_token', 'App\Http\Controllers\ApiAuthController@refreshToken');
    $api->get('posts', 'App\Http\Controllers\PostController@getPosts');
});
