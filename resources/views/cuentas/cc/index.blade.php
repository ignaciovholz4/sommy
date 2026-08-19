@extends('layouts.admin')

@section('title', 'Cuentas corrientes')

@section('contenido')
<style>
    .cc-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1100px; margin: 0 auto; }
    .cc-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .cc-title { font-size: 21px; font-weight: 600; }
    .cc-deuda {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        padding: 10px 22px; box-shadow: 0 10px 30px rgba(27,43,90,.06);
        text-align: right;
    }
    .cc-deuda .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .cc-deuda .v { font-size: 22px; font-weight: 700; color: #b4552d; }

    .cc-buscar { display: flex; gap: 10px; margin-bottom: 16px; }
    .cc-buscar input {
        flex: 1; max-width: 420px; border: 1px solid #E7EAF2; border-radius: 999px;
        padding: 10px 20px; font-size: 14px; color: #1B2B5A;
    }
    .cc-buscar button { border: none; background: #1B2B5A; color: #fff; border-radius: 999px; padding: 10px 24px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
    .cc-buscar button:hover { background: #2563EB; }

    .cc-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden;
    }
    .cc-table { width: 100%; border-collapse: collapse; }
    .cc-table th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        padding: 11px 16px; text-align: left;
    }
    .cc-table td { padding: 12px 16px; border-bottom: 1px solid #F1F4F9; font-size: 14px; }
    .cc-table tr:hover td { background: #F8FAFC; }
    .cc-table .der { text-align: right; }
    .cc-saldo-pos { color: #b4552d; font-weight: 700; }
    .cc-saldo-cero { color: #0d8a4f; font-weight: 600; }
    .cc-saldo-neg { color: #2563EB; font-weight: 600; }
    .cc-ver {
        display: inline-block; border: 1.5px solid #1B2B5A; color: #1B2B5A;
        border-radius: 999px; padding: 5px 16px; font-size: 12.5px; font-weight: 500;
        text-decoration: none;
    }
    .cc-ver:hover { background: #1B2B5A; color: #fff; }
    .cc-vacio { padding: 40px; text-align: center; color: #6E7A96; font-weight: 300; font-size: 14px; }
</style>

<div class="cc-wrap">
    <div class="cc-head">
        <div class="cc-title"><i class="fas fa-file-invoice-dollar" style="color:#2563EB;"></i> Cuentas corrientes de clientes</div>
        <div class="cc-deuda">
            <div class="l">Deuda total de clientes</div>
            <div class="v">${{ number_format($totalDeuda, 2, ',', '.') }}</div>
        </div>
    </div>

    <form method="GET" action="{{ url('/cc') }}" class="cc-buscar">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre, DNI/CUIT, email o teléfono...">
        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
    </form>

    <div class="cc-card">
        <table class="cc-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th class="der">Cargos</th>
                    <th class="der">Pagos</th>
                    <th class="der">Ventas a cobrar</th>
                    <th class="der">Debe (total)</th>
                    <th class="der"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $c)
                <tr>
                    <td style="font-weight:500;">{{ $c->nombre }} {{ $c->paterno }} {{ $c->materno }}</td>
                    <td style="color:#6E7A96;font-size:13px;">{{ $c->telefono ?: $c->email }}</td>
                    <td class="der">${{ number_format($c->cargos, 2, ',', '.') }}</td>
                    <td class="der">${{ number_format($c->pagos, 2, ',', '.') }}</td>
                    <td class="der {{ $c->ventas_pendiente > 0 ? 'cc-saldo-pos' : 'cc-saldo-cero' }}">${{ number_format($c->ventas_pendiente, 2, ',', '.') }}</td>
                    <td class="der {{ $c->saldo_total > 0 ? 'cc-saldo-pos' : ($c->saldo_total < 0 ? 'cc-saldo-neg' : 'cc-saldo-cero') }}">
                        ${{ number_format($c->saldo_total, 2, ',', '.') }}
                        @if($c->saldo_total < 0) <small>(a favor)</small> @endif
                    </td>
                    <td class="der"><a href="{{ url('/cc/cliente/' . $c->idcliente) }}" class="cc-ver">Ver cuenta</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="cc-vacio">
                    @if($q !== '')
                        No se encontraron clientes con "{{ $q }}".
                    @else
                        Todavía no hay cuentas corrientes con movimientos.<br>
                        <small>Buscá un cliente arriba para abrirle la cuenta y registrarle el primer cargo o seña.</small>
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
