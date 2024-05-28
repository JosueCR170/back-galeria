<?php

use App\Http\Controllers\ArtistaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ObraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\ApiAuthMiddleware;

Route::prefix('v1')->group(
    function(){
        //user
        Route::post('/user/store',[UserController::class,'store']);//-
        Route::post('/user/login',[UserController::class,'login']);//-
        Route::get('/user/getIdentity',[UserController::class,'getIdentity'])->middleware(ApiAuthMiddleware::class);//user -
        Route::get('/user/{id}',[UserController::class,'show'])->middleware(ApiAuthMiddleware::class);//admin -
        Route::get('/user',[UserController::class,'index'])->middleware(ApiAuthMiddleware::class);//admin -
        Route::put('/user/{id}',[UserController::class,'update'])->middleware(ApiAuthMiddleware::class);//admin-user - -
        Route::delete('/user/{id}',[UserController::class,'destroy'])->middleware(ApiAuthMiddleware::class);//admin -

        //artista
        Route::post('/artista/store',[ArtistaController::class,'store']);//-
        Route::post('/artista/login',[ArtistaController::class,'login']);//-
        Route::get('/artista/getIdentity',[ArtistaController::class,'getIdentity'])->middleware(ApiAuthMiddleware::class);//-
        Route::get('/artista/{id}',[ArtistaController::class,'show']);//-
        Route::get('/artista',[ArtistaController::class,'index']);//-
        Route::put('/artista/{id}',[ArtistaController::class,'update'])->middleware(ApiAuthMiddleware::class);
        Route::delete('/artista/{id}',[ArtistaController::class,'destroy'])->middleware(ApiAuthMiddleware::class);
        
        //Obra
        
        //Factura

        //Envio

        Route::post('/obra',[ObraController::class,'store2'])->middleware(ApiAuthMiddleware::class);
        Route::put('/obra/{id}',[ObraController::class,'update'])->middleware(ApiAuthMiddleware::class);

        
    }
);

