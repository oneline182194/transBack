<?php

namespace App\Http\Controllers;

use App\Alice\ApiManagerCurl;
use App\Models\Comprobante;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ComprobanteController extends Controller
{
    public function enviarSUNAT($date, $return = true)
    {
        set_time_limit(600);
        
        $fechaInicio = date("Y-m-d",strtotime($date." - 7 days")); 

        $invoices = Comprobante::with(['empresa' => function ($query) {
            $query->where('estado', 1);
        }, 'detalles.servicio', 'persona'])
        ->whereIn('tipoDocumento_id', ['01', '03'])
        ->whereBetween('fecha', [$fechaInicio . ' 00:00:00', $date . ' 23:59:59'])
        ->whereBetween('tipoDocumento_id', ['01', '03'])
        ->where('send', 0)
        ->where('estado', 1)
        ->get();

        foreach ($invoices as $invoice) {
            if ($invoice->empresa) {
                $this->enviarComprobanteAPI($invoice);
            }
        }

        if ($return) {
            return response()->json(['message' => 'Los comprobantes han sido enviados']);
        }
    }

    public function enviarComprobanteAPI($comprobante)
    {
        $response = ApiManagerCurl::get($comprobante);
        
        if($response['http_code'] == 200) {
            $result = json_decode($response['response'], true);
            if(!$result['success']) {
                if(isset($result['cdrResponse']) && $result['cdrResponse'] != null) {
                    $code = (int)$result['cdrResponse']['code'];
                } else {
                    $code = (int)$result['error']['code'];
                }
                if($code >= 2000 && $code <= 3999 ){
                    // Anular numeración
                }

                Log::error(($result['error']['message'] ?? 'CDR') . ' # Comprobante ID: ' . $comprobante->id. ' CODE: ' .$code);

            } else {
                DB::table('comprobante')->where('id', $comprobante->id)->update(['send' => 1]);
            }
        } else {
            // Error al comunicarse con API
            Log::error('Error al comunicarse con el API # Comprobante ID: ' . $comprobante->id);
        }
    }
}
