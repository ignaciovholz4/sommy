<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto</title>
    <style>
        *{ font-family: Helvetica; }
        .row{ margin-top:3px; }
        .row h3{ background-color: #03569F; font-weight: bold; color:white; }
        .table{ width:100%; border-collapse: collapse; }
        .borders{ border: #b2b2b2 1px solid; }
        .th1, .th4{ width: 16%; text-align: left; }
        .th2{ width: 40%; text-align: left; }
        .th3, .th5{ width: 14%; text-align: left; }
        .left{ width: 50%; float: left; }
        .right{ width: 50%; float: left; text-align: right; }
        .header{ text-align: center; }
        .letter{ font-weight: bold; }
        .coti{ padding:3px; background-color: #03569F; font-weight: bold; color:white; }
    </style>
</head>
<body>
    <div class="row header">
        <h3>PRESUPUESTO</h3>
    </div>
    <div class="row">
        <div class="left">
            <p><span class="letter">Cliente: </span>{{ $cliente }}</p>
            <p><span class="letter">Dirección: </span>{{ $direccion }}</p>
            <p><span class="letter">Teléfono: </span>{{ $telefono }}</p>
            <p><span class="letter">Email: </span>{{ $email }}</p>
        </div>
        <div class="right">
            <p><span class="coti">Folio: {{ $folio }}</span></p>
            <p><span class="letter">Fecha: </span>{{ $fecha }}</p>
            <p><span class="letter">Estado: </span>{{ $estado }}</p>
        </div>
    </div>

    <div class="row">
        <p><b>Lista de artículos</b></p>
    </div>
    <div class="row">
        <table class="table">
            <thead class="borders">
                <tr>
                    <th class="th1 borders">Código</th>
                    <th class="th2 borders">Artículo</th>
                    <th class="th3 borders">Cantidad</th>
                    <th class="th4 borders">Precio Unitario</th>
                    <th class="th5 borders">Subtotal Neto</th>
                    <th class="th5 borders">Subtotal con IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalle as $row)
                <tr>
                    <td class="borders">{{ $row['codigo'] }}</td>
                    <td class="borders">{{ $row['nombre'] }}</td>
                    <td class="borders">{{ $row['cantidad'] }}</td>
                    <td class="borders">{{ $row['precio_unitario'] }}</td>
                    <td class="borders">{{ $row['subtotal_neto'] }}</td>
                    <td class="borders">{{ $row['subtotal_con_iva'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4"></td>
                    <td class="borders"><b>Total Neto</b></td>
                    <td class="borders">{{ $total_neto }}</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td class="borders"><b>IVA discriminado</b></td>
                    <td class="borders">
                        @foreach($iva_discriminado as $iva)
                            <p>IVA {{ $iva['porcentaje'] }}%: ${{ $iva['monto'] }}</p>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td class="borders"><b>Total con IVA</b></td>
                    <td class="borders">{{ $total_con_iva }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>