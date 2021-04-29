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

    }
}
