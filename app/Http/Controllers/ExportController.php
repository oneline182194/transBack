<?php

namespace App\Http\Controllers;
use PDF;
use DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use QrCode;

class ExportController extends Controller
{
    public function getComprobante($id){

        try{

        $comprobante = DB::table('comprobante')->where('comprobante.id',$id)->join('personas as pe','pe.id','=','comprobante.personas_id')->join('empresas as em','em.id','=','comprobante.empresa_id')->get();
        if($comprobante[0]->tipo == 1){
            $comprobante[0]->detalles = DB::table('detalles as d')->where('d.comprobante_id',$id)
                                                                    ->join('servicios as s','s.id','=','d.servicios_id')
                                                                    ->join('pasajes as p','p.id','=','d.pasaje_id')
                                                                    ->select('d.*','s.descripcion','p.asiento')
                                                                    ->get();
        }else{
            $comprobante[0]->detalles = DB::table('detalles')->where('comprobante_id',$id)->get();
        }

        $gravado = (floatval ($comprobante[0]->monto) / 1.18);
        $comprobante[0]->gravado = round( $gravado, 4, PHP_ROUND_HALF_EVEN);
        $comprobante[0]->igv = $comprobante[0]->monto -  $comprobante[0]->gravado;

        $qrcode = base64_encode(QrCode::format('svg')->size(130)->errorCorrection('H')->generate('string'));
        $customPaper = array(0,0,170.07,600);
        $pdf = PDF::loadView('documents.comprobante', ['data'=> $comprobante[0],'qrcode'=> $qrcode])->setPaper($customPaper);
        return $pdf->stream();

        }catch(Exception $e){
            $response = [ 'status'=> true, 'mensaje' => substr($e->errorInfo[2], 54), 'code' => $e->getCode()];
            $codeResponse = 500;
        }
    }
}
