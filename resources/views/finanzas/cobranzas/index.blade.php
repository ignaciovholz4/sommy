@extends('layouts.admin')

@section('title', 'Cobranzas')

@section('contenido')
<style>
    .cob-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .cob-title { font-size: 21px; font-weight: 600; margin-bottom: 4px; }
    .cob-sub { font-size: 13px; color: #6E7A96; margin-bottom: 18px; }

    .cob-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; margin-bottom: 14px;
    }
    .cob-card .top { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; }
    .cob-cliente { font-weight: 700; font-size: 15px; }
    .cob-meta { font-size: 12px; color: #6E7A96; margin-top: 2px; }
    .cob-monto { font-size: 20px; font-weight: 700; color: #c0392b; text-align: right; }
    .cob-dias { font-size: 11.5px; color: #94A3B8; }

    .tier { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .tier.suave { background: #FEF3C7; color: #92400E; }
    .tier.firme { background: #FFEDD5; color: #9A3412; }
    .tier.urgente { background: #FEE2E2; color: #991B1B; }

    .cob-nota { background: #F1F4F9; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-top: 10px; color: #47536F; }
    .cob-tpl { font-size: 12.5px; color: #6E7A96; margin-top: 8px; }
    .cob-tpl b { color: #1B2B5A; }

    .cob-acciones { display: flex; gap: 8px; margin-top: 12px; }
    .cob-acciones button { border: none; border-radius: 999px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .btn-aprobar { background: #0d8a4f; color: #fff; }
    .btn-descartar { background: #F1F4F9; color: #47536F; }

    .cob-vacio { text-align: center; color: #94A3B8; padding: 40px 20px; }

    .cob-recientes-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .cob-recientes-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #6E7A96; padding: 8px 12px; border-bottom: 1px solid #E7EAF2; }
    .cob-recientes-table td { padding: 8px 12px; font-size: 13px; border-bottom: 1px solid #F1F4F9; }
    .badge-estado { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .badge-estado.enviado { background: #DCFCE7; color: #166534; }
    .badge-estado.descartado { background: #F1F4F9; color: #6E7A96; }
    .badge-estado.fallido { background: #FEE2E2; color: #991B1B; }
</style>

<div class="cob-wrap">
    <div class="cob-title">Cobranzas</div>
    <div class="cob-sub">
        Recordatorios armados automáticamente para clientes con deuda vencida. El agente NUNCA envía solo:
        revisá cada uno y aprobalo (se manda por WhatsApp con una plantilla aprobada) o descartalo.
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    @forelse($pendientes as $r)
        <div class="cob-card">
            <div class="top">
                <div>
                    <div class="cob-cliente">{{ $r->cliente->nombre }} {{ $r->cliente->paterno }}</div>
                    <div class="cob-meta">
                        <span class="tier {{ $r->tier }}">{{ $r->tier }}</span>
                        · {{ $r->dias_vencido }} días vencido
                        @if($r->cliente->telefono) · {{ $r->cliente->telefono }} @endif
                    </div>
                </div>
                <div>
                    <div class="cob-monto">${{ number_format($r->monto_vencido, 2, ',', '.') }}</div>
                </div>
            </div>

            @if($r->nota_interna)
                <div class="cob-nota">🤖 {{ $r->nota_interna }}</div>
            @endif

            <div class="cob-tpl">
                @if($r->template)
                    Se enviará la plantilla aprobada <b>{{ $r->template->name }}</b>: "{{ $r->template->body_text }}"
                @else
                    <span class="text-danger">La plantilla asignada ya no existe.</span>
                @endif
            </div>

            <div class="cob-acciones">
                <form method="POST" action="{{ route('finanzas.cobranzas.aprobar', $r->id) }}" onsubmit="return confirm('¿Enviar este recordatorio por WhatsApp a {{ $r->cliente->nombre }}?');">
                    @csrf
                    <button type="submit" class="btn-aprobar"><i class="fas fa-paper-plane"></i> Aprobar y enviar</button>
                </form>
                <form method="POST" action="{{ route('finanzas.cobranzas.descartar', $r->id) }}" onsubmit="return confirm('¿Descartar este recordatorio?');">
                    @csrf
                    <button type="submit" class="btn-descartar"><i class="fas fa-times"></i> Descartar</button>
                </form>
            </div>
        </div>
    @empty
        <div class="cob-card cob-vacio">No hay recordatorios pendientes de revisión por ahora.</div>
    @endforelse

    @if($recientes->isNotEmpty())
        <div class="cob-card">
            <div class="cob-cliente" style="margin-bottom:6px;">Historial reciente</div>
            <table class="cob-recientes-table">
                <thead>
                    <tr><th>Cliente</th><th>Monto</th><th>Tier</th><th>Estado</th><th>Revisado por</th></tr>
                </thead>
                <tbody>
                    @foreach($recientes as $r)
                        <tr>
                            <td>{{ $r->cliente->nombre ?? '—' }}</td>
                            <td>${{ number_format($r->monto_vencido, 2, ',', '.') }}</td>
                            <td><span class="tier {{ $r->tier }}">{{ $r->tier }}</span></td>
                            <td><span class="badge-estado {{ $r->estado }}">{{ $r->estado }}</span></td>
                            <td>{{ $r->revisadoPor->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
