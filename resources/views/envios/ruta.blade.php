@extends('layouts.admin')

@section('title', 'Hoja de ruta')

@section('contenido')
<style>
    .hr-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1000px; margin: 0 auto; }
    .hr-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .hr-title { font-size: 21px; font-weight: 600; margin: 8px 0 2px; }
    .hr-sub { font-size: 13px; color: #6E7A96; font-weight: 300; margin-bottom: 14px; }

    .hr-filtros { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 16px; background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 16px; }
    .hr-filtros label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6E7A96; display: block; margin-bottom: 3px; }
    .hr-filtros input, .hr-filtros select { border: 1px solid #E7EAF2; border-radius: 10px; padding: 8px 12px; font-size: 13.5px; color: #1B2B5A; }
    .hr-btn { border: none; border-radius: 999px; padding: 9px 20px; font-size: 13px; font-weight: 500; cursor: pointer; background: #1B2B5A; color: #fff; text-decoration: none; display: inline-block; }
    .hr-btn:hover { background: #2563EB; color: #fff; }
    .hr-btn.verde { background: #0d8a4f; }
    .hr-btn.sec { background: #E0F2FE; color: #1B2B5A; }

    .hr-resumen { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
    .hr-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 12px; padding: 9px 18px; font-size: 12px; color: #6E7A96; }
    .hr-kpi b { display: block; font-size: 17px; color: #1B2B5A; }

    .hr-parada { display: flex; gap: 14px; background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 13px 16px; margin-bottom: 10px; align-items: flex-start; }
    .hr-num { width: 34px; height: 34px; border-radius: 999px; background: #1B2B5A; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .hr-parada.sincoords .hr-num { background: #b4552d; }
    .hr-datos { flex: 1; }
    .hr-cliente { font-weight: 600; font-size: 14.5px; }
    .hr-dir { font-size: 13px; color: #47536F; }
    .hr-meta { font-size: 11.5px; color: #6E7A96; margin-top: 2px; }
    .hr-meta a { color: #0d8a4f; text-decoration: none; }
    .hr-tramo { font-size: 11px; color: #2563EB; font-weight: 600; white-space: nowrap; }
    .hr-mapa { font-size: 12px; color: #2563EB; text-decoration: none; white-space: nowrap; }
    .hr-firma { display: none; }
    .hr-vacio { text-align: center; color: #94A3B8; padding: 40px; background: #fff; border-radius: 14px; border: 1px solid #E7EAF2; }

    @media print {
        .dg-header-top, .dg-nav-bar, .hr-filtros, .hr-volver, .no-print, .main-footer { display: none !important; }
        .hr-wrap { padding: 0; max-width: 100%; }
        .hr-parada { break-inside: avoid; border: 1px solid #999; }
        .hr-firma { display: block; font-size: 11px; color: #444; border-top: 1px dashed #999; margin-top: 8px; padding-top: 4px; width: 220px; }
        .hr-mapa { display: none; }
    }
</style>

<div class="hr-wrap">
    <a href="{{ url('envios') }}" class="hr-volver"><i class="fas fa-arrow-left"></i> Tablero de envíos</a>
    <div class="hr-title"><i class="fas fa-route" style="color:#2563EB;"></i> Hoja de ruta — {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div>
    <div class="hr-sub">
        Salida: <b>{{ $deposito['direccion'] }}</b> · Paradas ordenadas por cercanía (la más conveniente primero).
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ url('envios/ruta') }}" class="hr-filtros">
        <div>
            <label>Día de reparto</label>
            <input type="date" name="fecha" value="{{ $fecha }}">
        </div>
        <div>
            <label>Fletero</label>
            <select name="transportista_id">
                <option value="">Todos</option>
                @foreach($transportistas as $t)
                    <option value="{{ $t->id }}" {{ (string) $transportistaId === (string) $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Sin fecha asignada</label>
            <select name="sin_fecha">
                <option value="1" {{ $incluirSinFecha ? 'selected' : '' }}>Incluir</option>
                <option value="0" {{ !$incluirSinFecha ? 'selected' : '' }}>Ocultar</option>
            </select>
        </div>
        <button type="submit" class="hr-btn"><i class="fas fa-sync-alt"></i> Armar ruta</button>
        @if($paradas->isNotEmpty())
            <a href="{{ $urlMaps }}" target="_blank" class="hr-btn verde"><i class="fas fa-map-marked-alt"></i> Abrir ruta en Google Maps</a>
            <button type="button" class="hr-btn sec" onclick="window.print()"><i class="fas fa-print"></i> Imprimir lista</button>
        @endif
    </form>

    {{-- Resumen --}}
    @if($paradas->isNotEmpty())
    <div class="hr-resumen">
        <div class="hr-kpi">Paradas <b>{{ $paradas->count() }}</b></div>
        <div class="hr-kpi">Recorrido estimado <b>{{ $kmTotal }} km</b></div>
        <div class="hr-kpi">Fletero <b>{{ $transportistaId ? optional($transportistas->firstWhere('id', (int) $transportistaId))->nombre : 'Todos' }}</b></div>
    </div>
    @endif

    {{-- Paradas en orden --}}
    @forelse($paradas as $i => $e)
    <div class="hr-parada {{ (!$e->lat || !$e->lng) ? 'sincoords' : '' }}">
        <div class="hr-num">{{ $i + 1 }}</div>
        <div class="hr-datos">
            <div class="hr-cliente">{{ $e->parada_cliente }} <span style="color:#94A3B8;font-weight:400;font-size:12px;">· {{ $e->parada_referencia }}</span></div>
            <div class="hr-dir"><i class="fas fa-map-marker-alt" style="color:#b4552d;"></i> {{ $e->parada_direccion ?: 'SIN DIRECCIÓN — completar antes de salir' }}</div>
            <div class="hr-meta">
                @if($e->parada_telefono)<a href="https://wa.me/{{ preg_replace('/\D/', '', $e->parada_telefono) }}" target="_blank"><i class="fab fa-whatsapp"></i> {{ $e->parada_telefono }}</a> · @endif
                {{ optional($e->transportista)->nombre ?: 'Sin fletero' }}
                @if(!$e->fecha_entrega_estimada) · <span style="color:#b4552d;font-weight:600;">sin fecha asignada</span>@endif
                @if($e->notas) · {{ \Illuminate\Support\Str::limit($e->notas, 60) }}@endif
            </div>
            <div class="hr-firma">Recibió: ______________________ Hora: ______</div>
        </div>
        <div style="text-align:right;">
            @if(isset($e->distancia_tramo))<div class="hr-tramo">+{{ $e->distancia_tramo }} km</div>@endif
            @if($e->lat && $e->lng)
                <a class="hr-mapa" href="https://www.google.com/maps/search/?api=1&query={{ $e->lat }},{{ $e->lng }}" target="_blank"><i class="fas fa-map"></i> Ver en mapa</a><br>
            @elseif($e->parada_direccion)
                <span style="font-size:11px;color:#b4552d;">No se pudo ubicar<br>en el mapa</span><br>
            @endif
            <a class="hr-mapa" href="{{ $e->order_ecommerce_id ? url('etiqueta/pedido/' . $e->order_ecommerce_id) : url('etiqueta/venta/' . $e->venta_id) }}" target="_blank"><i class="fas fa-tag"></i> Etiqueta</a>
        </div>
    </div>
    @empty
    <div class="hr-vacio">
        No hay envíos activos para ese día{{ $transportistaId ? ' con ese fletero' : '' }}.<br>
        <small>Asigná fecha de entrega y fletero desde el tablero de Envíos y volvé a armar la ruta.</small>
    </div>
    @endforelse
</div>
@endsection
