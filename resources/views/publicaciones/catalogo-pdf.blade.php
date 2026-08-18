<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Catálogo Sommy</title>
<style>
    @page { margin: 90px 36px 60px 36px; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #1B2B5A; font-size: 11px; }

    header {
        position: fixed; top: -70px; left: 0; right: 0; height: 56px;
        border-bottom: 2px solid #E0F2FE; padding-bottom: 8px;
    }
    header .logo { height: 40px; }
    header .head-right { float: right; text-align: right; padding-top: 6px; }
    header .head-right .tit { font-size: 15px; font-weight: bold; letter-spacing: .04em; }
    header .head-right .sub { font-size: 9.5px; color: #47536F; }

    footer {
        position: fixed; bottom: -42px; left: 0; right: 0; height: 30px;
        border-top: 1px solid #E7EAF2; padding-top: 6px;
        font-size: 9px; color: #47536F; text-align: center;
    }
    footer .firma { color: #2563EB; }

    .grid { width: 100%; border-collapse: separate; border-spacing: 10px; }
    .card {
        width: 50%; background: #F8FAFC; border: 1px solid #E7EAF2;
        border-radius: 12px; padding: 14px; vertical-align: top;
    }
    .card .foto-box { text-align: center; height: 150px; }
    .card .foto-box img { max-width: 100%; max-height: 150px; }
    .card .nombre { font-size: 13px; font-weight: bold; margin: 10px 0 4px; }
    .card .specs { font-size: 9.5px; color: #47536F; line-height: 1.55; }
    .card .precio-row { margin-top: 8px; }
    .card .precio { font-size: 17px; font-weight: bold; color: #1B2B5A; }
    .card .precio-old { font-size: 10px; color: #47536F; text-decoration: line-through; }
    .card .off {
        display: inline-block; background: #E0F2FE; color: #2563EB; border-radius: 999px;
        font-size: 9px; font-weight: bold; padding: 2px 8px; margin-left: 5px;
    }
    .card .desc { font-size: 9px; color: #47536F; margin-top: 6px; line-height: 1.5; }
</style>
</head>
<body>
    <header>
        @if(is_file($logo))<img class="logo" src="{{ $logo }}">@endif
        <div class="head-right">
            <div class="tit">CATÁLOGO {{ $fecha }}</div>
            <div class="sub">Directo de fábrica · Envío a domicilio</div>
        </div>
    </header>

    <footer>
        Sommy · Somos fabricantes: comprás directo de fábrica, sin intermediarios. &nbsp;·&nbsp;
        <span class="firma">Liviano como una pluma.</span>
    </footer>

    <table class="grid">
        @foreach($productos->chunk(2) as $fila)
        <tr>
            @foreach($fila as $p)
            <td class="card">
                <div class="foto-box">
                    @if($p['imagen_local'])<img src="{{ $p['imagen_local'] }}">@endif
                </div>
                <div class="nombre">{{ $p['nombre'] }}</div>
                <div class="specs">
                    @if($p['tipo']) {{ $p['tipo'] }}@if($p['pillow']) · Pillow top @endif<br>@endif
                    @if($p['plazas']){{ $p['plazas'] }}@endif
                    @if($p['firmeza']) · Firmeza {{ strtolower($p['firmeza']) }}@endif
                    @if($p['altura']) · {{ $p['altura'] }} cm de altura @endif<br>
                    @if($p['garantia']){{ $p['garantia'] }} años de garantía @endif
                    @if($p['noches']) · {{ $p['noches'] }} noches de prueba @endif
                </div>
                @if($conPrecio)
                <div class="precio-row">
                    @if($p['descuento'] > 0)
                        <span class="precio-old">${{ number_format($p['precio'], 0, ',', '.') }}</span><br>
                    @endif
                    <span class="precio">${{ number_format($p['precioFinal'], 0, ',', '.') }}</span>
                    @if($p['descuento'] > 0)<span class="off">-{{ round($p['descuento']) }}%</span>@endif
                </div>
                @endif
                @if($p['descripcion'])
                <div class="desc">{{ \Illuminate\Support\Str::limit($p['descripcion'], 140) }}</div>
                @endif
            </td>
            @endforeach
            @if($fila->count() === 1)<td style="border:none;"></td>@endif
        </tr>
        @endforeach
    </table>
</body>
</html>
