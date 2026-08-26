@extends('layouts.admin')

@section('title', 'Historial de compra/venta de divisas')

@section('contenido')
<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <div>
            <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-exchange-alt" style="color:#2563EB;"></i> Historial de compra/venta de divisas</h4>
            <p class="text-muted small mb-0">Para comprar o vender, entrá a la cuenta en esa moneda desde <a href="{{ route('cuentas.index') }}">Gestor de Cuentas</a> — ahí la plata sale/entra de una cuenta real, no de la nada.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-sm" style="width:100%;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Moneda</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Cotización</th>
                        <th class="text-right">Total ARS</th>
                        <th>Cuentas</th>
                        <th class="text-right">Resultado</th>
                    </tr>
                </thead>
                <tbody id="divisas_body">
                    <tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const URL_FINANZAS = "{{ url('finanzas') }}";

function moneyFmt(n) {
    return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

function cargarDivisas() {
    $('#divisas_body').html('<tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>');
    fetch(`${URL_FINANZAS}/divisas/data`)
        .then(res => res.json())
        .then(data => {
            if (!data.estado || !data.data.length) {
                $('#divisas_body').html('<tr><td colspan="8" class="text-center text-muted py-4">Todavía no hay operaciones registradas.</td></tr>');
                return;
            }
            $('#divisas_body').html(data.data.map(filaOperacion).join(''));
        });
}

function filaOperacion(o) {
    const tipoBadge = o.tipo === 'compra'
        ? '<span class="badge badge-success">Compra</span>'
        : '<span class="badge badge-info">Venta</span>';
    let resultado = '—';
    if (o.resultado !== null) {
        const cls = o.resultado >= 0 ? 'text-success' : 'text-danger';
        resultado = `<span class="${cls} font-weight-bold">${moneyFmt(o.resultado)}</span>`;
    }
    // Sin cuenta ARS real: fue un pago/cobro directo de una compra o venta, no un cambio manual
    const cuentas = o.cuenta_ars
        ? `${esc(o.cuenta_ars)} ↔ ${esc(o.cuenta_moneda)}`
        : `<span class="text-muted">${esc(o.referencia || 'Pago/cobro directo')}</span> — ${esc(o.cuenta_moneda)}`;
    return `<tr>
        <td>${esc(o.fecha)}</td>
        <td>${tipoBadge}</td>
        <td>${esc(o.moneda)}</td>
        <td class="text-right">${Number(o.monto_moneda).toLocaleString('es-AR', {minimumFractionDigits:2})}</td>
        <td class="text-right">${moneyFmt(o.cotizacion)}</td>
        <td class="text-right">${moneyFmt(o.monto_ars)}</td>
        <td>${cuentas}</td>
        <td class="text-right">${resultado}</td>
    </tr>`;
}

$(document).ready(function () {
    cargarDivisas();
});
</script>
@endsection
