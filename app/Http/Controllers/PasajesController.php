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
}
