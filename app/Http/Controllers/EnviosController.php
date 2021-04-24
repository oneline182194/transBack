<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class EnviosController extends Controller
{
    public function listEnvios(Request $request){
        try{
            $envios = DB::select('CALL listaEnvios(1)');
            $response = [ 'status'=> true, 'data' => $envios];
            $codeResponse = 200;
        }catch(\Exception $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function saveEnvios(Request $request){

        $comprobante = [
            'fecha' => date("Y-m-d H:i:s"),
            'personas_id'=> $request->personas_id,
            'serie' => 'SE-02241',
            'monto' => $this->getMonto($request->paquetes),
            'igv' => 0.00,
            'descuento' => 0.00,
            'nota' => $request->nota ?? null,
            'tipo' => 2,
            'tipoDocumento_id' => $request->comprobante??null
        ];
        try {
            DB::beginTransaction(); 

            $comprobante = DB::table('comprobante')->insertGetId($comprobante);
            foreach ($request->paquetes as $key => $value) {
                $detalles = DB::table('detalles')->insertGetId([
                    'servicios_id' => $request->servicio_id,
                    'pasaje_id' => null,
                    'cantidad' => 1,
                    'precio' => $value['precio'],
                    'subtotal' => $value['precio'],
                    'comprobante_id' => $comprobante
                ]);
                $envios = DB::table('envios')->insertGetId([
                    'personas_id' => $request->personas_id,
                    'receptor' => $request->clienteD,
                    'descripcion' => $value['descripcion'],
                    'fechaAdqui' => date("Y-m-d H:i:s"),
                    'fechaEnvio' => null,
                    'fechaRecepcion' => null,
                    'estadoEnvio_id' => 1,
                    'comprobante_id' => $comprobante,
                    'detalle_id'=>$detalles,
                    'users_id' => 1
                ]);
                
            }
            DB::commit();
            $response = [ 'status'=> true, 'data' => $envios];
            $codeResponse = 200;

        } catch (\Throwable $th) {
            DB::rollBack();
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function getMonto($pack){
        $monto = 0;
        foreach ($pack as $key => $p) {
            $monto = $p['precio'] + $monto;
        }
        return $monto;
    }
}
