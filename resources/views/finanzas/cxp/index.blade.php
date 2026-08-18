@extends('layouts.admin')

@section('title', 'Cuentas por pagar')

@section('contenido')
<style>
    .cxp-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .cxp-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .cxp-title { font-size: 21px; font-weight: 600; }
    .cxp-kpis { display: flex; gap: 10px; flex-wrap: wrap; }
    .cxp-kpi {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        padding: 10px 22px; box-shadow: 0 10px 30px rgba(27,43,90,.06);
        text-align: right;
    }
    .cxp-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .cxp-kpi .v { font-size: 22px; font-weight: 700; color: #b4552d; }
    .cxp-kpi.vencido .v { color: #c0392b; }

    .cxp-buscar { display: flex; gap: 10px; margin-bottom: 16px; }
    .cxp-buscar input {
        flex: 1; max-width: 420px; border: 1px solid #E7EAF2; border-radius: 999px;
        padding: 10px 20px; font-size: 14px; color: #1B2B5A;
    }
    .cxp-buscar button { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 10px 24px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
    .cxp-buscar button:hover { background: #2563EB; }

    .cxp-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow-x: auto;
    }
    .cxp-table { width: 100%; border-collapse: collapse; }
    .cxp-table th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        padding: 11px 16px; text-align: left; white-space: nowrap;
    }
    .cxp-table td { padding: 12px 16px; border-bottom: 1px solid #F1F4F9; font-size: 14px; }
    .cxp-table tr:hover td { background: #F8FAFC; }
    .cxp-table .der { text-align: right; }
    .cxp-saldo-pos { color: #b4552d; font-weight: 700; }
    .cxp-saldo-cero { color: #0d8a4f; font-weight: 600; }
    .cxp-saldo-neg { color: #2563EB; font-weight: 600; }

    .cxp-semaforo {
        display: inline-flex; align-items: center; gap: 6px;
        border-radius: 999px; font-size: 11.5px; font-weight: 600; padding: 4px 12px; white-space: nowrap;
    }
    .cxp-semaforo .punto { width: 8px; height: 8px; border-radius: 50%; }
    .cxp-semaforo.rojo { background: #FDECEA; color: #c0392b; }
    .cxp-semaforo.rojo .punto { background: #c0392b; }
    .cxp-semaforo.amarillo { background: #FEF6E7; color: #9a6b0f; }
    .cxp-semaforo.amarillo .punto { background: #d99b1c; }
    .cxp-semaforo.verde { background: #DCFCE7; color: #166534; }
    .cxp-semaforo.verde .punto { background: #0d8a4f; }

    .cxp-ver {
        display: inline-block; border: 1.5px solid #1B2B5A; color: #1B2B5A;
        border-radius: 999px; padding: 5px 16px; font-size: 12.5px; font-weight: 500;
        text-decoration: none; white-space: nowrap;
    }
    .cxp-ver:hover { background: #1B2B5A; color: #fff; text-decoration: none; }
    .cxp-vacio { padding: 40px; text-align: center; color: #6E7A96; font-weight: 300; font-size: 14px; }
</style>

<div class="cxp-wrap">
    <div class="cxp-head">
        <div class="cxp-title"><i class="fas fa-file-invoice" style="color:#2563EB;"></i> Cuentas por pagar a proveedores</div>
        <div class="cxp-kpis">
            <div class="cxp-kpi vencido">
                <div class="l">Vencido</div>
                <div class="v">${{ number_format($totalVencido, 2, ',', '.') }}</div>
            </div>
            <div class="cxp-kpi">
                <div class="l">Deuda total</div>
                <div class="v">${{ number_format($totalDeuda, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ url('finanzas/cxp') }}" class="cxp-buscar">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar proveedor por nombre, email o teléfono para abrirle cuenta...">
        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
    </form>

    <div class="cxp-card">
        <table class="cxp-table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Contacto</th>
                    <th>Plazo</th>
                    <th>Vencimientos</th>
                    <th class="der">Debe</th>
                    <th class="der">Haber</th>
                    <th class="der">Saldo</th>
                    <th class="der"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($proveedores as $p)
                <tr>
                    <td style="font-weight:500;">{{ $p->nombre }}</td>
                    <td style="color:#6E7A96;font-size:13px;">{{ $p->telefono ?: $p->email }}</td>
                    <td style="color:#6E7A96;font-size:13px;">{{ $p->condicion_pago_dias > 0 ? $p->condicion_pago_dias . ' días' : 'Contado' }}</td>
                    <td>
                        @if($p->vencido > 0)
                            <span class="cxp-semaforo rojo"><span class="punto"></span> Vencido ${{ number_format($p->vencido, 2, ',', '.') }}</span>
                        @elseif($p->saldo > 0 && $p->por_vencer > 0)
                            <span class="cxp-semaforo amarillo"><span class="punto"></span> Por vencer ${{ number_format($p->por_vencer, 2, ',', '.') }}</span>
                        @else
                            <span class="cxp-semaforo verde"><span class="punto"></span> Al día</span>
                        @endif
                    </td>
                    <td class="der">${{ number_format($p->debe, 2, ',', '.') }}</td>
                    <td class="der">${{ number_format($p->haber, 2, ',', '.') }}</td>
                    <td class="der {{ $p->saldo > 0 ? 'cxp-saldo-pos' : ($p->saldo < 0 ? 'cxp-saldo-neg' : 'cxp-saldo-cero') }}">
                        ${{ number_format($p->saldo, 2, ',', '.') }}
                        @if($p->saldo < 0) <small>(a favor)</small> @endif
                    </td>
                    <td class="der"><a href="{{ url('finanzas/cxp/' . $p->idproveedor) }}" class="cxp-ver">Ver cuenta</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="cxp-vacio">
                    @if($q !== '')
                        No se encontraron proveedores con "{{ $q }}".
                    @else
                        Todavía no hay cuentas corrientes de proveedores con movimientos.<br>
                        <small>Buscá un proveedor arriba para abrirle la cuenta, o registrá una compra a crédito.</small>
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
