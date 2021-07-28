<?php

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


use Illuminate\Support\Facades\Route;

Route::get('api_test' , function(){
    echo 'fuck111';
});

require_once __DIR__ . '/api/admin.php';
require_once __DIR__ . '/api/web.php';
