<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante</title>
    <style>
        *{
            font-family: Arial, Helvetica, sans-serif !important;
            text-align: justify;
            text-justify: inter-word;
        }
        .container{
            width:90%;
            margin:15px auto;
        }
        tr{
             font-family: Arial, Helvetica, sans-serif !important;
        }
        .table,th,td{
            border-collapse: collapse;
            border: 1px solid black;
        }
        @media print {
          thead {
            display: table-header-group;
          }
        }
    </style>
</head>
<body>
    <table class="table">
        <thead>
            <tr>
                <td colspan="8" style="text-align:left;padding:4px 10px">
                    <h3 style="text-align:center" style="font-size:21px;margin:0;padding:0">{{ $data->razonSocial }}</h3>
                    <p style="text-align:center" style="font-size:11px">RUC : {{ $data->RUC }}</p>

                    <p style="font-size:11px;padding:0;margin:0">
                        <span>{{ $data->direccion }}</span><br>
                        <b>Contacto: </b>{{ $data->telefono }}<br>
                        <b>Correo: </b>{{ $data->correo }}
                    </p>
                </td>
                <th colspan="4" style="text-align:center">
                    <strong>

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
                        FACTURA ELECTRONICA
                    @endif
                    @if($data->tipoDocumento_id == '03')
                        BOLETA ELECTRONICA
                    @endif
                    </strong><br><br>
                    <strong>{{ $data->serie }}-{{ $data->correlativo }}</strong><br>
                </th>
            </tr>
            <tr>
                <th colspan="3"  style="border-right:none">
                    <p style="font-size:11px;padding:1px 8px;margin:0">Fecha de Emisión</p>
                    <p style="font-size:11px;padding:1px 8px;margin:0">Cliente </p>
                    <p style="font-size:11px;padding:1px 8px;margin:0">Documento </p>
                    <p style="font-size:11px;padding:1px 8px;margin:0">Dirección </p>
                    <p style="font-size:11px;padding:1px 8px;margin:0">Tipo Moneda </p>
                    <p style="font-size:11px;padding:1px 8px;margin:0">Observación </p>
                </th>
                <td colspan="9" style="border-left:none">
                    <p style="font-size:11px;margin:0;padding:1px 0px">: &nbsp;&nbsp;{{ Carbon\Carbon::parse($data->fecha)->format('d / m / Y') }}</p>
                    <p style="font-size:11px;margin:0;padding:1px 0px">: &nbsp;&nbsp;{{ $data->nombres }} {{ $data->paterno }}</p>
                    <p style="font-size:11px;margin:0;padding:1px 0px">: &nbsp;&nbsp;{{$data->documento}} </p>
                    <p style="font-size:11px;margin:0;padding:1px 0px">: &nbsp;&nbsp;{{$data->dir}} </p>
                    <p style="font-size:11px;margin:0;padding:1px 0px">: &nbsp;&nbsp;Soles</p>
                    <p style="font-size:11px;margin:0;padding:1px 0px">: &nbsp;&nbsp;@if($data->nota)<span> {{$data->nota}}</span> @else <span>-</span> @endif </p>
                </td>
            </tr>
            <tr style="font-size:13px">
                <th width="30" style="text-align:center">Cant.</th>
                <th width="42" style="text-align:center">Uni/Med.</th>
                <th colspan="6">Descripción</th>
                <th  style="text-align:center" colspan="2">Valor Unit.</th>
                <th  style="text-align:center" colspan="2">Importe Venta</th>
            </tr>
        </thead>
        
        <tbody style="font-size:12px">
            @foreach($data->detalles as $s)
            <tr>
                <td style="text-align:center">{{$s->cantidad}}</td>
                <td style="text-align:center">UNIDAD</td>
                <td colspan="6" style="text-align:left;font-size:12px;padding:4px 7px;font-family: Arial, Helvetica, sans-serif;text-transform: uppercase;">
                    {!! $s->descripcion !!}
                </td>
                <td style="text-align:center" colspan="2">{{ number_format($s->precio,2)}}</td>
                
                <td style="text-align:center" colspan="2">{{ number_format($s->subtotal,2)}}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="8" rowspan="4">
                    <div style="display:inline-block">
                        <div style="display:inline-block">
                            <img width="80" style="margin:10px 15px;margin-top:40px" src="data:image/png;base64, {!! $qrcode !!}"><br>
                        </div>
                        <div style="display:inline-block;margin-top:-30px">
                            <span style="text-align:left;font-size:11px;padding:4px 12px">(*) Sin Impuestos</span><br>
                            <span style="text-align:left;font-size:11px;padding:4px 12px">(**) Incluye impuestos, de ser Op. Gravada </span><br>
                            <span style="text-align:left;font-size:11px;padding:4px 12px">Gracias por su Compra</span>
                        </div>
                    </div>
                </td>
                <td colspan="2" style="text-align:center">Op. Gravada</td>
                <td colspan="2" style="text-align:center">S/. {{ number_format($data->gravado,2) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center">IGV</td>
                <td colspan="2" style="text-align:center">S/. {{ number_format($data->igv,2) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center">Otros Cargos</td>
                <td colspan="2" style="text-align:center">S/. 0.00</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center">Importe Total</td>
                <td colspan="2" style="text-align:center">S/. {{ number_format($data->monto,2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="12">
                    <p style="text-align:left;font-size:11px;padding:4px 12px" >
                    Esta es una representacion impresa de la 
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
                    @endif, para consultar el documento visita : https://ww1.sunat.gob.pe/ol-ti-itconsultaunificadalibre/consultaUnificadaLibre/consulta 
                    </p>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>