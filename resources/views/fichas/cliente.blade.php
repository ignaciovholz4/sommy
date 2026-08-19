@extends('layouts.admin')

@section('title', 'Ficha del cliente')

@section('contenido')
@include('fichas._estilos')

<div class="fx-wrap">
    <a href="{{ url('clientes') }}" class="fx-volver"><i class="fas fa-arrow-left"></i> Clientes</a>
    <div class="fx-title"><i class="fas fa-user-circle" style="color:#2563EB;"></i> {{ trim($cliente->nombre . ' ' . $cliente->paterno . ' ' . $cliente->materno) }}</div>
    <div class="fx-sub">
        @if($cliente->dni_cuit)<b>DNI/CUIT {{ $cliente->dni_cuit }}</b> · @endif{{ $cliente->email ?: '' }}
        @if($cliente->telefono)
            · <a href="https://wa.me/{{ preg_replace('/\D/', '', $cliente->telefono) }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i> {{ $cliente->telefono }}</a>
        @endif
        @if($cliente->direccion) · {{ $cliente->direccion }}{{ $cliente->localidad ? ', ' . $cliente->localidad : '' }}@endif
    </div>

    {{-- KPIs --}}
    <div class="fx-kpis">
        <div class="fx-kpi"><div class="l">Total comprado</div><div class="v">${{ number_format($kpis['total'], 2, ',', '.') }}</div></div>
        <div class="fx-kpi"><div class="l">Operaciones</div><div class="v">{{ $kpis['operaciones'] }}</div></div>
        <div class="fx-kpi"><div class="l">Ventas / Pedidos web</div><div class="v" style="font-size:15px;">${{ number_format($kpis['total_ventas'], 0, ',', '.') }} / ${{ number_format($kpis['total_pedidos'], 0, ',', '.') }}</div></div>
        <div class="fx-kpi {{ $cc['saldo'] > 0 ? 'deuda' : 'ok' }}">
            <div class="l">Cuenta corriente {{ $cc['saldo'] > 0 ? '(nos debe)' : ($cc['saldo'] < 0 ? '(a favor)' : '— al día') }}</div>
            <div class="v">${{ number_format(abs($cc['saldo']), 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Ventas --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-cash-register"></i> Ventas ({{ $ventas->count() }})</h3>
        </div>
        <table class="fx-table">
            <thead><tr><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Dónde pagó</th><th class="der">Total</th></tr></thead>
            <tbody>
            @forelse($ventas as $v)
                <tr>
                    <td><a href="{{ url('ventas?ver=' . $v->idventa) }}">{{ $v->num_folio ?: '#' . $v->idventa }}</a></td>
                    <td>{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($v->tipo_venta ?? 'minorista') }}{{ optional($v->tipoComprobante)->descripcion ? ' · ' . $v->tipoComprobante->descripcion : '' }}</td>
                    <td>
                        <span class="fx-chip {{ ['cobrada' => 'ok', 'a cobrar' => 'pend', 'anulada' => 'mal'][$v->estado] ?? '' }}">{{ ucfirst($v->estado) }}</span>
                    </td>
                    <td class="fx-plata">
                        @forelse($v->movimientos as $m)
                            {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 0, ',', '.') }}<br>
                        @empty — @endforelse
                        @foreach($compVentas->get($v->idventa, collect()) as $cp)
                            <a href="{{ asset($cp->archivo) }}" target="_blank" title="{{ $cp->nota ?: 'Comprobante' }}" style="color:#2563EB;"><i class="fas fa-paperclip"></i> Comprobante</a><br>
                        @endforeach
                    </td>
                    <td class="der" style="font-weight:600;">${{ number_format($v->total_con_iva, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="fx-vacio">Sin ventas registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pedidos ecommerce --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-shopping-basket"></i> Pedidos web / otros canales ({{ $pedidos->count() }})</h3>
        </div>
        <table class="fx-table">
            <thead><tr><th>Pedido</th><th>Fecha</th><th>Canal</th><th>Estado</th><th>Dónde pagó</th><th class="der">Total</th></tr></thead>
            <tbody>
            @forelse($pedidos as $p)
                @php
                    $movsP = $pagosPedidos->get('Pedido #' . $p->order_id, collect());
                    $pagadoP = (float) $movsP->sum('total');
                    $faltaP = max((float) $p->total_amount - $pagadoP, 0);
                @endphp
                <tr>
                    <td><a href="{{ url('orders/order/' . $p->order_id) }}">#{{ $p->order_id }}</a></td>
                    <td>{{ \Carbon\Carbon::parse($p->order_date)->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($p->origen ?? 'tienda') }}</td>
                    <td><span class="fx-chip {{ $p->status_order_id == 5 ? 'ok' : ($p->status_order_id == 6 ? 'mal' : 'pend') }}">{{ optional($p->status)->status_name ?: '—' }}</span></td>
                    <td class="fx-plata">
                        @forelse($movsP as $m)
                            {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 0, ',', '.') }}<br>
                        @empty
                            <span style="color:#94A3B8;">Sin pago registrado</span>
                        @endforelse
                        @if($pagadoP > 0.009 && $faltaP > 0.009 && $p->status_order_id != 6)
                            <span style="color:#b4552d;font-weight:700;">Faltan ${{ number_format($faltaP, 0, ',', '.') }}</span>
                        @endif
                        @foreach($compPedidos->get($p->order_id, collect()) as $cp)
                            <a href="{{ asset($cp->archivo) }}" target="_blank" title="{{ $cp->nota ?: 'Comprobante' }}" style="color:#2563EB;"><i class="fas fa-paperclip"></i> Comprobante</a><br>
                        @endforeach
                    </td>
                    <td class="der" style="font-weight:600;">${{ number_format($p->total_amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="fx-vacio">Sin pedidos web.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cuenta corriente --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-file-invoice-dollar"></i> Cuenta corriente</h3>
            <a class="fx-link" href="{{ url('cc/cliente/' . $cliente->idcliente) }}"><i class="fas fa-pen"></i> Operar cuenta (cargos / pagos)</a>
        </div>
        <table class="fx-table">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Concepto</th><th>Medio</th><th class="der">Monto</th></tr></thead>
            <tbody>
            @forelse($ccMovs->take(15) as $m)
                <tr>
                    <td style="white-space:nowrap;color:#6E7A96;">{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') }}</td>
                    <td><span class="fx-chip {{ $m->tipo === 'pago' ? 'ok' : 'pend' }}">{{ ucfirst($m->tipo) }}</span></td>
                    <td>{{ $m->concepto }}</td>
                    <td style="color:#6E7A96;">{{ $m->medio_pago ? ucfirst($m->medio_pago) : '—' }}</td>
                    <td class="der" style="font-weight:600;{{ $m->tipo === 'pago' ? 'color:#0d8a4f;' : '' }}">{{ $m->tipo === 'pago' ? '-' : '' }}${{ number_format($m->monto, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="fx-vacio">Sin movimientos de cuenta corriente. 👌</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
