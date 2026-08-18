<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Código de Barras</title> 
    <style>
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
            height: 2px;
            background-color: black;
        }
        .div-content {
            width: 100%;
        }
        .table {
            width: 100%;
        }
        .borders {
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="row" style="margin-top:3px;text-align: center;">
        <h3 class="text-success border-2 border-bottom text-center" style="font-family: Helvetica;font-weight: bold;">
            CÓDIGO DE BARRAS DEL PRODUCTO
        </h3>
    </div>
    <div class="div-content" style="margin-top:3px;">
        <table class="table table-bordered">
            <tbody class="borders">
                @foreach($productsArray as $product)
                    <tr>
                        <td class="letter2">{{ $product['nombre'] }}</td>
                    </tr>
                    <tr>
                        <td>{!! $product['barcode'] !!}</td>
                    </tr>
                    <tr>
                        <td class="letter2">{{ $product['codigo'] }}</td>
                    </tr>
                    <tr>
                        <td class="letter2">
                            Precio venta s/IVA: $ {{ number_format($product['pventa_sin_iva'] ?? 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="letter2">
                            Precio venta c/IVA: $ {{ number_format($product['pventa_con_iva'] ?? 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="letter2">
                            Tipo: {{ ($product['tipo_producto_id'] ?? 1) == 1 ? 'Simple' : 'Personalizado' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="letter2">
                            Pesable: {{ !empty($product['articulo_pesable_balanza']) && $product['articulo_pesable_balanza'] ? 'Sí' : 'No' }}
                        </td>
                    </tr>
                    <hr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>