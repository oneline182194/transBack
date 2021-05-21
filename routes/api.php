<?php

use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PasajesController;
use App\Http\Controllers\EnviosController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExtraController;

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
Route::group(['prefix' => 'auth'], function () {
    Route::post('signIn',[ LoginController::class,'signIn' ]);
});


Route::resource('usuarios',UserController::class)->middleware('auth:sanctum');

Route::get('getSerie/{idEmpresa}/{tipo}',[ PasajesController::class,'getSerie' ]);

Route::group(['prefix' => 'config'], function () {
    Route::get('listEmpresas',[ GeneralController::class,'listEmpresas' ]);
    Route::post('saveEmpresa',[ GeneralController::class,'saveEmpresa' ]); 

    Route::get('listConductores',[ GeneralController::class,'listConductores' ]);
    Route::post('saveConductor',[ GeneralController::class,'saveConductor' ]); 
    Route::post('saveVehiculo',[ GeneralController::class,'saveVehiculo' ]); 
    Route::get('deleteVehiculo/{id}',[ GeneralController::class,'deleteVehiculo' ]); 

    Route::get('listModelos',[ GeneralController::class,'listModelos' ]);
    Route::get('listServicios',[ GeneralController::class,'listServicios' ]);
    Route::get('listComprobantes',[ GeneralController::class,'listComprobantes' ]);

    Route::post('editarEmpresa',[GeneralController::class,'editarEmpresa']);

    Route::get('buscarCliente/{tipo}/{documento}',[ GeneralController::class,'buscarCliente' ]);

    Route::post('saveServicio',[GeneralController::class,'saveServicio']);
    
});
Route::group(['prefix' => 'pasajes'], function () {
    Route::get('listTurnos/{origen}/{fecha}',[ PasajesController::class,'listTurnos' ]);
    Route::get('getAsientos/{turnoId}',[ PasajesController::class,'getAsientos' ]); 
    Route::post('saveTurnos',[ PasajesController::class,'saveTurnos' ]); 
    Route::post('savePasaje',[ PasajesController::class,'savePasaje' ]); 
    Route::get('anularPasaje/{anularPasaje}',[ PasajesController::class,'anularPasaje' ]); 
    
});
Route::group(['prefix' => 'envios'], function () {
    Route::post('listEnvios',[ EnviosController::class,'listEnvios' ]);
    Route::post('saveEnvios',[ EnviosController::class,'saveEnvios' ]);
    Route::post('despachado',[ EnviosController::class,'despachado' ]);

    Route::post('listEntregas',[ EnviosController::class,'listEntregas' ]);
    Route::get('recibirEnvio/{idEnvio}',[ EnviosController::class,'recibirEnvio' ]); 
    Route::get('entregarEnvio/{idEnvio}',[ EnviosController::class,'entregarEnvio' ]); 
});
Route::group(['prefix' => 'export'], function () {
    Route::get('comprobante/{id}',[ ExportController::class,'getComprobante' ]);
    Route::get('exportarNomina/{idTurno}',[ ExportController::class,'exportarNomina' ]);
});

Route::group(['prefix' => 'comprobantes'], function () {
    Route::get('enviarSUNAT/{date}',[ ComprobanteController::class, 'enviarSUNAT' ]);
});
Route::group(['prefix' => 'extra'], function () {
    Route::post('saveComprobante',[ ExtraController::class,'saveComprobante' ]); 
    Route::get('listaRegistros/{page}',[ ExtraController::class,'listaRegistros' ]); 
    
});


