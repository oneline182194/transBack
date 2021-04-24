<?php

namespace App\Http\Controllers;
use Consulta\Laravel\Consulta;
use Illuminate\Http\Request;
use DB;

class GeneralController extends Controller
{
    public function saveEmpresa(Request $request){
        try {
            $data = DB::table('empresas')->insertGetId(['razonSocial' => $request->razonSocial,'RUC' => $request->RUC,'gerente' => $request->gerente,'estado' => 1]);
            $response = [ 'status'=> true, 'data' =>  $data];
            $codeResponse = 200;
        } catch (\fException $e) {

            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function listEmpresas(){
        $list = DB::table('empresas')
                        ->orderby('razonSocial','asc')
                        ->get();
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function saveConductor(Request $request){
        try {
            $data = DB::table('personas')->insertGetId(['documento' => $request->dni,'paterno' => $request->paterno,'materno' => $request->materno,'nombres' => $request->nombres,'sexo'=> $request->sexo]);
            $personal = DB::table('personal')->insertGetId(['estado' => 1,'personas_id' => $data]);
            $response = [ 'status'=> true, 'data' =>  $personal];
            $codeResponse = 200;
        } catch (\fException $e) {

            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function saveVehiculo(Request $request){
        try {
            $newVehiculo = [
                'anio' => $request->anio,
                'color' => $request->color,
                'descripcion' =>  $request->descripcion,
                'empresas_id' => $request->empresa_id,
                'modelo_id' => $request->modelo_id,
                'personas_id'=> $request->personas_id,
                'placa'=> $request->placa
            ];
            $data = DB::table('movil')->insertGetId($newVehiculo);
            $response = [ 'status'=> true, 'data' =>  $data];
            $codeResponse = 200;
        } catch (\fException $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function listConductores(){
        $list = DB::select('CALL listaConductores()');
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function listServicios(){
        $list = DB::table('servicios') ->get();
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function listComprobantes(){
        $list = DB::table('tipodocumento')->where('estado', '1')->get();
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function listModelos(){
        $list = DB::table('modelo') ->get();
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function buscarCliente($tipo,$documento){
        try {
            $getCliente = DB::table('personas')->where('documento',$documento)->get();
            if(count($getCliente) > 0){
                $dataCliente = $getCliente[0];
                $dataCliente->nombresCompletos = $dataCliente->nombres .' '. $dataCliente->paterno .' '. $dataCliente->materno;
            }else{
                $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.MTYzMA.Z91bggUHVslRNsIRNi38ATsWKVqst0ZLeHjbHc3bN_4';
                if($tipo == '01'){ 
                    $jsonString = file_get_contents("https://dniruc.apisperu.com/api/v1/ruc/".$documento."?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im9uZWxpbmUuZnJlZWxhbmNlckBnbWFpbC5jb20ifQ.SZjMhu8PV9BNBtbhFFa2VRtZ_UJwB9Z07ZB85WWYRcE");
                    $setCliente = (array) json_decode($jsonString,true);
                }else{ 
                    $jsonString = file_get_contents("https://dniruc.apisperu.com/api/v1/dni/".$documento."?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im9uZWxpbmUuZnJlZWxhbmNlckBnbWFpbC5jb20ifQ.SZjMhu8PV9BNBtbhFFa2VRtZ_UJwB9Z07ZB85WWYRcE");
                    $setCliente = (array) json_decode($jsonString,true );
                }
                $dataCliente = $this->savePerson($tipo, $documento, $setCliente);
                $dataCliente['nombresCompletos'] = addslashes($dataCliente['nombres']);
            }
            $response = [ 'status'=> true, 'data' => $dataCliente];
            $codeResponse = 200;
        } catch (\fException $e) {
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function savePerson($tipo, $documento, $persona){
        if($tipo == '01'){
            $setPersona = [ 'documento' => $documento, 'nombres' => $persona['razonSocial'] ,'paterno' => '', 'materno' => ''];
        }else{
            $setPersona = [ 'documento' => $documento, 'paterno' => $persona['apellidoPaterno'], 'materno' => $persona['apellidoMaterno'], 'nombres' => $persona['nombres'] ];
        }
        $persona_id = DB::table('personas')->insertGetId($setPersona);
        $getCliente = ['id' => $persona_id,'documento'=>$documento, 'nombres' => $setPersona['nombres'] .' '. $setPersona['paterno'] . ' '. $setPersona['materno']];
        return $getCliente;
    }
}
