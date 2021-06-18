<?php

namespace App\Http\Controllers;
use PDF;
use DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use QrCode;
use Hashids\Hashids;

class ExportController extends Controller
{
    public function getComprobante($id){

        try{
            $hashids = new Hashids('',4,'1234567890ABCDEFGHIJKLMNOPQRSTU');
            $comprobante = DB::table('comprobante')
                                            ->where('comprobante.id',$id)
                                            ->join('personas as pe','pe.id','=','comprobante.personas_id')
                                            ->join('users as u','u.id','=','comprobante.user_id')
                                            ->join('empresas as em','em.id','=','comprobante.empresa_id')
                                            ->select('comprobante.*','pe.*','em.*','pe.direccion as dir','u.nombre as user')
                                            ->get();
            if($comprobante[0]->tipo == 1){
                $comprobante[0]->detalles = DB::table('detalles as d')->where('d.comprobante_id',$id)
                                                                        ->join('servicios as s','s.id','=','d.servicios_id')
                                                                        ->join('pasajes as p','p.id','=','d.pasaje_id')
                                                                        ->join('turnos as t','t.id','=','p.turnos_id')
                                                                        ->select('d.*','s.descripcion','p.asiento','t.hora')
                                                                        ->get();
            }else{
                $comprobante[0]->detalles = DB::table('detalles as d')->where('comprobante_id',$id)
                                                                        ->join('servicios as s','s.id','=','d.servicios_id')
                                                                        ->get();
            }
            if($comprobante[0]->tipo == 2){
                $envio = DB::table('envios')->where('comprobante_id',$id)->get();
                $comprobante[0]->envio = $envio;
                $comprobante[0]->code = $hashids->encode($envio[0]->id);
            }
            $gravado = (floatval ($comprobante[0]->monto) / 1.18);
            $comprobante[0]->gravado = round( $gravado, 4, PHP_ROUND_HALF_EVEN);
            $comprobante[0]->igv = $comprobante[0]->monto -  $comprobante[0]->gravado;
            //dd($comprobante[0]);
            $qrcode = base64_encode(QrCode::format('svg')->size(120)->errorCorrection('H')->generate('string'));
            $customPaper = array(0,0,170.07,600);
            $pdf = PDF::loadView('documents.comprobante', ['data'=> $comprobante[0],'qrcode'=> $qrcode])->setPaper($customPaper);
            return $pdf->stream();

        }catch(Exception $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
    }
    public function getComprobanteA4($id){

        try{
            $hashids = new Hashids('',4,'1234567890ABCDEFGHIJKLMNOPQRSTU');
            $comprobante = DB::table('comprobante')
                                            ->where('comprobante.id',$id)
                                            ->join('personas as pe','pe.id','=','comprobante.personas_id')
                                            ->join('users as u','u.id','=','comprobante.user_id')
                                            ->join('empresas as em','em.id','=','comprobante.empresa_id')
                                            ->select('comprobante.*','pe.*','em.*','pe.direccion as dir','u.nombre as user')
                                            ->get();
            if($comprobante[0]->tipo == 1){
                $comprobante[0]->detalles = DB::table('detalles as d')->where('d.comprobante_id',$id)
                                                                        ->join('servicios as s','s.id','=','d.servicios_id')
                                                                        ->join('pasajes as p','p.id','=','d.pasaje_id')
                                                                        ->join('turnos as t','t.id','=','p.turnos_id')
                                                                        ->select('d.*','s.descripcion','p.asiento','t.hora')
                                                                        ->get();
            }else{
                $comprobante[0]->detalles = DB::table('detalles as d')->where('comprobante_id',$id)
                                                                        ->join('servicios as s','s.id','=','d.servicios_id')
                                                                        ->get();
            }
            if($comprobante[0]->tipo == 2){
                $envio = DB::table('envios')->where('comprobante_id',$id)->get();
                $comprobante[0]->envio = $envio;
                $comprobante[0]->code = $hashids->encode($envio[0]->id);
            }
            $gravado = (floatval ($comprobante[0]->monto) / 1.18);
            $comprobante[0]->gravado = round( $gravado, 4, PHP_ROUND_HALF_EVEN);
            $comprobante[0]->igv = $comprobante[0]->monto -  $comprobante[0]->gravado;
            //dd($comprobante[0]);
            $qrcode = base64_encode(QrCode::format('svg')->size(120)->errorCorrection('H')->generate('string'));
            $customPaper = array(0,0,170.07,600);
            $pdf = PDF::loadView('documents.a4', ['data'=> $comprobante[0],'qrcode'=> $qrcode])->setPaper('A4');
            return $pdf->stream();

        }catch(Exception $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
    }
    public function exportarNomina($idTurno){
        $header = DB::select('CALL getTurno('.$idTurno.')');
        $turnos = DB::select('CALL listaAsientos2('. $idTurno .')');
        $pdf = PDF::loadView('documents.nomina', ['data'=> $header[0],'pasajeros'=> $turnos])->setPaper('A4');
        return $pdf->stream();
    }
}
