<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>comprobante</title>
    <style>
        
       *{ 
           margin:0px;
           padding:0px;
           font-size:10px;
           font-family: 'Lucida', sans-serif;;
        }
       table{
           margin:15px auto 15px auto;
           width: 95%;
       }
    </style>
</head>
<body>
    <table >
        <thead>
            <tr>
                <th colspan="5">
                    <h3 style="font-size:14px;padding-bottom: 12px">{{ $data->razonSocial }}</h3>
                </th>
            </tr>
            <tr>
                <th colspan="5">RUC : {{ $data->RUC }}</th>
            </tr>
            <tr>
                <th colspan="5">{{ $data->direccion }}</th>
            </tr>
            <tr>
                <th colspan="5">Tel: {{ $data->telefono }}</th> 
            </tr>
            <tr>
                <th colspan="5" style="padding-bottom: 12px">Email: {{ $data->correo }}</th>
            </tr>
            <tr>
                <th colspan="5" style="border-top: 2px black dashed;padding:12px 0px 12px 0px;font-size:13px;">
                @if($data->tipoDocumento_id == '20')
                    RESERVA
                @endif
                @if($data->tipoDocumento_id == '30')
                    PASE
                @endif
                @if($data->tipoDocumento_id == '10')
                    RECIBO
                @endif
                @if($data->tipoDocumento_id == '01')
                    FACTURA DE VENTA ELECTRONICA
                @endif
                @if($data->tipoDocumento_id == '03')
                    BOLETA DE VENTA ELECTRONICA
                @endif
                </th>
            </tr>
            <tr>
                <th colspan="5" style="padding:0px 0px 12px 0px;font-size:12px">{{ $data->serie }}</th>            
            </tr>
            <tr>
                <th colspan="2" style="text-align:right">Fecha Em.: </th>
                <th colspan="3" style="text-align:left"> {{ $data->fecha }}</th>
            </tr>
            <tr>
                <th colspan="2" style="text-align:right">Cliente:</th>
                <th colspan="3" style="text-align:left"> {{ $data->nombres }} {{ $data->paterno }}</th>
            </tr>
            <tr >
                @if($data->tipoDocumento_id == '01')
                <th colspan="2" style="text-align:right">RUC:</th>
                @else
                <th colspan="2" style="text-align:right">DNI:</th>
                @endif
                <th colspan="3" style="text-align:left;margin-bottom:15px"> {{ $data->documento }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" style="border-bottom: 1px black dashed;margin:8px 0px 0px 0px"><br></td>
            </tr>
            <tr>
                <td width="20">Cant.</td>
                <td colspan="2">Descripcion</td>
                <td width="25">Prec.</td>
                <td width="25">Imp.</td>
            </tr>
            <tr>
                <td colspan="5" style="border-top: 1px black dashed;padding:4px 0px 0px 0px"></td>
            </tr>
            @foreach ($data->detalles as $det)
            <tr>
                <td>{{ $det->cantidad }}</td>
                <td colspan="2"><b>(A: {{ $det->asiento }})</b> {{ $det->descripcion }}</td>
                <td>{{ $det->precio }}</td>
                <td>{{ $det->subtotal }}</td>
            </tr>
            @endforeach
            
            <tr>
                <td colspan="5" style="border-top: 1px black dashed;padding:4px 0px 0px 0px"></td>
            </tr>
           
            <tr>
                <td colspan="5" style="text-align:right">
                    <p>Gravada: S/ {{ number_format($data->gravado,2) }}</p>
                    <p>IGV(18.00%): S/ {{ number_format($data->igv,2) }}</p>
                    <p>Descuento Total: S/ {{ number_format($data->descuento,2) }}</p>
                    <p>Total Pagar: S/ {{ number_format($data->monto,2) }}</p>
                </td>
            </tr>
            </tr>
            
            <tr>
                <td><br><br></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:center;">
                    <img src="data:image/png;base64, {!! $qrcode !!}">
                </td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:center;padding-top:12px">
                    <b>Gracias por su preferencia.</b>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>