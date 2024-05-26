<?php

use App\Http\Controllers\ArtistaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

Route::prefix('v1')->group(
    function(){
        Route::post('/artista/login',[ArtistaController::class,'login']);
    }
);

