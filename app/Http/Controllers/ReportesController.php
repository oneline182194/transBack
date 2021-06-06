<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ReportesController extends Controller
{
    public function reporteDocumentos(Request $request){
        try{
            $registros = DB::select("CALL reporteComprobante('$request->empresa','$request->comprobante',$request->year,$request->month)");
            $response = [ 'status'=> true, 'data' => $registros];
            $codeResponse = 200;
        }catch(Exceptions $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
        return response()->json( $response, $codeResponse );
    }
}
