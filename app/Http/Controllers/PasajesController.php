<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PasajesController extends Controller
{
    public function saveTurnos(Request $request){
        try {
            $newVehiculo = [
                'hora' => $request->hora,
                'dia' => $request->dia,
                'personal_id' => $request->personal_id,
                'estado' => 1,
                'estadoTurno_id'=> 1,
                'origen' => $request->region
            ];
            $data = DB::table('turnos')->insertGetId($newVehiculo);
            $response = [ 'status'=> true, 'data' =>  $data];
            $codeResponse = 200;
        } catch (\fException $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function listTurnos($origen,$fecha){
        $list = DB::select('call listadoTurnos('.$origen.',"'.$fecha.'")');
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function savePasaje(Request $request){
        $comprobante = [
            'fecha' => $request->fecha,
            'serie' => $request->serie,
            'monto' => $request->precioTot,
            'personas_id' => $request->personas_id,
            'empresa_id' => $request->empresa_id,
            'correlativo' => $this->getSerie($request->empresa_id,$request->serie),
            'igv' => 0.00,
            'descuento' => 0.00,
            'nota' => $request->nota ?? null,
            'tipoDocumento_id' => $request->comprobante??null,
            'user_id' =>$request->user_id
        ];
        try{
            DB::beginTransaction(); 

            if( $request->personas_id == 1 ){
                $persona = [
                    'documento'=>$request->numero,
                    'nombres'=>$request->cliente,
                    'telefono'=>$request->telefono,
                ];
                $getPersona = DB::table('personas')->insertGetId($persona);
                $comprobante['personas_id'] = $getPersona;
            }else{
                $persona = [
                    'telefono'=>$request->telefono ?? null,
                    'direccion' => $request->direccion ?? null,
                ];
                $getPersona = DB::table('personas')->where('id', $request->personas_id)->update($persona);
                $getPersona = $request->personas_id;
            }
            $comprobante = DB::table('comprobante')->insertGetId($comprobante);
            foreach ($request->asiento as $key => $value) {
              
                $pasaje = DB::table('pasajes')->insertGetId([
                    'turnos_id' => $request->turno,
                    'personas_id' => $getPersona,
                    'fecha' => $request->fecha,
                    'asiento' => intval($value),
                    'personal_id' => intval($request->personal_id),
                    'comprobante_id' => $comprobante,
                    'users_id' => $request->user_id
                ]);
                $detalles = DB::table('detalles')->insertGetId([
                    'servicios_id' => $request->servicio_id,
                    'pasaje_id' => intval($pasaje),
                    'cantidad' => 1,
                    'precio' => $request->precioUni,
                    'subtotal' => $request->precioUni,
                    'comprobante_id' => $comprobante
                ]);
            }
            DB::commit();
            $response = [ 'status'=> true, 'data' => $pasaje];
            $codeResponse = 200;

        } catch(\Exceptions $e){
            DB::rollBack();
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function getAsientos($turnoId){
        try{
            $turnos = DB::select('CALL listaAsientos('. $turnoId .')');
            $response = [ 'status'=> true, 'data' => $turnos];
            $codeResponse = 200;
        }catch(\Exception $e){
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
    public function anularPasaje($idComprobante){
        try{
            DB::beginTransaction(); 
            $edit = DB::table('comprobante')->where('id', $idComprobante)->update([ 'estado' => 0]);
            DB::commit();
            $response = [ 'status'=> true, 'data' => $edit];
            $codeResponse = 200;

        } catch(\Exceptions $e){
            DB::rollBack();
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
}
