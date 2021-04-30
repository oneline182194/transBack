<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomina</title>
    <style>
        .tc{
            text-align: center;
        }
        .tl{
            text-align: left !important;
        }
        .tr{
            text-align: right;
        }
        .table{
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        table, td, th {
            border: 1px solid black;
            font-size:12px;
            padding: 3px 3px 3px 5px
        }
        .mt{
            margin-top:15px
        }
    </style>
</head> 
<body>
    <h1 class="tc">{{ $data->razonSocial }}</h1>
    <h4 class="tc">{{ $data->RUC }}</h4>
    <p class="tc">
        <strong >{{ $data->direccion }}</strong><br>
        <strong>{{ $data->telefono }}</strong><br>
        <strong>{{ $data->correo }}</strong><br>
    </p>
    
    <table class="table">
        <thead>
            <tr>
                <th colspan="4" class="tl">Informacion de Conductor</th>
            </tr>
            <tr>
                <th width="70">Documentos</th>
                <td>{{ $data->documento }}</td>
                <th width="70">Conductor</th>
                <td>{{ $data->nombres }} {{ $data->paterno }} {{ $data->materno }}</td>
            </tr>
            <tr>
                <th colspan="4" class="tl">Informacion de Vehiculo</th>
            </tr>
            <tr>
                <th>Placa</th>
                <td>{{ $data->placa }}</td>
                <th>Modelo</th>
                <td>{{ $data->descripcion }}</td>
            </tr>
            <tr>
                <th>Año</th>
                <td>{{ $data->anio }}</td>
                <th>Color</th>
                <td>{{ $data->color }}</td>
            </tr>
        </thead>
    </table>
    <table class="table mt">
        <thead>
            <tr>
                <th width="20">#</th>
                <th width="60">DNI</th>
                <th>Nombres Completos</th>
                <th width="35">Asiento</th>
                <th width="35">Edad</th>
                <th>Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pasajeros as $key => $p)
            <tr>
                <td class="tc">{{ $key + 1 }}</td>
                <td class="tc">{{ $p->documento }}</td>
                <td class="tl">{{ $p->cliente }} {{ $p->clienteA }}</td>
                <td class="tc">{{ $p->asiento }}</td>
                <td></td>
                <td></td>
            </tr> 
            @endforeach
                  
        </tbody>
    </table>
</body>
</html>