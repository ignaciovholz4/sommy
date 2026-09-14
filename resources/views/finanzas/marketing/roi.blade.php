@extends('layouts.admin')

@section('title', 'ROI de Meta Ads')

@section('contenido')
<style>
    .fin-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1250px; margin: 0 auto; }
    .fin-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .fin-vacio { color: #6E7A96; font-weight: 300; font-size: 13.5px; text-align: center; padding: 40px 0; }
    .fin-roi-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6E7A96; border-bottom: 2px solid #E7EAF2; }
    .fin-roi-table td, .fin-roi-table th { padding: 10px 12px; }
    .fin-roi-table tbody tr:hover { background: #F7F8FC; }
    .badge-roas-bueno { background: #E7F7EE; color: #0d8a4f; }
    .badge-roas-malo { background: #FDECEC; color: #b4552d; }
</style>

<div class="fin-wrap">
    <div class="fin-title">
        <div><i class="fas fa-bullseye" style="color:#2563EB;"></i> ROI de Meta Ads por campaña</div>
        <a href="{{ route('finanzas.marketing.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver al gasto
        </a>
    </div>

    <div class="fin-card">
        @if($campanas->isEmpty())
            <div class="fin-vacio">
                Todavía no hay datos de campañas sincronizados. Corré "Sincronizar ahora" desde el panel de gasto
                una vez que haya campañas activas en Meta Ads.
            </div>
        @else
            <div class="table-responsive">
                <table class="table fin-roi-table mb-0">
                    <thead>
                        <tr>
                            <th>Campaña</th>
                            <th class="text-end">Gasto</th>
                            <th class="text-end">Ventas</th>
                            <th class="text-end">Pedidos</th>
                            <th class="text-end">ROAS</th>
                            <th class="text-end">CAC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campanas as $c)
                        <tr>
                            <td>{{ $c->nombre_campana ?: $c->meta_campaign_id }}</td>
                            <td class="text-end">{{ format_money_global($c->gasto) }}</td>
                            <td class="text-end">{{ format_money_global($c->ventas_totales) }}</td>
                            <td class="text-end">{{ $c->cantidad_ventas }}</td>
                            <td class="text-end">
                                @if($c->roas !== null)
                                    <span class="badge {{ $c->roas >= 1 ? 'badge-roas-bueno' : 'badge-roas-malo' }}">{{ $c->roas }}x</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">{{ $c->cac !== null ? format_money_global($c->cac) : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted mt-3 mb-0" style="font-size:12.5px;">
                El cruce es por <code>utm_campaign</code>: usá el mismo nombre en el link del anuncio y en el nombre
                de la campaña de Meta para que el ROI se calcule bien. Ventas contadas: Pagado, Enviado y Entregado.
            </p>
        @endif
    </div>
</div>
@endsection
