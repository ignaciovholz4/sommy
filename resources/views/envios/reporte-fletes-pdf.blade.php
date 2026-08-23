<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte de fletes</title>
<style>
    @page { margin: 90px 36px 60px 36px; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #1B2B5A; font-size: 10.5px; }

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

    .resumen {
        background: #F8FAFC; border: 1px solid #E7EAF2; border-radius: 10px;
        padding: 10px 14px; margin-bottom: 14px;
    }
    .resumen b { color: #1B2B5A; }

    .entrega {
        border: 1px solid #E7EAF2; border-radius: 12px; padding: 12px 14px;
        margin-bottom: 12px; page-break-inside: avoid;
    }
    .entrega .top { border-bottom: 1px solid #E7EAF2; padding-bottom: 6px; margin-bottom: 6px; }
    .entrega .ref { font-size: 13px; font-weight: bold; }
    .entrega .fecha { float: right; color: #47536F; font-size: 9.5px; }
    .entrega .dato { line-height: 1.6; }
    .entrega .dato b { color: #47536F; }

    .cobro { margin-top: 6px; background: #F8FAFC; border-radius: 8px; padding: 6px 10px; }
    .cobro .saldo-ok { color: #166534; font-weight: bold; }
    .cobro .saldo-pend { color: #991B1B; font-weight: bold; }

    .fotos { margin-top: 8px; }
    .fotos .foto-item { display: inline-block; width: 32%; text-align: center; margin-right: 1%; }
    .fotos .foto-item img { max-width: 100%; max-height: 110px; border: 1px solid #E7EAF2; border-radius: 6px; }
    .fotos .foto-item .cap { font-size: 8.5px; color: #47536F; margin-top: 2px; }

    .vacio { text-align: center; color: #94A3B8; padding: 40px 0; }
</style>
</head>
<body>
    <header>
        @if(is_file($logo))<img class="logo" src="{{ $logo }}">@endif
        <div class="head-right">
            <div class="tit">REPORTE DE FLETES</div>
            <div class="sub">{{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</div>
        </div>
    </header>

    <footer>
        Generado el {{ now()->format('d/m/Y H:i') }} · Sommy
    </footer>

    <div class="resumen">
        <b>{{ $filas->count() }}</b> entrega(s) en el período ·
        Cobrado: <b>${{ number_format($totalCobrado, 2, ',', '.') }}</b> ·
        Pendiente: <b>${{ number_format($totalPendiente, 2, ',', '.') }}</b>
    </div>

    @forelse($filas as $f)
    <div class="entrega">
        <div class="top">
            <span class="fecha">{{ optional($f['fecha_entrega'])->format('d/m/Y H:i') }}</span>
            <span class="ref">{{ $f['referencia'] }}</span>
        </div>

        <div class="dato"><b>Cliente:</b> {{ $f['cliente_nombre'] }}
            @if($f['cliente_telefono']) · {{ $f['cliente_telefono'] }} @endif
            @if($f['cliente_dni']) · DNI/CUIT {{ $f['cliente_dni'] }} @endif
        </div>
        <div class="dato"><b>Dirección de entrega:</b> {{ $f['direccion'] }}</div>
        <div class="dato"><b>Fletero:</b> {{ $f['fletero'] }}</div>

        <div class="cobro">
            Total: <b>${{ number_format($f['total'], 2, ',', '.') }}</b> ·
            Pagado (transferencias/cuenta): <b>${{ number_format($f['pagado'], 2, ',', '.') }}</b>
            @if($f['medios']) ({{ $f['medios'] }}) @endif
            @if(!is_null($f['cobrado_puerta']))
                · Cobrado en la puerta: <b>${{ number_format($f['cobrado_puerta'], 2, ',', '.') }}</b>
            @endif
            · Saldo: <span class="{{ $f['saldo'] > 0 ? 'saldo-pend' : 'saldo-ok' }}">${{ number_format($f['saldo'], 2, ',', '.') }}</span>
            @if($f['nota_entrega'])<br><b>Nota del fletero:</b> {{ $f['nota_entrega'] }}@endif
        </div>

        @if(count($f['fotos']))
        <div class="fotos">
            @foreach($f['fotos'] as $titulo => $ruta)
            <div class="foto-item">
                <img src="{{ $ruta }}">
                <div class="cap">{{ $titulo }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="vacio">Sin entregas en el período seleccionado.</div>
    @endforelse
</body>
</html>
