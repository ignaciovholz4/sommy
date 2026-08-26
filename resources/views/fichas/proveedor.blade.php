@extends('layouts.admin')

@section('title', 'Ficha del proveedor')

@section('contenido')
@include('fichas._estilos')

<div class="fx-wrap">
    <a href="{{ url('compras/proveedor') }}" class="fx-volver"><i class="fas fa-arrow-left"></i> Proveedores</a>
    <div class="fx-title"><i class="fas fa-truck" style="color:#2563EB;"></i> {{ $proveedor->nombre }}</div>
    <div class="fx-sub">
        {{ $proveedor->email ?: '' }}
        @if($proveedor->telefono)
            · <a href="https://wa.me/{{ preg_replace('/\D/', '', $proveedor->telefono) }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i> {{ $proveedor->telefono }}</a>
        @endif
        @if($proveedor->cuit) · CUIT {{ $proveedor->cuit }}@endif
        @if($proveedor->condicion_pago_dias) · Pago a {{ $proveedor->condicion_pago_dias }} días @endif
    </div>

    {{-- KPIs --}}
    <div class="fx-kpis">
        <div class="fx-kpi"><div class="l">Total comprado</div><div class="v">${{ number_format($kpis['total_comprado'], 2, ',', '.') }}</div></div>
        <div class="fx-kpi"><div class="l">Compras</div><div class="v">{{ $kpis['operaciones'] }}</div></div>
        <div class="fx-kpi"><div class="l">Gastos asociados</div><div class="v">${{ number_format($kpis['gastos_total'], 2, ',', '.') }}</div></div>
        <div class="fx-kpi {{ $cc['saldo'] > 0 ? 'deuda' : 'ok' }}">
            <div class="l">Le debemos {{ $cc['vencido'] > 0 ? '· $' . number_format($cc['vencido'], 0, ',', '.') . ' vencido ⚠' : '' }}</div>
            <div class="v">${{ number_format(max($cc['saldo'], 0), 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Compras --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-shopping-cart"></i> Compras ({{ $compras->count() }})</h3>
            <a class="fx-link" href="{{ url('compras') }}"><i class="fas fa-list"></i> Historial completo</a>
        </div>
        <table class="fx-table">
            <thead><tr><th>Folio</th><th>Fecha</th><th>Comprobante</th><th>Estado</th><th>Pagada desde</th><th class="der">Total</th></tr></thead>
            <tbody>
            @forelse($compras as $c)
                <tr>
                    <td style="font-weight:600;">{{ $c->num_folio ?: '#' . $c->idcompra }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                    <td>{{ optional($c->tipoComprobante)->descripcion ?: '—' }}</td>
                    <td><span class="fx-chip {{ ['pagada' => 'ok', 'a pagar' => 'pend', 'anulada' => 'mal'][$c->estado] ?? '' }}">{{ ucfirst($c->estado ?: '—') }}</span></td>
                    <td class="fx-plata">
                        @forelse($c->movimientos as $m)
                            {{ optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta' }}: ${{ number_format($m->total, 0, ',', '.') }}<br>
                        @empty — @endforelse
                        @foreach($c->adjuntos as $adj)
                            <a href="{{ $adj->url }}" target="_blank" title="{{ $adj->original_name }}" style="color:#2563EB;"><i class="fas fa-paperclip"></i> {{ \Illuminate\Support\Str::limit($adj->original_name, 22) }}</a><br>
                        @endforeach
                    </td>
                    <td class="der" style="font-weight:600;">${{ number_format($c->total_con_iva, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="fx-vacio">Sin compras registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pedidos de compra --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-file-signature"></i> Pedidos de compra ({{ $pedidos->count() }})</h3>
            <a class="fx-link" href="{{ url('compras/pedidos') }}"><i class="fas fa-list"></i> Ver pedidos</a>
        </div>
        <table class="fx-table">
            <thead><tr><th>Folio</th><th>Fecha</th><th>Estado</th><th class="der">Total</th></tr></thead>
            <tbody>
            @forelse($pedidos as $p)
                <tr>
                    <td style="font-weight:600;">{{ $p->num_folio ?: '#' . $p->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}</td>
                    <td><span class="fx-chip {{ ['convertido' => 'ok', 'borrador' => 'pend', 'anulado' => 'mal'][$p->estado] ?? '' }}">{{ ucfirst($p->estado) }}</span></td>
                    <td class="der" style="font-weight:600;">${{ number_format($p->total_con_iva, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="fx-vacio">Sin pedidos de compra.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Gastos asociados --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-receipt"></i> Gastos asociados ({{ $gastos->count() }})</h3>
            <a class="fx-link" href="{{ url('finanzas/gastos') }}"><i class="fas fa-list"></i> Ver gastos</a>
        </div>
        <table class="fx-table">
            <thead><tr><th>Fecha</th><th>Descripción</th><th>Estado</th><th class="der">Monto</th></tr></thead>
            <tbody>
            @forelse($gastos as $g)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</td>
                    <td>
                        {{ $g->descripcion }}
                        @if($g->comprobante_path)
                            <br><a href="{{ asset('storage/' . $g->comprobante_path) }}" target="_blank" style="color:#2563EB;font-size:12px;"><i class="fas fa-paperclip"></i> Comprobante</a>
                        @endif
                    </td>
                    <td><span class="fx-chip {{ ['pagado' => 'ok', 'pendiente' => 'pend', 'anulado' => 'mal'][$g->estado] ?? '' }}">{{ ucfirst($g->estado) }}</span></td>
                    <td class="der" style="font-weight:600;">${{ number_format($g->monto, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="fx-vacio">Sin gastos asociados a este proveedor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cuenta corriente (CxP) --}}
    <div class="fx-card">
        <div class="fx-card-head">
            <h3><i class="fas fa-file-invoice-dollar"></i> Cuenta corriente (lo que le debemos)</h3>
            <a class="fx-link" href="{{ url('finanzas/cxp/' . $proveedor->idproveedor) }}"><i class="fas fa-pen"></i> Operar cuenta (pagos / ajustes)</a>
        </div>
        <table class="fx-table">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Vencimiento</th><th>Estado</th><th class="der">Monto</th></tr></thead>
            <tbody>
            @forelse($ccMovs->take(15) as $m)
                <tr>
                    <td style="white-space:nowrap;color:#6E7A96;">{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') }}</td>
                    <td><span class="fx-chip {{ $m->tipo === 'haber' ? 'ok' : 'pend' }}">{{ $m->tipo === 'haber' ? 'Pago' : 'Deuda' }}</span></td>
                    <td>{{ $m->descripcion }}</td>
                    <td style="color:{{ $m->tipo === 'debe' && $m->estado !== 'pagado' && $m->fecha_vencimiento && $m->fecha_vencimiento < now()->toDateString() ? '#b4552d;font-weight:600' : '#6E7A96' }};">
                        {{ $m->fecha_vencimiento ? \Carbon\Carbon::parse($m->fecha_vencimiento)->format('d/m/Y') : '—' }}
                    </td>
                    <td style="color:#6E7A96;">{{ ucfirst($m->estado ?? '—') }}</td>
                    <td class="der" style="font-weight:600;{{ $m->tipo === 'haber' ? 'color:#0d8a4f;' : '' }}">{{ $m->tipo === 'haber' ? '-' : '' }}${{ number_format($m->monto, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="fx-vacio">Sin movimientos: no le debemos nada. 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="fx-card" style="padding:16px;">
        @include('notas._panel', ['tipo' => 'proveedor', 'id' => $proveedor->idproveedor])
    </div>
</div>
@endsection
