@extends('layouts.admin')

@section('title', 'Campañas de Meta Ads')

@section('contenido')
<style>
    .fin-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1250px; margin: 0 auto; }
    .fin-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; }
    .fin-vacio { color: #6E7A96; font-weight: 300; font-size: 13.5px; text-align: center; padding: 40px 0; }
    .fin-roi-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6E7A96; border-bottom: 2px solid #E7EAF2; }
    .fin-roi-table td, .fin-roi-table th { padding: 10px 12px; vertical-align: middle; }
    .badge-activo { background: #E7F7EE; color: #0d8a4f; }
    .badge-pausado { background: #F1F2F6; color: #6E7A96; }
    .fin-aviso { background:#FEF6E7; color:#9a6b0f; border-radius:12px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
</style>

<div class="fin-wrap">
    <div class="fin-title">
        <div><i class="fas fa-bullhorn" style="color:#2563EB;"></i> Campañas de Meta Ads</div>
        <div class="d-flex gap-2">
            @can('haveaccess', 'finanzas.marketing.config')
            <a href="{{ route('finanzas.marketing.config') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-shield-halved"></i> Topes de gasto</a>
            @endcan
            @can('haveaccess', 'finanzas.marketing.campanas.crear')
            <a href="{{ route('finanzas.marketing.campanas.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nueva campaña</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(!$habilitado)
        <div class="fin-aviso">
            <i class="fas fa-circle-info"></i> Meta Ads todavía no está configurado (falta el access token o el ad account id en <code>.env</code>).
        </div>
    @endif

    <div class="fin-card">
        @if(empty($campanas))
            <div class="fin-vacio">Todavía no hay campañas creadas en la cuenta.</div>
        @else
            <div class="table-responsive">
                <table class="table fin-roi-table mb-0">
                    <thead>
                        <tr>
                            <th>Campaña</th>
                            <th>Objetivo</th>
                            <th>Presupuesto diario</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campanas as $c)
                        <tr>
                            <td>{{ $c['name'] }}</td>
                            <td>{{ $c['objective'] ?? '—' }}</td>
                            <td>{{ isset($c['daily_budget']) ? '$' . number_format($c['daily_budget'] / 100, 2) : '—' }}</td>
                            <td>
                                <span class="badge {{ $c['status'] === 'ACTIVE' ? 'badge-activo' : 'badge-pausado' }}">{{ $c['status'] }}</span>
                            </td>
                            <td class="text-end">
                                @can('haveaccess', 'finanzas.marketing.campanas.estado')
                                <button class="btn btn-sm btn-outline-dark btn-toggle-estado"
                                    data-id="{{ $c['id'] }}" data-nombre="{{ $c['name'] }}"
                                    data-estado-actual="{{ $c['status'] }}">
                                    {{ $c['status'] === 'ACTIVE' ? 'Pausar' : 'Activar' }}
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const URL_FINANZAS = "{{ url('finanzas') }}";

document.querySelectorAll('.btn-toggle-estado').forEach(btn => {
    btn.addEventListener('click', function () {
        const nombre = this.dataset.nombre;
        const nuevoEstado = this.dataset.estadoActual === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        const verbo = nuevoEstado === 'ACTIVE' ? 'activar' : 'pausar';

        if (!confirm(`¿Confirmás ${verbo} la campaña "${nombre}"?${nuevoEstado === 'ACTIVE' ? ' Esto puede generar gasto real.' : ''}`)) {
            return;
        }

        this.disabled = true;
        fetch(`${URL_FINANZAS}/marketing/campanas/${this.dataset.id}/estado`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ nuevo_estado: nuevoEstado, nombre_campana: nombre })
        })
        .then(res => res.json())
        .then(data => {
            if (data.estado === 1) {
                toastr.success(data.mensaje);
                setTimeout(() => location.reload(), 1200);
            } else {
                toastr.error(data.mensaje || 'No se pudo actualizar el estado.');
                this.disabled = false;
            }
        })
        .catch(() => { toastr.error('Error de conexión.'); this.disabled = false; });
    });
});
</script>
@endsection
