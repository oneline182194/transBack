<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Hashids\Hashids;

class EnviosController extends Controller
{
    public function listEnvios(Request $request){
        try{
            $search = '';
            $data = $request->all();
            $hashids = new Hashids('',4,'1234567890ABCDEFGHIJKLMNOPQRSTU');
            if($request->parametro == 'e.id' && $request->buscar){
                $search = $hashids->decode($request->buscar);
                $search = $search[0];
            }else{
                $search = $request->buscar;
            }
            $envios = DB::select("CALL getEncomienda($request->estado)");

            foreach ($envios as $key => $row) {
                $envios[$key]->clave = $hashids->encode($row->id);
                $envios[$key]->carros = DB::table('envio_carro')->where('envioId',$envios[$key]->id )->get();
            }
            $response = [ 'status'=> true, 'data' => $envios ];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function listEntregas(Request $request){
        try{
            $envios = DB::select("CALL listaEntrega($request->region, $request->page,$request->size)");
            $hashids = new Hashids('',4,'1234567890ABCDEFGHIJKLMNOPQRSTU');
            foreach ($envios as $key => $row) {
                $envios[$key]->code = $hashids->encode($row->envioId);
            }
            $response = [ 'status'=> true, 'data' => $envios];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function saveEnvios(Request $request){
        //return response()->json( $request );
        $comprobante = [
            'fecha' => date("Y-m-d H:i"),
            'personas_id'=> $request->personas_id,
            'serie' => $request->serie,
            'empresa_id' => $request->empresa_id,
            'monto' => ( floatval ($request->cancelado) + floatval($request->xcobrar) + floatval($request->taxi) ),
            'correlativo' => $this->getSerie($request->serie),
            'igv' => 0.00,
            'descuento' => 0.00,
            'nota' => $request->nota ?? null,
            'tipo' => 2,
            'tipoDocumento_id' => $request->comprobante??null,
            'user_id' =>$request->user_id
        ];
        try {
            DB::beginTransaction(); 

            $comprobante = DB::table('comprobante')->insertGetId($comprobante);
           
            $detalles = DB::table('detalles')->insertGetId([
                'servicios_id' => $request->servicio_id,
                'pasaje_id' => null,
                'cantidad' => 1,
                'precio' => ( floatval ($request->cancelado) + floatval($request->xcobrar)  ),
                'subtotal' => ( floatval ($request->cancelado) + floatval($request->xcobrar)  ),
                'comprobante_id' => $comprobante
            ]);
            if($request->taxi || $request->taxi > 0){
                $taxi = DB::table('detalles')->insertGetId([
                    'servicios_id' => 5,
                    'pasaje_id' => null,
                    'cantidad' => 1,
                    'precio' => floatval($request->taxi),
                    'subtotal' => floatval($request->taxi),
                    'comprobante_id' => $comprobante
                ]);
            }
            $envios = DB::table('envios')->insertGetId([
                'personas_id' => $request->personas_id,
                'receptor' => $request->nombresD,
                'descripcion' => $request->contenido,
                'destino' => $request->direccionD,
                'fechaAdqui' => date("Y-m-d H:i"),
                'fechaEnvio' => null,
                'fechaRecepcion' => null,
                'estadoEnvio_id' => 1,
                'comprobante_id' => $comprobante,
                'detalle_id'=>$detalles,
                'users_id' => $request->user_id,

                'balija' => $request->balija,
                'xcobrar' => $request->xcobrar,
                'taxi' => $request->taxi,

                'ro' => intval($request->ro),
                'rd' => intval($request->rd),
            ]);
            DB::commit();
            $response = [ 'status'=> true, 'data' => $envios];
            $codeResponse = 200;

        } catch (\Exceptions $e) {
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
    public function getSerie($serie){
        $serie = DB::select("select (correlativo + 1) as correlativo from comprobante   where empresa_id = 5 and  serie = '".$serie."' and estado = 1 order by correlativo desc  limit 1");
        if(count($serie) > 0){
            return $serie[0]->correlativo;
        }else{
            return 1;
        }
    }
    public function despachado(Request $request){
        try{
            $dt = $request->all();

            foreach ( $dt as  $key => $row){
                $data = [ 
                        'envioId' => $row['envioId'],
                        'placa' => $row['placa'],
                        'personal_id'=> $row['personal_id'],
                        'carga'=> $row['carga'],
                        'fecha' => $row['fecha'],
                        'hora'=> $row['hora'],
                        'estado'=>1
                    ];
                if($row['id'] == 0){
                    $despachado = DB::table('envio_carro')->insert($data);
                }else{
                    $despachado = DB::table('envio_carro')->where('id',$row['id'])->update($data);
                }
                
            }
            $response = [ 'status'=> true, 'data' => $despachado];
            $codeResponse = 200;
        }catch (\Exceptions $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function deleteDespachado($id){
        try{
            $despachado =DB::table('envio_carro')->where('id', $id)->delete();
            $response = [ 'status'=> true, 'data' => $despachado];
            $codeResponse = 200;
        }catch (\Exceptions $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function recibirEnvio(Request $request){
        try{
            $data = [
                'estadoEnvio_id' => 3,
                'user_rep' => $request->username,
                'fechaRecepcion' => date("Y-m-d H:i:s")
            ];
            $despachado = DB::table('envios')->where('id',$request->idEnvio)->update($data);
            $response = [ 'status'=> true, 'data' => $despachado ];
            $codeResponse = 200;
        }catch (\Exceptions $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function entregarEnvio(Request $request){
        try{
            $data = [
                'estadoEnvio_id' => 4,
                'user_ent' => $request->username,
                'fechaEntrega' => date("Y-m-d H:i:s")
            ];
            $despachado = DB::table('envios')->where('id',$request->idEnvio)->update($data);
            $response = [ 'status'=> true, 'data' => $despachado ];
            $codeResponse = 200;
        }catch (\Exceptions $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    function editarDestinatario(Request $request){
        try{
            $data = [
                'ddni' => $request->ddni,
                'receptor' => $request->receptor,
                'dtel' => $request->dtel,

            ];
            $editart = DB::table('envios')->where('id',$request->envioId)->update($data);
            $response = [ 'status'=> true, 'data' => $editart ];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
    }
}
