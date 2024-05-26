<?php

use App\Http\Controllers\ArtistaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ObraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ApiAuthMiddleware;

Route::prefix('v1')->group(
    function(){
        Route::post('/artista/store',[ArtistaController::class,'store']);
        Route::post('/artista/login',[ArtistaController::class,'login']);

        Route::post('/user/store',[UserController::class,'store']);
        Route::post('/user/login',[UserController::class,'login']);

        Route::post('/obra',[ObraController::class,'store2'])->middleware(ApiAuthMiddleware::class);

        
    }
);

