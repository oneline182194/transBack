<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class LoginController extends Controller
{
    public function signIn(Request $request){
        
        $data = DB::table('users')->where('user', $request->user)->get();
        if(count($data) > 0){
            $pass = hash('sha256', $request->password);
            if($data[0]->password == $pass && $data[0]->estado == 1){
                $data[0]->password = 'XXXX';
                $response = [ 'status'=> true, 'res'=> $data[0] ];
                $code = 200;
            }else{
                $response = [ 'status'=> false, 'res'=>'La constraseña ingresada es incorrecta'];
                $code = 400;
            }
            
        }else{
            $response = [ 'status'=> false, 'res'=>'El usuario ingresado no existe' ];
            $code = 400;
        }
        return response()->json($response,$code);
    }
    public function listUsers(Request $request){
        return response()->json( $request);
    }
    public function generateUserConduc(Request $request){
        $pass = hash('sha256', $request->user);
        $user = [
            'nombre' => $request->nombre,
            'user' => $request->user,
            'rang' => $request->rang,
            'password' => $pass,
            'estado' => 1
        ];
        try{
            DB::beginTransaction(); 
            $userId = DB::table('users')->insertGetId($user);
            $ret = DB::table('personal')->where('id',$request->personal_id)->update(['user' => $userId ]);
            DB::commit();
            $response = [ 'status'=> true, 'data' => $ret];
            $codeResponse = 200;

        } catch(\Exceptions $e){
            DB::rollBack();
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
    public function changePassword(Request $request){
        try{
            $pass = hash('sha256', $request->one);
            $respuesta = DB::table('users')->where('id', $request->user_id)->update(['password' => $pass]);
            $response = [ 'status'=> true, 'data' => $respuesta];
            $codeResponse = 200;

        } catch(\Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
}
