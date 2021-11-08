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
    public function listUsers(){
        $list = DB::table('users')->get();
        $response = [ 'status'=> true, 'data' =>  $list];
        return response()->json( $response, 200 );
    }
    public function saveConductor(Request $request){
        try {
            if($request->id){
                $data = DB::table('personas')->where('id',$request->id)->update(['documento' => $request->dni,'paterno' => $request->paterno,'materno' => $request->materno,'nombres' => $request->nombres,'sexo'=> $request->sexo]);
            }else{
                $data = DB::table('personas')->insertGetId(['documento' => $request->dni,'paterno' => $request->paterno,'materno' => $request->materno,'nombres' => $request->nombres,'sexo'=> $request->sexo]);
                $personal = DB::table('personal')->insertGetId(['estado' => 1,'personas_id' => $data]);
            }
            $response = [ 'status'=> true, 'data' =>  $data];
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
    public function buscarCliente($tipo,$documento, $licencia = null){
        try {
            $getCliente = DB::table('personas')->where('documento',$documento)->get();
            if(count($getCliente) > 0){
                $dataCliente = $getCliente[0];
                $dataCliente->nombresCompletos = $dataCliente->nombres .' '. $dataCliente->paterno .' '. $dataCliente->materno;
            }else{
                if($tipo == '01'){ 
                    $jsonString = file_get_contents("https://dniruc.apisperu.com/api/v1/ruc/".$documento."?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im9uZWxpbmUxODIxQGhvdG1haWwuY29tIn0.KrjVnfvICYxsRcmPR7sTdYQXIoiCRgJTyR2u3pjyDOw");
                    $setCliente = (array) json_decode($jsonString,true);
                    $dataCliente = $this->savePerson($tipo, $documento, $setCliente);
                }else{ 

                    if($licencia){
                        $jsonString = file_get_contents("https://dniruc.apisperu.com/api/v1/dni/".$documento."?token=$licencia");
                        $setCliente = (array) json_decode($jsonString,true );
                        $dataCliente = $this->savePerson($tipo, $documento, $setCliente,1);
                    }else{
                        $jsonString = file_get_contents("https://consulta.api-peru.com/api/dni/".$documento);
                        $setCliente = (array) json_decode($jsonString,true );
                        $setCliente = $setCliente['data'];
                        $dataCliente = $this->savePerson($tipo, $documento, $setCliente,2);
                    }
                }
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
    public function savePerson($tipo, $documento, $persona, $type){
        if($tipo == '01'){
            $setPersona = [ 'documento' => $documento, 'nombres' => addslashes($persona['razonSocial']) ,'paterno' => '', 'materno' => '', 'direccion' => addslashes($persona['direccion'])];
        }else{
            if($type == 1){
                $setPersona = [ 'documento' => $documento, 'paterno' => $persona['apellidoPaterno'], 'materno' => $persona['apellidoMaterno'], 'nombres' => $persona['nombres'], 'direccion' => '' ];
            }else{
                $setPersona = [ 'documento' => $documento, 'nombres' => $persona['nombre_completo'],'paterno' => '' , 'materno' => '',  'direccion' => '' ];
            }
        }
        $persona_id = DB::table('personas')->insertGetId($setPersona);
        $getCliente = ['id' => $persona_id,'documento'=>$documento, 'nombres' => $setPersona['nombres'] .' '. $setPersona['paterno'] . ' '. $setPersona['materno'], 'direccion'=> $setPersona['direccion'] ?? null ];
        return $getCliente;
    }
    public function editarEmpresa(Request $request){
        $data = [
            'RUC' => $request->RUC,
            'color' => $request->color,
            'correo' => $request->correo,
            'direccion' => $request->direccion,
            'estado' => $request->estado,
            'gerente' => $request->gerente,
            'razonSocial' => $request->razonSocial,
            'serieBoleta' => $request->serieBoleta,
            'serieFactura' => $request->serieFactura,
            'telefono' => $request->telefono
        ];
        try {
            $data = DB::table('empresas')->where('id', $request->id)->update($data);
            $response = [ 'status'=> true, 'data' => $data];
            $codeResponse = 200;
        }catch(\fException $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function deleteVehiculo($id){
        try{
            DB::beginTransaction(); 
            $edit = DB::table('personal')->where('id', $id)->update([ 'estado' => 0]);
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
    public function getEmpresas(){
        try{
            $data = DB::table('empresas')->get();
            $response = [ 'status'=> true, 'data' => $data];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
    }
    public function saveServicio(Request $request){
        try{
            $data = [
                'descripcion'=>$request->descripcion,
                'monto' => $request->pu,
                'tipoServicio_id' => 6
            ];
            $returns = DB::table('servicios')->insertGetId($data);
            $response = [ 'status'=> true, 'data' => $returns];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function getLicencias(){
        try{
            $data = DB::table('licencias')->get();
            $response = [ 'status'=> true, 'data' => $data];
            $codeResponse = 200;
        }catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
}
