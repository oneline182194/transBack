<?php

use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PasajesController;
use App\Http\Controllers\EnviosController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\ReportesController;

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
    Route::post('generateUserConduc',[ LoginController::class,'generateUserConduc' ]);
    Route::post('changePassword',[ LoginController::class,'changePassword' ]);
});

Route::get('dowloadXml/{numeracion}/{tipoDoc}/{empresa_id}',[ ExportController::class,'getFile' ]);
Route::get('apiComprobantes/{documento}',[ GeneralController::class,'apiComprobantes' ]);


Route::resource('usuarios',UserController::class)->middleware('auth:sanctum');

Route::get('getSerie/{idEmpresa}/{tipo}',[ PasajesController::class,'getSerie' ]);

Route::group(['prefix' => 'config'], function () {
    Route::get('listEmpresas',[ GeneralController::class,'listEmpresas' ]);
    Route::post('saveEmpresa',[ GeneralController::class,'saveEmpresa' ]); 
    Route::get('listUsers',[ GeneralController::class,'listUsers' ]);
    
    Route::get('listConductores',[ GeneralController::class,'listConductores' ]);
    Route::post('saveConductor',[ GeneralController::class,'saveConductor' ]); 
    Route::post('saveVehiculo',[ GeneralController::class,'saveVehiculo' ]); 
    Route::get('deleteVehiculo/{id}',[ GeneralController::class,'deleteVehiculo' ]); 

    Route::get('listModelos',[ GeneralController::class,'listModelos' ]);
    Route::get('listServicios',[ GeneralController::class,'listServicios' ]);
    Route::get('listComprobantes',[ GeneralController::class,'listComprobantes' ]);

    Route::post('editarEmpresa',[GeneralController::class,'editarEmpresa']);

    Route::get('buscarCliente/{tipo}/{documento}/{licencia?}',[ GeneralController::class,'buscarCliente' ]);

    Route::post('saveServicio',[GeneralController::class,'saveServicio']);

    Route::get('getLicencias',[GeneralController::class,'getLicencias']);
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
    Route::get('deleteDespachado/{id}',[ EnviosController::class,'deleteDespachado' ]);
    

    Route::post('listEntregas',[ EnviosController::class,'listEntregas' ]);
    Route::post('recibirEnvio',[ EnviosController::class,'recibirEnvio' ]); 
    Route::post('entregarEnvio',[ EnviosController::class,'entregarEnvio' ]); 
    Route::post('editarDestinatario',[ EnviosController::class,'editarDestinatario' ]);
}); 
Route::group(['prefix' => 'export'], function () {
    Route::get('comprobante/{id}',[ ExportController::class,'getComprobante' ]);
    Route::get('getComprobanteA4/{id}',[ ExportController::class,'getComprobanteA4' ]);
    Route::get('exportarNomina/{idTurno}',[ ExportController::class,'exportarNomina' ]);
});

Route::group(['prefix' => 'comprobantes'], function () {
    Route::get('enviarSUNAT/{date}',[ ComprobanteController::class, 'enviarSUNAT' ]);
    Route::get('AnularComprobanteSunat/{date}',[ ExtraController::class, 'AnularComprobanteSunat' ]);
});
Route::group(['prefix' => 'extra'], function () {
    Route::post('saveComprobante',[ ExtraController::class,'saveComprobante' ]); 
    Route::post('listaRegistros',[ ExtraController::class,'listaRegistros' ]); 
    Route::post('editPerson',[ GeneralController::class,'editPerson' ]); 
});
Route::group(['prefix' => 'reportes'], function () {
    Route::post('reporteDocumentos',[ ReportesController::class,'reporteDocumentos' ]);
    Route::post('reporteResumen',[ ReportesController::class,'reporteResumen' ]);
    Route::post('reporteBalance',[ ReportesController::class,'reporteBalance' ]);
    Route::post('reporteDiario',[ ReportesController::class,'reporteDiario' ]);
});


