@extends('layouts.admin')

@section('title', 'Inversor · ' . $inversor->nombre)

@section('contenido')
<style>
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .res-tot-label { font-size: 12px; color: #47536F; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .res-tot-valor { font-size: 22px; font-weight: 700; color: #1B2B5A; }
    .res-tot-valor.rojo { color: #b4552d; }
</style>

<div class="container-fluid" style="padding: 18px 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px;">
        <div>
            <a href="{{ route('finanzas.inversores.index') }}" class="text-muted small"><i class="fas fa-arrow-left"></i> Inversores</a>
            <h4 class="mb-0" style="color:#1B2B5A;font-weight:600;">{{ $inversor->nombre }}
                @if($inversor->porcentaje_participacion !== null)
                    <span class="badge bg-info text-dark">{{ number_format($inversor->porcentaje_participacion, 2, ',', '.') }}%</span>
                @endif
            </h4>
        </div>
        @can('haveaccess', 'inversores.crud')
        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarInversor"><i class="fas fa-edit"></i> Editar</button>
        @endcan
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Aportes</div>
                <div class="res-tot-valor">${{ number_format($aportes, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Retiros + Reparto</div>
                <div class="res-tot-valor rojo">${{ number_format($retiros, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="fin-card">
                <div class="res-tot-label">Saldo</div>
                <div class="res-tot-valor {{ $saldo < 0 ? 'rojo' : '' }}">${{ number_format($saldo, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @can('haveaccess', 'inversores.movimiento')
    <div class="fin-card mb-4">
        <h6 class="fw-bold mb-3">Registrar aporte / retiro</h6>
        <form id="formMovimientoInversor" data-inversor-id="{{ $inversor->id }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Tipo</label>
                <select name="tipo" class="form-control" required>
                    <option value="aporte">Aporte (entra)</option>
                    <option value="retiro">Retiro (sale)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Monto</label>
                <input type="number" name="monto" class="form-control" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">¿De/a qué cuenta? (opcional)</label>
                <select name="cuenta_ref" id="movimiento_cuenta" class="form-control">
                    <option value="">No mover una cuenta (solo registrar)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Concepto (opcional)</label>
                <input type="text" name="concepto" class="form-control" maxlength="250">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-dark w-100">Guardar</button>
            </div>
        </form>
    </div>
    @endcan

    <div class="fin-card">
        <h6 class="fw-bold mb-3">Movimientos ({{ $movimientos->count() }})</h6>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Cuenta</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $m)
                    <tr>
                        <td>{{ $m->fecha->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $m->tipo === 'aporte' ? 'badge-success' : ($m->tipo === 'distribucion' ? 'badge-info' : 'badge-danger') }}">
                                {{ ucfirst($m->tipo) }}
                            </span>
                        </td>
                        <td>{{ $m->concepto ?: '—' }}</td>
                        <td>{{ optional($m->cuenta)->nombre ?: '—' }}</td>
                        <td class="text-end fw-bold {{ $m->tipo === 'aporte' ? 'text-success' : 'text-danger' }}">
                            {{ $m->tipo === 'aporte' ? '+' : '−' }}${{ number_format($m->monto, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Sin movimientos todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@can('haveaccess', 'inversores.crud')
<div class="modal fade" id="modalEditarInversor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('finanzas.inversores.update', $inversor->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Editar inversor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="200" value="{{ $inversor->nombre }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">% de participación</label>
                        <input type="number" name="porcentaje_participacion" class="form-control" min="0" max="100" step="0.01" value="{{ $inversor->porcentaje_participacion }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="30" value="{{ $inversor->telefono }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" maxlength="150" value="{{ $inversor->email }}">
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" class="form-check-input" id="activoCheck" {{ $inversor->activo ? 'checked' : '' }}>
                        <label class="form-check-label" for="activoCheck">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" onclick="if(confirm('¿Eliminar este inversor y todo su historial?')) document.getElementById('formEliminarInversor').submit();">Eliminar</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Guardar</button>
                </div>
            </form>
            <form id="formEliminarInversor" method="POST" action="{{ route('finanzas.inversores.destroy', $inversor->id) }}" style="display:none;">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script src="{{ asset('js/funciones_inversores/inversores.js') }}?v={{ filemtime(public_path('js/funciones_inversores/inversores.js')) }}"></script>
@endpush
@endsection
