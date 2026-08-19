@extends('layouts.admin')

@section('title', 'Notificaciones')

@section('contenido')
<style>
    .nt-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 850px; margin: 0 auto; }
    .nt-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
    .nt-title { font-size: 21px; font-weight: 600; }
    .nt-btn { border: none; border-radius: 999px; padding: 8px 18px; font-size: 12.5px; font-weight: 500; cursor: pointer; background: #E0F2FE; color: #1B2B5A; }
    .nt-item {
        display: flex; gap: 13px; background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        padding: 13px 16px; margin-bottom: 8px; text-decoration: none; color: #1B2B5A;
        box-shadow: 0 5px 16px rgba(27,43,90,.05); transition: transform .12s ease;
    }
    .nt-item:hover { transform: translateX(3px); color: #1B2B5A; text-decoration: none; }
    .nt-item.noleida { background: #F0F7FF; border-color: #BFDBFE; }
    .nt-ico { font-size: 22px; flex-shrink: 0; }
    .nt-tit { font-weight: 600; font-size: 14px; }
    .nt-msj { font-size: 12.5px; color: #47536F; }
    .nt-meta { font-size: 11px; color: #94A3B8; margin-top: 3px; }
    .nt-meta .rev { color: #2563EB; font-weight: 600; }
    .nt-nivel { margin-left: auto; align-self: flex-start; border-radius: 999px; font-size: 9.5px; font-weight: 700; padding: 2px 10px; text-transform: uppercase; flex-shrink: 0; }
    .nt-nivel.alerta { background: #FEF3C7; color: #92400E; }
    .nt-nivel.exito { background: #DCFCE7; color: #166534; }
    .nt-nivel.info { background: #E0F2FE; color: #1B2B5A; }
    .nt-vacio { text-align: center; color: #94A3B8; padding: 50px; background: #fff; border-radius: 14px; border: 1px solid #E7EAF2; }
</style>

<div class="nt-wrap">
    <div class="nt-head">
        <div class="nt-title"><i class="fas fa-bell" style="color:#F59E0B;"></i> Notificaciones del negocio</div>
        <button class="nt-btn" onclick="fetch('{{ url('notificaciones/leidas') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => location.reload())">
            <i class="fas fa-check-double"></i> Marcar todas como leídas
        </button>
    </div>

    @forelse($notificaciones as $n)
    <a href="{{ url('notificaciones/' . $n->id . '/ir') }}" class="nt-item {{ $n->leida_at ? '' : 'noleida' }}">
        <span class="nt-ico">{{ \App\Models\Notificacion::ICONOS[$n->tipo] ?? '🔔' }}</span>
        <span style="min-width:0;">
            <span class="nt-tit">{{ $n->titulo }}</span><br>
            @if($n->mensaje)<span class="nt-msj">{{ $n->mensaje }}</span><br>@endif
            <span class="nt-meta">{{ $n->created_at->locale('es')->diffForHumans() }} · {{ $n->created_at->format('d/m/Y H:i') }} @if($n->url)· <span class="rev">Revisar →</span>@endif</span>
        </span>
        <span class="nt-nivel {{ $n->nivel }}">{{ $n->nivel }}</span>
    </a>
    @empty
    <div class="nt-vacio">Todavía no hay notificaciones.<br><small>Acá van a aparecer los pedidos nuevos, ventas, entregas, devoluciones y alertas de stock.</small></div>
    @endforelse
</div>
@endsection
