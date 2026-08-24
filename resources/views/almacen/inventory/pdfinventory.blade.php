<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inventario de Productos</title>
    <style>
        .borders {
            border: #b2b2b2 1px solid;
        }
        .letter {
            font-size: 13px;
            font-family: Helvetica;
            font-weight: bold;
            margin-top: 4px;
        }
        .letter2 {
            font-size: 13px;
            font-family: Helvetica;
        }
        hr {
            height: 4px;
            background-color: black;
        }
    </style>
</head>
<body>
    <div class="row" style="margin-top:3px;text-align: center;">
        <h3 class="text-success border-2 border-bottom text-center" style="font-family: Helvetica;font-weight: bold;">
            LISTA DE PRODUCTOS EN EXISTENCIA
        </h3>
    </div>
    <div class="row" style="margin-top:3px;">
        <table class="table table-bordered">
            <thead class="borders">
                <tr>
                    <th class="borders letter th" style="width: 9%">CÓDIGO</th>
                    <th class="borders letter th" style="width: 20%">NOMBRE</th>
                    <th class="borders letter th" style="width: 15%">DESCRIPCIÓN</th>
                    <th class="borders letter th" style="width: 8%">TIPO</th>
                    <th class="borders letter th" style="width: 7%">STOCK</th>
                    <th class="borders letter th" style="width: 10%">PRECIO COMPRA S/IVA</th>
                    <th class="borders letter th" style="width: 10%">PRECIO VENTA S/IVA</th>
                    <th class="borders letter th" style="width: 7%">MARGEN</th>
                    <th class="borders letter th" style="width: 7%">IVA COMPRA</th>
                    <th class="borders letter th" style="width: 7%">IVA VENTA</th>
                </tr>
            </thead>
            <tbody class="borders">
                @foreach ($productos as $row)
                    <tr>
                        <td class="borders letter2">{{ $row['Codigo'] }}</td>
                        <td class="borders letter2">{{ $row['Nombre'] }}</td>
                        <td class="borders letter2">{{ $row['Descripcion'] }}</td>
                        <td class="borders text-center letter2">{{ $row['Tipo producto'] }}</td>
                        <td class="borders text-center letter2">{{ rtrim(rtrim(number_format($row['Stock'], 2), '0'), '.') }}</td>
                        <td class="borders text-center letter2">${{ number_format($row['Precio compra sin IVA'], 2) }}</td>
                        <td class="borders text-center letter2">${{ number_format($row['Precio venta sin IVA'], 2) }}</td>
                        <td class="borders text-center letter2">{{ $row['Margen %'] !== null ? $row['Margen %'] . '%' : '—' }}</td>
                        <td class="borders text-center letter2">{{ $row['IVA compra'] }}</td>
                        <td class="borders text-center letter2">{{ $row['IVA venta'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>