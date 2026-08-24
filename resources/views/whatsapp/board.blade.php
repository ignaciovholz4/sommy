@extends('layouts.admin')

@section('title', 'Tablero de atención')

@section('contenido')
<style>
    .att-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; }
    .att-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
    .att-title { font-size: 21px; font-weight: 600; }
    .att-links { display: flex; gap: 8px; flex-wrap: wrap; }
    .att-link {
        border: 1.5px solid #E7EAF2; color: #47536F; background: #fff; border-radius: 999px;
        padding: 7px 16px; font-size: 12.5px; font-weight: 500; text-decoration: none;
    }
    .att-link:hover { border-color: #2563EB; color: #2563EB; text-decoration: none; }

    .att-board { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; align-items: start; }
    @media (max-width: 1300px) { .att-board { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767px)  { .att-board { grid-template-columns: 1fr; } }

    .att-col { background: #F1F4F9; border-radius: 16px; padding: 10px; }
    .att-col-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 8px 10px; font-size: 12.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em; color: #47536F;
    }
    .att-col-head .num {
        background: #1B2B5A; color: #fff; border-radius: 999px; min-width: 24px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center; font-size: 12px;
    }
    .att-col.c1 .att-col-head i { color: #b4552d; }
    .att-col.c2 .att-col-head i { color: #2563EB; }
    .att-col.c3 .att-col-head i { color: #0EA5E9; }
    .att-col.c4 .att-col-head i { color: #0d8a4f; }

    .att-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        box-shadow: 0 6px 18px rgba(27,43,90,.06); padding: 13px 14px; margin-bottom: 10px;
        cursor: pointer; transition: transform .12s ease, box-shadow .12s ease;
        display: block; color: inherit; text-decoration: none !important;
    }
    .att-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(27,43,90,.12); color: inherit; }
    .att-card .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; gap: 6px; }
    .att-card .nombre { font-weight: 700; font-size: 14px; color: #1B2B5A; display: flex; align-items: center; gap: 6px; min-width: 0; }
    .att-card .nombre span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .att-card .dato { font-size: 12.5px; color: #47536F; line-height: 1.55; }
    .att-card .dato i { width: 15px; text-align: center; color: #94A3B8; }
    .att-card .preview {
        font-size: 12px; color: #6E7A96; margin-top: 5px;
        overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    }
    .att-card .pie { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; gap: 6px; flex-wrap: wrap; }

    .att-chip { border-radius: 999px; font-size: 10.5px; font-weight: 600; padding: 3px 10px; white-space: nowrap; }
    .chip-bot     { background: #EDE9FE; color: #5B21B6; }
    .chip-pedido  { background: #FEF3C7; color: #92400E; }
    .chip-humano  { background: #DBEAFE; color: #1D4ED8; }
    .chip-tiempo  { background: #F8FAFC; color: #6E7A96; border: 1px solid #E7EAF2; font-weight: 500; }
    .chip-tiempo.viejo { background: #FEF2F2; color: #991B1B; border-color: #FECACA; }
    .att-unread {
        background: #1EBE5A; color: #fff; border-radius: 999px; font-size: 11px;
        min-width: 20px; height: 20px; display: inline-flex; align-items: center;
        justify-content: center; padding: 0 6px; font-weight: 700; flex-shrink: 0;
    }
    .att-vacio { text-align: center; color: #94A3B8; font-size: 12.5px; font-weight: 300; padding: 22px 8px; }
</style>

@php
    $iconos = [
        'whatsapp'  => '<i class="fab fa-whatsapp" style="color:#1EBE5A"></i>',
        'messenger' => '<i class="fab fa-facebook-messenger" style="color:#1877F2"></i>',
        'instagram' => '<i class="fab fa-instagram" style="color:#C13584"></i>',
    ];

    $card = function ($c) use ($iconos) {
        $nombre = optional($c->cliente)->nombre ?: ($c->profile_name ?: $c->phone_e164);
        $hace   = $c->last_message_at ? \Carbon\Carbon::parse($c->last_message_at)->diffForHumans(null, true) : null;
        $viejo  = $c->last_message_at && \Carbon\Carbon::parse($c->last_message_at)->lt(now()->subHours(4));
        return compact('nombre', 'hace', 'viejo');
    };
@endphp

<div class="att-wrap">
    <div class="att-head">
        <div class="att-title">
            <i class="fas fa-headset" style="color:#2563EB;"></i> Tablero de atención
        </div>
        <div class="att-links">
            <a class="att-link" href="{{ route('whatsapp.inbox') }}"><i class="fas fa-inbox"></i> Bandeja (chat)</a>
            @can('haveaccess','agents.index')
            <a class="att-link" href="{{ route('whatsapp.agents.index') }}"><i class="fas fa-robot"></i> Agentes IA</a>
            @endcan
        </div>
    </div>

    <div class="att-board">
        @foreach([
            ['clase' => 'c1', 'icono' => 'fa-bell',            'titulo' => 'Sin atender',        'items' => $sinAtender, 'vacio' => 'No hay clientes esperando. 🎉'],
            ['clase' => 'c2', 'icono' => 'fa-comments',        'titulo' => 'En atención',        'items' => $enAtencion, 'vacio' => 'Nadie siendo atendido ahora.'],
            ['clase' => 'c3', 'icono' => 'fa-hourglass-half',  'titulo' => 'Esperando cliente',  'items' => $esperando,  'vacio' => 'Sin respuestas pendientes del cliente.'],
            ['clase' => 'c4', 'icono' => 'fa-check-circle',    'titulo' => 'Cerradas (7 días)',  'items' => $cerradas,   'vacio' => 'Sin conversaciones cerradas esta semana.'],
        ] as $col)
        <div class="att-col {{ $col['clase'] }}">
            <div class="att-col-head">
                <span><i class="fas {{ $col['icono'] }}"></i> {{ $col['titulo'] }}</span>
                <span class="num">{{ $col['items']->count() }}</span>
            </div>

            @forelse($col['items'] as $c)
                @php $d = $card($c); @endphp
                <a class="att-card" href="{{ route('whatsapp.inbox') }}?conv={{ $c->id }}">
                    <div class="top">
                        <span class="nombre">{!! $iconos[$c->channel] ?? '' !!} <span>{{ $d['nombre'] }}</span></span>
                        @if($c->unread_count > 0)
                            <span class="att-unread">{{ $c->unread_count }}</span>
                        @endif
                    </div>
                    @if($c->phone_e164)
                        <div class="dato"><i class="fas fa-phone"></i> {{ $c->phone_e164 }}</div>
                    @endif
                    @if($c->assignedUser)
                        <div class="dato"><i class="fas fa-user-tie"></i> {{ $c->assignedUser->name }}</div>
                    @endif
                    @if($c->last_message_preview)
                        <div class="preview">{{ $c->last_message_preview }}</div>
                    @endif
                    <div class="pie">
                        <span class="att-chip {{ $c->mode === 'bot' ? 'chip-bot' : 'chip-humano' }}">
                            {{ $c->mode === 'bot' ? '🤖 Bot' : '👤 Humano' }}
                        </span>
                        @if($c->cliente_id && isset($conPedidoEnMarcha[$c->cliente_id]))
                            <span class="att-chip chip-pedido">🛒 Pedido en marcha</span>
                        @endif
                        @if($d['hace'])
                            <span class="att-chip chip-tiempo {{ $d['viejo'] && $col['clase'] !== 'c4' ? 'viejo' : '' }}">
                                <i class="far fa-clock"></i> hace {{ $d['hace'] }}
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="att-vacio">{{ $col['vacio'] }}</div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>
@endsection
