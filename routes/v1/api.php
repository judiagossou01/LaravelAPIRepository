<?php

use App\Http\Controllers\Api\V1\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['envKeyAuth']], function () {
    Route::get('/', HomeController::class);
    Route::post('user-login', [HomeController::class, 'login']);
});
