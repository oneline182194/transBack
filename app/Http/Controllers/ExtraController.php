<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
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
            'nota' => '-',
            'estado' => 1,
            'tipo' => 3,
            'tipoDocumento_id' => $request->comprobante,
            'empresa_id' => $request->empresa_id,
            'send' => 0,
            'correlativo' => $this->getSerie($request->empresa_id,$request->serie),
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

        }catch(Exceptions $e){
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
    public function listaRegistros($page){
        try{
            $comprobantes = DB::table('comprobante')->where('tipo',3)->join('personas as p','p.id','=','comprobante.personas_id')->orderBy('id', 'desc')->select('comprobante.*','p.documento','p.nombres','p.paterno')->get();
            $response = [ 'status'=> true, 'data' => $comprobantes];
            $codeResponse = 200;
        }catch(Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
}
