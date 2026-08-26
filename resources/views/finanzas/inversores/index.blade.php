@extends('layouts.admin')

@section('title', 'Inversores')

@section('contenido')
<style>
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .res-tot-label { font-size: 12px; color: #47536F; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .res-tot-valor { font-size: 22px; font-weight: 700; color: #1B2B5A; }
    .inv-table th { font-size: .72rem; text-transform: uppercase; color: #64748b; }
    .inv-saldo.negativo { color: #b4552d; }
</style>

<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;"><i class="fas fa-hand-holding-usd" style="color:#2563EB;"></i> Inversores</h4>
        <div class="d-flex gap-2">
            @can('haveaccess', 'inversores.reparto')
            <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalReparto"><i class="fas fa-percentage"></i> Repartir ganancias</button>
            @endcan
            @can('haveaccess', 'inversores.crud')
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoInversor"><i class="fas fa-plus"></i> Nuevo inversor</button>
            @endcan
        </div>
    </div>

    @if(session('inversor_ok'))
        <div class="alert alert-success">{{ session('inversor_ok') }}</div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Inversores activos</div>
                <div class="res-tot-valor">{{ $inversores->where('activo', true)->count() }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Total aportado (histórico)</div>
                <div class="res-tot-valor">${{ number_format($totalAportado, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Saldo total (aportes - retiros/reparto)</div>
                <div class="res-tot-valor">${{ number_format($inversores->sum('saldo'), 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="fin-card">
        <div class="table-responsive">
            <table class="table table-sm table-striped inv-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>% Participación</th>
                        <th>Contacto</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inversores as $inv)
                    <tr>
                        <td class="fw-bold">{{ $inv->nombre }}</td>
                        <td>{{ $inv->porcentaje_participacion !== null ? number_format($inv->porcentaje_participacion, 2, ',', '.') . '%' : '—' }}</td>
                        <td class="text-muted small">{{ $inv->telefono }} {{ $inv->email ? ' · '.$inv->email : '' }}</td>
                        <td class="text-end fw-bold inv-saldo {{ $inv->saldo < 0 ? 'negativo' : '' }}">${{ number_format($inv->saldo, 2, ',', '.') }}</td>
                        <td><span class="badge {{ $inv->activo ? 'badge-success' : 'badge-secondary' }}">{{ $inv->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="text-end"><a href="{{ route('finanzas.inversores.ficha', $inv->id) }}" class="btn btn-sm btn-outline-primary">Ver ficha</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Todavía no cargaste ningún inversor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: nuevo inversor --}}
@can('haveaccess', 'inversores.crud')
<div class="modal fade" id="modalNuevoInversor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('finanzas.inversores.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo inversor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">% de participación (opcional, para reparto de ganancias)</label>
                        <input type="number" name="porcentaje_participacion" class="form-control" min="0" max="100" step="0.01">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Teléfono (opcional)</label>
                        <input type="text" name="telefono" class="form-control" maxlength="30">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email (opcional)</label>
                        <input type="email" name="email" class="form-control" maxlength="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Modal: reparto de ganancias --}}
@can('haveaccess', 'inversores.reparto')
<div class="modal fade" id="modalReparto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formReparto">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-percentage me-2"></i>Repartir ganancias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Se le paga a cada inversor activo con % cargado su parte proporcional del monto total. Solo entran inversores con % &gt; 0.</p>
                    <div class="mb-2">
                        <label class="form-label">Monto total de la ganancia a repartir</label>
                        <input type="number" name="monto_total" id="reparto_monto" class="form-control" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Concepto (opcional)</label>
                        <input type="text" name="concepto" class="form-control" maxlength="250" placeholder="Ej: ganancias de agosto 2026">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">¿De dónde sale la plata? (opcional)</label>
                        <select name="cuenta_ref" id="reparto_cuenta" class="form-control">
                            <option value="">No mover una cuenta (solo registrar el reparto)</option>
                        </select>
                    </div>
                    <div id="reparto_preview" class="small text-muted"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Repartir</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script src="{{ asset('js/funciones_inversores/inversores.js') }}?v={{ filemtime(public_path('js/funciones_inversores/inversores.js')) }}"></script>
@endpush
@endsection
