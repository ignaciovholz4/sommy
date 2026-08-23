<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 90px 36px 60px 36px; }
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1B2B5A; font-size: 11px; margin: 0; }

    header { position: fixed; top: -70px; left: 0; right: 0; height: 60px;
             background: #1B2B5A; color: #fff; padding: 14px 24px; }
    header .marca { font-size: 22px; font-weight: bold; letter-spacing: 1px; }
    header .sub { font-size: 10px; color: #9DB8E8; }
    header .fecha { position: absolute; right: 24px; top: 22px; font-size: 10px; color: #9DB8E8; }

    footer { position: fixed; bottom: -45px; left: 0; right: 0; height: 30px;
             text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px; }

    .categoria { background: #EAF1FB; color: #1B2B5A; font-weight: bold; font-size: 13px;
                 padding: 6px 12px; border-radius: 6px; margin: 14px 0 8px 0; }

    table.prod { width: 100%; border-collapse: collapse; margin-bottom: 6px; page-break-inside: avoid; }
    table.prod td { vertical-align: top; padding: 8px; border-bottom: 1px solid #eef2f7; }
    .foto { width: 90px; }
    .foto img { width: 82px; height: 82px; object-fit: cover; border-radius: 8px; }
    .sinfoto { width: 82px; height: 82px; background: #F1F4F9; border-radius: 8px;
               text-align: center; color: #94a3b8; font-size: 9px; padding-top: 34px; }
    .nombre { font-size: 13px; font-weight: bold; color: #1B2B5A; margin-bottom: 3px; }
    .descripcion { color: #64748b; font-size: 9.5px; margin-bottom: 4px; }
    table.variantes { border-collapse: collapse; }
    table.variantes td { border: none; padding: 1px 14px 1px 0; font-size: 11px; }
    .medida { color: #64748b; }
    .precio { color: #2563EB; font-weight: bold; white-space: nowrap; }
    .precio-unico { font-size: 15px; color: #2563EB; font-weight: bold; }
</style>
</head>
<body>
<header>
    <div class="marca">Sommy</div>
    <div class="sub">Lista de precios &middot; Venta minorista</div>
    <div class="fecha">Vigente al {{ $fecha }}<br>Precios en pesos, IVA incluido</div>
</header>

<footer>
    Sommy Colchones {{ $empresa->telefono ?? '' ? '· Tel: ' . $empresa->telefono : '' }} {{ ($empresa->email ?? '') ? '· ' . $empresa->email : '' }} — Precios sujetos a cambio sin previo aviso.
</footer>

@php $categoriaActual = null; @endphp
@foreach($productos as $p)
    @if(($p->categoria->nombre ?? 'Otros') !== $categoriaActual)
        @php $categoriaActual = $p->categoria->nombre ?? 'Otros'; @endphp
        <div class="categoria">{{ $categoriaActual }}</div>
    @endif

    <table class="prod">
        <tr>
            <td class="foto">
                @if($p->foto_local)
                    <img src="{{ $p->foto_local }}" alt="">
                @else
                    <div class="sinfoto">Sin foto</div>
                @endif
            </td>
            <td>
                <div class="nombre">{{ $p->nombre }}</div>
                @if($p->descripcion)
                    <div class="descripcion">{{ \Illuminate\Support\Str::limit(strip_tags($p->descripcion), 140) }}</div>
                @endif

                @if($p->combinaciones->isNotEmpty())
                    <table class="variantes">
                        @foreach($p->combinaciones as $v)
                            <tr>
                                <td class="medida">{{ $v->combinacion }}</td>
                                <td class="precio">${{ number_format((float) $v->pventa_variante, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <span class="precio-unico">${{ number_format((float) $p->pventa_con_iva, 0, ',', '.') }}</span>
                @endif
            </td>
        </tr>
    </table>
@endforeach
</body>
</html>
