@extends('layouts.admin')

@section('title', 'Meta Ads / Google Ads')

@section('contenido')
<style>
    .fin-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1250px; margin: 0 auto; }
    .fin-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }

    .fin-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 767px) { .fin-kpis { grid-template-columns: 1fr; } }
    .fin-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 14px 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .fin-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .fin-kpi .v { font-size: 19px; font-weight: 700; margin-top: 2px; white-space: nowrap; }
    .fin-kpi .v.azul { color: #2563EB; }
    .fin-kpi .v.rojo { color: #b4552d; }

    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .fin-card h3 { font-size: 14px; font-weight: 600; margin: 0 0 12px; }
    .fin-card .chart-box { position: relative; height: 320px; }
    .fin-vacio { color: #6E7A96; font-weight: 300; font-size: 13.5px; text-align: center; padding: 40px 0; }
    .fin-aviso { background:#FEF6E7; color:#9a6b0f; border-radius:12px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
</style>

<div class="fin-wrap">
    <div class="fin-title">
        <div><i class="fab fa-facebook" style="color:#2563EB;"></i> Meta Ads / Google Ads</div>
        <button class="btn btn-sm btn-outline-primary" id="btnSincronizarAds"><i class="fas fa-rotate"></i> Sincronizar ahora</button>
    </div>

    @if(!$metaHabilitado || !$googleHabilitado)
    <div class="fin-aviso">
        <i class="fas fa-circle-info"></i>
        @if(!$metaHabilitado && !$googleHabilitado)
            Todavía no hay claves de Meta Ads ni Google Ads cargadas en <code>.env</code>.
        @elseif(!$metaHabilitado)
            Meta Ads todavía no está configurado (falta el access token o el ad account id).
        @else
            Google Ads todavía no está configurado (falta el developer token, OAuth o el customer id).
        @endif
    </div>
    @endif

    <div class="fin-kpis">
        <div class="fin-kpi">
            <div class="l">Meta Ads — este mes</div>
            <div class="v azul" id="kpiMeta">—</div>
        </div>
        <div class="fin-kpi">
            <div class="l">Google Ads — este mes</div>
            <div class="v azul" id="kpiGoogle">—</div>
        </div>
        <div class="fin-kpi">
            <div class="l">Total combinado — este mes</div>
            <div class="v rojo" id="kpiTotal">—</div>
        </div>
    </div>

    <div class="fin-card">
        <h3>Gasto diario — últimos 30 días</h3>
        <div class="chart-box"><canvas id="chartAdsSpend"></canvas></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const URL_FINANZAS = "{{ url('finanzas') }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function moneyFmt(n) {
    return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

let chartAds = null;

function cargarMarketing() {
    fetch(`${URL_FINANZAS}/marketing/data`)
        .then(res => res.json())
        .then(data => {
            $('#kpiMeta').text(moneyFmt(data.total_meta_mes));
            $('#kpiGoogle').text(moneyFmt(data.total_google_mes));
            $('#kpiTotal').text(moneyFmt(data.total_combinado_mes));

            const ctx = document.getElementById('chartAdsSpend');
            if (chartAds) chartAds.destroy();
            chartAds = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.dias,
                    datasets: [
                        { label: 'Meta', data: data.serie_meta, backgroundColor: '#2563EB', stack: 'ads', borderRadius: 3 },
                        { label: 'Google', data: data.serie_google, backgroundColor: '#0d8a4f', stack: 'ads', borderRadius: 3 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', align: 'end' } },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(110,122,150,.14)' } }
                    }
                }
            });
        });
}

$(document).ready(function () {
    $('#btnSincronizarAds').on('click', function () {
        const $btn = $(this).prop('disabled', true);
        fetch(`${URL_FINANZAS}/marketing/sincronizar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                $btn.prop('disabled', false);
                if (data.estado === 1) {
                    toastr.success(data.mensaje);
                    cargarMarketing();
                } else {
                    toastr.error(data.mensaje || 'No se pudo sincronizar.');
                }
            });
    });

    cargarMarketing();
});
</script>
@endsection
