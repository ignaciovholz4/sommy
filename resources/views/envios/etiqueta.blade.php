<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Etiqueta {{ $referencia }}</title>
<style>
    /* Etiqueta térmica 100 x 150 mm (10x15) */
    @page { size: 100mm 150mm; margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { background: #fff; }
    body { font-family: Arial, Helvetica, sans-serif; color: #000; }

    .etiqueta {
        width: 100mm; height: 150mm; padding: 5mm;
        display: flex; flex-direction: column;
        page-break-after: always; overflow: hidden;
    }

    .cabecera { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1.2mm solid #000; padding-bottom: 2mm; }
    .marca { font-size: 8mm; font-weight: 900; letter-spacing: .5mm; }
    .ref { font-size: 5.5mm; font-weight: 900; }

    .bloque { margin-top: 3mm; }
    .rotulo { font-size: 3mm; font-weight: 700; text-transform: uppercase; letter-spacing: .3mm; }
    .destinatario { border: .6mm solid #000; border-radius: 2mm; padding: 3mm; margin-top: 1.5mm; }
    .nombre { font-size: 6.5mm; font-weight: 900; line-height: 1.15; }
    .dir { font-size: 5mm; font-weight: 700; line-height: 1.3; margin-top: 1.5mm; }
    .tel { font-size: 4.5mm; margin-top: 1.5mm; }

    .fila-datos { display: flex; gap: 2mm; margin-top: 3mm; }
    .dato-caja { flex: 1; border: .4mm solid #000; border-radius: 2mm; padding: 2mm 2.5mm; }
    .dato-caja .l { font-size: 2.8mm; font-weight: 700; text-transform: uppercase; }
    .dato-caja .v { font-size: 4.5mm; font-weight: 900; }

    .notas { margin-top: 3mm; font-size: 3.4mm; line-height: 1.35; }
    .pie { margin-top: auto; border-top: .4mm solid #000; padding-top: 2mm; font-size: 3mm; display: flex; justify-content: space-between; }

    @media screen {
        body { background: #94A3B8; display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 16px; }
        .etiqueta { background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,.3); }
        .no-print { font-family: Arial; font-size: 13px; background: #fff; border-radius: 999px; padding: 8px 20px; border: none; cursor: pointer; font-weight: 700; }
    }
    @media print { .no-print { display: none; } }
</style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨 Imprimir etiqueta</button>

    @for($i = 0; $i < $copias; $i++)
    <div class="etiqueta">
        <div class="cabecera">
            <span class="marca">{{ $remitente }}</span>
            <span class="ref">{{ $referencia }}</span>
        </div>

        <div class="bloque">
            <div class="rotulo">Destinatario</div>
            <div class="destinatario">
                <div class="nombre">{{ $cliente }}</div>
                <div class="dir">
                    {{ $direccion_envio ?: ($direccion ?: 'Dirección a coordinar') }}
                    @if(!$direccion_envio)
                        @if($localidad)<br>{{ $localidad }}@endif{{ $provincia ? ($localidad ? ', ' : '') . $provincia : '' }}
                        @if($cp) (CP {{ $cp }})@endif
                    @endif
                </div>
                @if($telefono)<div class="tel">📞 {{ $telefono }}</div>@endif
            </div>
        </div>

        <div class="fila-datos">
            <div class="dato-caja">
                <div class="l">Bultos</div>
                <div class="v">{{ $bultos ?: 1 }}</div>
            </div>
            <div class="dato-caja">
                <div class="l">Flete</div>
                <div class="v" style="font-size:3.6mm;">{{ $transportista ?: 'A asignar' }}</div>
            </div>
            <div class="dato-caja">
                <div class="l">Paga envío</div>
                <div class="v" style="font-size:3.6mm;">{{ $pagado_por === 'empresa' ? 'Remitente' : 'Destinatario' }}</div>
            </div>
        </div>

        @if($tracking || $notas)
        <div class="notas">
            @if($tracking)<b>Tracking:</b> {{ $tracking }}<br>@endif
            @if($notas)<b>Obs:</b> {{ \Illuminate\Support\Str::limit($notas, 160) }}@endif
        </div>
        @endif

        <div class="pie">
            <span>{{ $remitente }} · Directo de fábrica</span>
            <span>{{ now()->format('d/m/Y') }}</span>
        </div>
    </div>
    @endfor

    <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
</body>
</html>
