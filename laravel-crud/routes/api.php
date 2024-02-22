<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\testController;
use App\Http\Middleware\SessionMiddleware;


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:sanctum'], function() {

      Route::post('logout', [AuthController::class, 'logout']);
      Route::get('user',function (Request $request) {
        return $request->user();
        });
    });
});

Route::group(['middleware' => [SessionMiddleware::class]], function(){

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/users', [UserController::class,"store"]);
    Route::get('/users', [UserController::class,"index"]);
    Route::get('/users/{id}', [UserController::class,"show"]);
    Route::put('/users/{id}', [UserController::class,"update"]);
    Route::delete('/users/{id}', [UserController::class,"destroy"]);

    Route::post('/test', [testController::class, 'sessionTest'])->middleware(SessionMiddleware::class);
    Route::get('/test', [testController::class, 'sessionTest'])->middleware(SessionMiddleware::class);

});


