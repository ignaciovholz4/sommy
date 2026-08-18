@extends('layouts.admin')

@section('title', 'Cuenta corriente')

@section('contenido')
<style>
    .ccd-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1100px; margin: 0 auto; }
    .ccd-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .ccd-head { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin: 10px 0 18px; }
    .ccd-title { font-size: 21px; font-weight: 600; }
    .ccd-sub { font-size: 13px; color: #6E7A96; font-weight: 300; }

    .ccd-resumen { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 767px) { .ccd-resumen { grid-template-columns: 1fr; } }
    .ccd-kpi {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 14px;
        padding: 14px 18px; box-shadow: 0 10px 30px rgba(27,43,90,.06);
    }
    .ccd-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .ccd-kpi .v { font-size: 21px; font-weight: 700; }
    .ccd-kpi.saldo .v { color: #b4552d; }
    .ccd-kpi.saldo.alDia .v { color: #0d8a4f; }

    .ccd-forms { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    @media (max-width: 991px) { .ccd-forms { grid-template-columns: 1fr; } }
    .ccd-form {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        padding: 18px 20px; box-shadow: 0 10px 30px rgba(27,43,90,.06);
    }
    .ccd-form h3 { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
    .ccd-form.pago h3 { color: #0d8a4f; }
    .ccd-form.cargo h3 { color: #b4552d; }
    .ccd-form .fila { display: flex; gap: 10px; flex-wrap: wrap; }
    .ccd-form input, .ccd-form select {
        border: 1px solid #E7EAF2; border-radius: 10px; padding: 9px 13px; font-size: 13.5px; color: #1B2B5A;
    }
    .ccd-form input[name=concepto] { flex: 2; min-width: 180px; }
    .ccd-form input[name=monto] { flex: 1; min-width: 110px; }
    .ccd-form select { min-width: 130px; }
    .ccd-form button {
        border: none; border-radius: 999px; padding: 9px 22px; font-size: 13px; font-weight: 500; color: #fff; cursor: pointer;
    }
    .ccd-form.pago button { background: #0d8a4f; }
    .ccd-form.pago button:hover { background: #0b7a45; }
    .ccd-form.cargo button { background: #1B2B5A; }
    .ccd-form.cargo button:hover { background: #2563EB; }

    .ccd-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden;
    }
    .ccd-table { width: 100%; border-collapse: collapse; }
    .ccd-table th {
        background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
        padding: 11px 16px; text-align: left;
    }
    .ccd-table td { padding: 11px 16px; border-bottom: 1px solid #F1F4F9; font-size: 13.5px; }
    .ccd-table .der { text-align: right; }
    .ccd-tipo {
        display: inline-block; border-radius: 999px; font-size: 11px; font-weight: 600; padding: 3px 12px;
    }
    .ccd-tipo.cargo { background: #FBEDE6; color: #b4552d; }
    .ccd-tipo.pago { background: #DCFCE7; color: #166534; }
</style>

<div class="ccd-wrap">
    <a href="{{ url('/cc') }}" class="ccd-volver"><i class="fas fa-arrow-left"></i> Cuentas corrientes</a>
    <div class="ccd-head">
        <div>
            <div class="ccd-title">{{ $cliente->nombre }} {{ $cliente->paterno }} {{ $cliente->materno }}</div>
            <div class="ccd-sub">
                {{ $cliente->telefono ?: '' }} {{ $cliente->telefono && $cliente->email ? '·' : '' }} {{ $cliente->email ?: '' }}
                @if($cliente->telefono)
                    · <a href="https://wa.me/{{ preg_replace('/\D/', '', $cliente->telefono) }}" target="_blank" rel="noopener noreferrer" style="color:#0d8a4f;">WhatsApp <i class="fab fa-whatsapp"></i></a>
                @endif
            </div>
        </div>
    </div>

    @if(session('cc_ok'))
        <div class="alert alert-success" style="border-radius:12px;">{{ session('cc_ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:12px;">{{ $errors->first() }}</div>
    @endif

    {{-- Resumen --}}
    <div class="ccd-resumen">
        <div class="ccd-kpi"><div class="l">Total cargos</div><div class="v">${{ number_format($cargos, 2, ',', '.') }}</div></div>
        <div class="ccd-kpi"><div class="l">Total pagos</div><div class="v">${{ number_format($pagos, 2, ',', '.') }}</div></div>
        <div class="ccd-kpi saldo {{ $saldo <= 0 ? 'alDia' : '' }}">
            <div class="l">Saldo {{ $saldo > 0 ? '(debe)' : ($saldo < 0 ? '(a favor del cliente)' : '— al día') }}</div>
            <div class="v">${{ number_format(abs($saldo), 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Registrar movimientos --}}
    <div class="ccd-forms">
        <div class="ccd-form cargo">
            <h3><i class="fas fa-plus-circle"></i> Registrar cargo (venta a cuenta / deuda)</h3>
            <form method="POST" action="{{ url('/cc/cliente/' . $cliente->idcliente . '/movimiento') }}">
                @csrf
                <input type="hidden" name="tipo" value="cargo">
                <div class="fila">
                    <input type="text" name="concepto" placeholder="Concepto (ej: Colchón Serena 2 plazas — saldo)" required>
                    <input type="number" name="monto" step="0.01" min="0.01" placeholder="Monto $" required>
                    <input type="text" name="referencia" placeholder="Ref. (ej: pedido #12)" style="flex:1;min-width:120px;">
                    <button type="submit">Registrar cargo</button>
                </div>
            </form>
        </div>

        <div class="ccd-form pago">
            <h3><i class="fas fa-hand-holding-usd"></i> Registrar pago (recibo)</h3>
            <form method="POST" action="{{ url('/cc/cliente/' . $cliente->idcliente . '/movimiento') }}">
                @csrf
                <input type="hidden" name="tipo" value="pago">
                <div class="fila">
                    <input type="text" name="concepto" placeholder="Concepto (ej: Seña / Cuota 1 de 3)" required>
                    <input type="number" name="monto" step="0.01" min="0.01" placeholder="Monto $" required>
                    <select name="medio_pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="mercadopago">Mercado Pago</option>
                        <option value="cheque">Cheque</option>
                    </select>
                    <button type="submit">Registrar pago</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Historial --}}
    <div class="ccd-card">
        <table class="ccd-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Medio</th>
                    <th>Ref.</th>
                    <th class="der">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientos as $m)
                <tr>
                    <td style="white-space:nowrap;color:#6E7A96;">{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</td>
                    <td><span class="ccd-tipo {{ $m->tipo }}">{{ $m->tipo === 'cargo' ? 'Cargo' : 'Pago' }}</span></td>
                    <td>{{ $m->concepto }}</td>
                    <td style="color:#6E7A96;">{{ $m->medio_pago ? ucfirst($m->medio_pago) : '—' }}</td>
                    <td style="color:#6E7A96;">{{ $m->referencia ?: '—' }}</td>
                    <td class="der" style="font-weight:600;{{ $m->tipo === 'pago' ? 'color:#0d8a4f;' : '' }}">
                        {{ $m->tipo === 'pago' ? '-' : '' }}${{ number_format($m->monto, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#6E7A96;font-weight:300;">Sin movimientos todavía. Registrá el primer cargo o pago arriba.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
