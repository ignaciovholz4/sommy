<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Stock - {{ $sucursal->nombre }}</title>
    <style>
        .borders { border: #b2b2b2 1px solid; }
        .letter { font-size: 12px; font-family: Helvetica; font-weight: bold; margin-top: 4px; }
        .letter2 { font-size: 12px; font-family: Helvetica; }
    </style>
</head>
<body>
    <div class="row" style="margin-top:3px;text-align:center;">
        <h3 style="font-family: Helvetica;font-weight: bold;">
            STOCK — {{ strtoupper($sucursal->nombre) }}
        </h3>
        <p style="font-family: Helvetica;font-size: 11px;color:#555;">Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="row" style="margin-top:3px;">
        <table class="table table-bordered" style="width:100%;">
            <thead class="borders">
                <tr>
                    <th class="borders letter" style="width: 35%">ARTÍCULO</th>
                    <th class="borders letter" style="width: 15%">COMBINACIÓN</th>
                    <th class="borders letter" style="width: 15%">CÓDIGO</th>
                    <th class="borders letter text-center" style="width: 12%">STOCK ACTUAL</th>
                    <th class="borders letter text-center" style="width: 11%">MÍN.</th>
                    <th class="borders letter" style="width: 12%">UBICACIÓN</th>
                </tr>
            </thead>
            <tbody class="borders">
                @forelse ($filas as $row)
                    <tr>
                        <td class="borders letter2">{{ $row['Artículo'] }}</td>
                        <td class="borders letter2">{{ $row['Combinación'] }}</td>
                        <td class="borders letter2">{{ $row['Código'] }}</td>
                        <td class="borders text-center letter2">{{ rtrim(rtrim(number_format($row['Stock actual'], 2, ',', '.'), '0'), ',') }}</td>
                        <td class="borders text-center letter2">{{ $row['Stock mínimo'] !== '' ? $row['Stock mínimo'] : '—' }}</td>
                        <td class="borders letter2">{{ $row['Ubicación'] !== '' ? $row['Ubicación'] : '—' }}</td>
                    </tr>
                @empty
                    <tr><td class="borders letter2" colspan="6" style="text-align:center;">Sin artículos cargados en esta sucursal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
