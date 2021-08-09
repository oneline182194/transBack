<?php

namespace App\Alice;

class ApiManagerCurl
{
    private static function formatData($invoice)
    {
        return response()->json( $invoice);
        if($invoice->tipoDocumento_id === "01" || $invoice->tipoDocumento_id === "03" || $invoice->tipoDocumento_id === "07"){

            $cliTipoDoc = ( strlen($invoice->persona->documento) >= 11 ) ? "6" : "1"; 

            $usuario_sol = explode('#', $invoice->empresa->usuario_sol);

            $array = [
                "ublVersion" => "2.1",
                "serie" => $invoice->serie,
                "correlativo" => $invoice->correlativo,
                "fechaEmision" => explode(' ', $invoice->fecha)[0],
                "tipoDoc" => $invoice->tipoDocumento_id,
                "tipoOperacion" => "0101",
                "tipoMoneda" => "PEN",
                "seguridad" => [
                    "usuario_sol" => $usuario_sol[0],
                    "clave_sol" => $usuario_sol[1],
                    "passphrase" => $invoice->empresa->passphrase
                ],
                "company" => [
                    "ruc" => $invoice->empresa->RUC,
                    "address" => [
                        "codLocal" => "0000",
                        "codigoPais" => "PE"
                    ]
                ],
                "client" => [
                    "tipoDoc" => $cliTipoDoc,
                    "numDoc" => $invoice->persona->documento,
                    "rznSocial" => $invoice->persona->nombres
                ],
                "totalImpuestos" => $invoice->monto - $invoice->monto / 1.18,
                "mtoOperGravadas" => $invoice->monto / 1.18,
                "mtoIGV" => $invoice->monto - $invoice->monto / 1.18,
                "valorVenta" => $invoice->monto / 1.18,
                "subTotal" => $invoice->monto,
                "mtoImpVenta" => $invoice->monto,
            ];

            if ($invoice->tipoDocumento_id === "07") {
                $array['codMotivo'] = '01';
                $array['desMotivo'] = 'Anulación de Comprobante';
                $array['numDocfectado'] = $invoice->numDocfectado;
                $array['tipDocAfectado'] = $invoice->tipDocAfectado;
            }
            
            $detalles = [];
            for($i = 0; $i < count($invoice->detalles) ; $i++) {
                $detalle = [
                    "unidad" => "NIU",
                    "cantidad" => $invoice->detalles[$i]->cantidad,
                    "mtoValorVenta" => $invoice->detalles[$i]->subtotal / 1.18,
                    "mtoPrecioUnitario" => $invoice->detalles[$i]->subtotal / $invoice->detalles[$i]->cantidad,
                    "totalImpuestos" => $invoice->detalles[$i]->subtotal - $invoice->detalles[$i]->subtotal / 1.18,
                    "mtoBaseIgv" => $invoice->detalles[$i]->subtotal / 1.18,
                    "igv" => $invoice->detalles[$i]->subtotal - $invoice->detalles[$i]->subtotal / 1.18,
                    "porcentajeIgv" => 18,
                    "tipAfeIgv" => 10,
                    "descripcion" => $invoice->detalles[$i]->producto->nombre,
                    "mtoValorUnitario" => ($invoice->detalles[$i]->subtotal / 1.18) / $invoice->detalles[$i]->cantidad
                ];
                
                $detalles[] = $detalle;
            }
            
            $array["details"] = $detalles;

            return json_encode($array);
        }
       
    }
    
    public static function get($invoice)
    {
        $data = Self::formatData($invoice);
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://alice.monologic.ws/api/comprobantes/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . $invoice->empresa->token,
                "content-type: application/json"
            ),

        ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        
        if ($info['http_code'] == 200) {
            $curl_response = [ 'response' => $response, 'http_code' => $info['http_code']];
            curl_close($curl);
        } else {
            $curl_response = [ 'response' => $response, 'http_code' => $info['http_code'], 'error' => curl_error($curl) ?? ''];
            curl_close($curl);
        }

        return $curl_response;
    }
    
    public function downloadXML($numeracion, $tipoDoc, $empresa)
    {
        $data = [
            "numeracion" => $numeracion,
            "tipo_doc" => $tipoDoc,
        ];
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://alice.monologic.ws/api/files/downloadXML",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . $empresa->token,
            ),

        ));

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        if ($info['http_code'] == 200) {
            $filename = $empresa->emp_ruc . "-" . $data['tipo_doc'] . "-" . $data['numeracion'] . ".xml";
            $file = fopen($filename, "w+");
            fputs($file, $response);
            fclose($file);
            
            return $filename;
        } else {
            abort('No se pudo descargar', 400);
        }
    }
}