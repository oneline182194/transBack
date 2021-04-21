<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PasajesController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('usuarios',UserController::class)->middleware('auth:sanctum');

Route::group(['prefix' => 'config'], function () {
    Route::get('listEmpresas',[ GeneralController::class,'listEmpresas' ]);
    Route::post('saveEmpresa',[ GeneralController::class,'saveEmpresa' ]); 

    Route::get('listConductores',[ GeneralController::class,'listConductores' ]);
    Route::post('saveConductor',[ GeneralController::class,'saveConductor' ]); 
    Route::post('saveVehiculo',[ GeneralController::class,'saveVehiculo' ]); 

    Route::get('listModelos',[ GeneralController::class,'listModelos' ]);
    Route::get('listServicios',[ GeneralController::class,'listServicios' ]);

    Route::get('buscarCliente/{tipo}/{documento}',[ GeneralController::class,'buscarCliente' ]);
    
});
Route::group(['prefix' => 'pasajes'], function () {
    Route::get('listTurnos/{origen}/{fecha}',[ PasajesController::class,'listTurnos' ]);
    Route::post('saveTurnos',[ PasajesController::class,'saveTurnos' ]); 
});


