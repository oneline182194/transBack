<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comprobante;
class ExtraController extends Controller
{
    public function saveComprobante(Request $request){
        //return response()->json( $request );
        $comprobante = [
            'personas_id' => $request->personas_id,
            'fecha' => $request->fecha .'T'.date("H:i"),
            'serie' => $request->serie,
            'monto' => $request->total,
            'igv' => 0,
            'descuento' => 0,
            'nota' => $request->nota,
            'estado' => 1,
            'tipo' => 3,
            'tipoDocumento_id' => $request->comprobante,
            'empresa_id' => $request->empresa_id,
            'send' => 0,
            'correlativo' => $this->getSerie($request->empresa_id,$request->serie),
            'user_id' =>$request->user_id
        ];
        try{
            DB::beginTransaction(); 
            $comprobante_id = DB::table('comprobante')->insertGetId($comprobante);
            $person = DB::table('personas')->where('id',$request->personas_id)->update([ 'direccion' => $request->direccion ]);
            foreach($request->detalles as $d){
                $detalle = DB::table('detalles')->insertGetId([
                    'servicios_id' => $d['id'],
                    'pasaje_id' => null,
                    'cantidad'=> $d['cantidad'],
                    'precio'=> $d['pu'],
                    'subtotal' => $d['subtotal'],
                    'comprobante_id'=> $comprobante_id
                ]);
            }
            DB::commit();
            $response = [ 'status'=> true, 'data' => $comprobante_id];
            $codeResponse = 200;

        }catch(\Exception $e){
            DB::rollBack();
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function getSerie($idEmpresa,$serie){
        $serie = DB::select("select (correlativo + 1) as correlativo from comprobante   where empresa_id = ". $idEmpresa." and  serie = '".$serie."' and estado = 1 order by correlativo desc  limit 1");
        if(count($serie) > 0){
            return $serie[0]->correlativo;
        }else{
            return 1;
        }
    }
    public function listaRegistros(Request $request){
        try{
            $comprobantes = DB::select("CALL misFacturas($request->user_id,$request->page,$request->size)");
            $response = [ 'status'=> true, 'data' => $comprobantes];
            $codeResponse = 200;
        }catch(\Exception $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function AnularComprobanteSunat($idComprobante){
        try{
            $comprobante = DB::table('comprobante')->where('id',$idComprobante)->first();
            $detalles = DB::table('detalles')->where('comprobante_id',$idComprobante)->get();
            $comprobante->fecha = date("Y-m-d H:i:s"); 
            $comprobante->id = NULL;
            $lastComprobante = $comprobante->tipoDocumento_id;
            $comprobante->numDocfectado = $comprobante->serie . '-' . $comprobante->correlativo; 
            if($comprobante->tipoDocumento_id == '01'){
                $comprobante->serie = 'FNC1';
                $comprobante->tipoDocumento_id = '07';
                $comprobante->correlativo =$this->getSerie($comprobante->empresa_id,'FNC1');
            }
            if($comprobante->tipoDocumento_id == '03'){
                $comprobante->serie = 'BNC1';
                $comprobante->tipoDocumento_id = '07';
                $comprobante->correlativo = $this->getSerie($comprobante->empresa_id,'BNC1');
            }
            $comprobante = (array) $comprobante;
            //dd($detalles);
            DB::beginTransaction();
            $edit = DB::table('comprobante')->where('id', $idComprobante)->update([ 'estado' => 0]);
            $rest = DB::table('comprobante')->insertGetId($comprobante);
            foreach ($detalles->toArray() as  $value ) {
                $value->id = NULL;
                $value->comprobante_id = $rest;
                $detalles = DB::table('detalles')->insertGetId((array) $value);
            }
            DB::commit();

            $nota = Comprobante::with(['empresa' => function ($query) {
                $query->where('estado', 1);
            }, 'detalles.servicio', 'persona'])
            ->where('id', $rest)->first();
            $nota->tipDocAfectado = $lastComprobante;
            if ($nota->empresa) {
                $comprobanteController = new ComprobanteController();
                $comprobanteController->enviarComprobanteAPI($nota);
            }

            $response = [ 'status'=> true, 'data' => $rest];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            DB::rollBack();
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function getSerieFactura($idEmpresa,$serie){
        $serie = DB::select("select (correlativo + 1) as correlativo from comprobante   where empresa_id = ". $idEmpresa." and  serie = '".$serie."' and estado = 1 order by correlativo desc  limit 1");
        if(count($serie) > 0){
            return $serie[0]->correlativo;
        }else{
            return 1;
        }
    }
    public function getSerieBoleta($idEmpresa,$serie){
        $serie = DB::select("select (correlativo + 1) as correlativo from comprobante   where empresa_id = ". $idEmpresa." and  serie = '".$serie."' and estado = 1 order by correlativo desc  limit 1");
        if(count($serie) > 0){
            return $serie[0]->correlativo;
        }else{
            return 1;
        }
    }
}
