@extends('layouts.admin')

@section('title', 'Clarity — Comportamiento de visitantes')

@section('contenido')
<style>
    .clr-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1250px; margin: 0 auto; }
    .clr-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
    .clr-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 16px 18px; margin-bottom: 16px; }
    .clr-card h3 { font-size: 14px; font-weight: 600; margin: 0 0 10px; text-transform: capitalize; }
    .clr-vacio { color: #6E7A96; font-weight: 300; font-size: 13.5px; text-align: center; padding: 40px 0; }
    .clr-aviso { background:#FEF6E7; color:#9a6b0f; border-radius:12px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
    .clr-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6E7A96; border-bottom: 2px solid #E7EAF2; white-space: nowrap; }
    .clr-table td, .clr-table th { padding: 8px 10px; white-space: nowrap; }
</style>

<div class="clr-wrap">
    <div class="clr-title">
        <div><i class="fas fa-fire" style="color:#2563EB;"></i> Comportamiento de visitantes (Clarity)</div>
        <div class="d-flex gap-2">
            @if($projectId)
            <a href="https://clarity.microsoft.com/projects/view/{{ $projectId }}/dashboard" target="_blank" class="btn btn-sm btn-outline-dark">
                <i class="fas fa-arrow-up-right-from-square"></i> Ver mapa de calor y grabaciones
            </a>
            @endif
            @can('haveaccess', 'clarity.sincronizar')
            <button class="btn btn-sm btn-outline-primary" id="btnSincronizarClarity"><i class="fas fa-rotate"></i> Sincronizar ahora</button>
            @endcan
        </div>
    </div>

    @if(!$habilitado)
    <div class="clr-aviso">
        <i class="fas fa-circle-info"></i> Todavía no cargaste el API Token de Clarity en <a href="{{ route('integraciones.index') }}">Integraciones</a>.
    </div>
    @endif

    @if(!$snapshot)
        <div class="clr-card">
            <div class="clr-vacio">
                Todavía no hay datos sincronizados. Apretá "Sincronizar ahora"
                <br><small>(la API de Clarity limita a 10 sincronizaciones por día, por eso no se trae en cada carga de pantalla)</small>
            </div>
        </div>
    @else
        <p class="text-muted mb-3" style="font-size:12.5px;">
            Último dato: {{ $snapshot->sincronizado_at?->diffForHumans() }} — ventana de los últimos 3 días (límite de la API de Clarity).
        </p>

        @foreach($snapshot->payload as $bloque)
            @php $filas = $bloque['information'] ?? []; @endphp
            @if(!empty($filas))
            <div class="clr-card">
                <h3>{{ $bloque['metricName'] ?? 'Métrica' }}</h3>
                <div class="table-responsive">
                    <table class="table clr-table mb-0">
                        <thead>
                            <tr>
                                @foreach(array_keys($filas[0]) as $col)
                                    <th>{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($filas as $fila)
                            <tr>
                                @foreach($fila as $valor)
                                    <td>{{ is_array($valor) ? json_encode($valor) : $valor }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const btnSincronizarClarity = document.getElementById('btnSincronizarClarity');

if (btnSincronizarClarity) {
    btnSincronizarClarity.addEventListener('click', function () {
        this.disabled = true;
        fetch("{{ route('clarity.sincronizar') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } })
            .then(res => res.json())
            .then(data => {
                if (data.estado === 1) {
                    toastr.success(data.mensaje);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.mensaje || 'No se pudo sincronizar.');
                    this.disabled = false;
                }
            })
            .catch(() => { toastr.error('Error de conexión.'); this.disabled = false; });
    });
}
</script>
@endsection
