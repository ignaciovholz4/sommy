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
    .fin-mes-nav { display:flex; align-items:center; gap:10px; font-weight:600; }
    .fin-dias-row { background: #FAFBFD; }
    .fin-dias-chip { display:inline-flex; flex-direction:column; align-items:center; background:#fff; border:1px solid #E7EAF2; border-radius:8px; padding:6px 8px; margin:2px; font-size:11px; min-width:52px; }
    .fin-dias-chip b { font-size:12.5px; color:#1B2B5A; }
    .fin-toggle-dias { cursor:pointer; color:#2563EB; }
    .fin-aviso-pagar { background:#EAF2FF; color:#1B2B5A; border-radius:12px; padding:10px 14px; font-size:13px; margin-top:16px; }
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

    <div class="fin-card mt-3">
        <div class="fin-title" style="margin-bottom:12px;">
            <div>Gasto por día — {{ $mes->translatedFormat('F Y') }}</div>
            <div class="fin-mes-nav">
                <a href="{{ route('finanzas.marketing.roi', ['mes' => $mes->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></a>
                <a href="{{ route('finanzas.marketing.roi', ['mes' => $mes->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>

        @if($campanasDelMes->isEmpty())
            <div class="fin-vacio">Sin gasto sincronizado para este mes.</div>
        @else
            <div class="table-responsive">
                <table class="table fin-roi-table mb-0">
                    <thead>
                        <tr>
                            <th>Campaña</th>
                            <th class="text-end">Gasto del mes</th>
                            <th class="text-end">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campanasDelMes as $i => $c)
                        <tr>
                            <td>{{ $c->nombre_campana ?: $c->meta_campaign_id }}</td>
                            <td class="text-end">{{ format_money_global($c->gasto_mes) }}</td>
                            <td class="text-end">
                                <span class="fin-toggle-dias" data-bs-toggle="collapse" data-bs-target="#dias-{{ $i }}">
                                    Ver por día <i class="fas fa-chevron-down"></i>
                                </span>
                            </td>
                        </tr>
                        <tr class="collapse fin-dias-row" id="dias-{{ $i }}">
                            <td colspan="3">
                                @foreach($c->dias as $dia)
                                    <span class="fin-dias-chip">{{ $dia['fecha'] }}<b>{{ format_money_global($dia['spend']) }}</b></span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="fin-aviso-pagar">
            <i class="fas fa-circle-info"></i> El gasto de Ads se refleja automáticamente como un <strong>Gasto pendiente</strong>
            (categoría "Publicidad (Ads)") una vez por mes y por plataforma — pagalo desde
            <a href="{{ route('finanzas.gastos.index') }}" target="_blank">Finanzas &gt; Gastos</a> con la cuenta/caja que corresponda,
            igual que cualquier otro gasto.
        </div>
    </div>
</div>
@endsection
