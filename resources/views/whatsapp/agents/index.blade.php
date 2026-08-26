@extends('layouts.admin')

@section('title', 'Agentes IA')

@section('contenido')
<div class="d-flex justify-content-between align-items-center py-3 flex-wrap">
    <h4 class="mb-0"><i class="fas fa-robot text-primary"></i> Agentes de venta IA</h4>
    <div>
        <a href="{{ route('whatsapp.inbox') }}" class="btn btn-sm btn-outline-secondary"><i class="fab fa-whatsapp"></i> Bandeja</a>
        @can('haveaccess','agents.crud')
        <a href="{{ route('whatsapp.agents.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nuevo agente</a>
        @endcan
    </div>
</div>

@if(session('message'))
<div class="alert alert-{{ session('typealert') == 'success' ? 'success' : 'warning' }} py-2">{{ session('message') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr style="font-size:13px">
                    <th>Agente</th>
                    <th>Proveedor / Modelo</th>
                    <th>Herramientas</th>
                    <th>Horario</th>
                    <th class="text-right">Costo hoy (USD)</th>
                    <th class="text-center">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody style="font-size:13.5px">
                @forelse($agents as $agent)
                <tr>
                    <td>
                        <strong>{{ $agent->nombre }}</strong>
                        <div class="text-muted" style="font-size:12px">{{ $agent->runs_count }} ejecuciones</div>
                    </td>
                    <td>{{ $agent->provider }} · <code>{{ $agent->model }}</code></td>
                    <td style="font-size:12px">
                        @foreach($agent->tools_enabled ?? [] as $tool)
                            <span class="badge badge-light border">{{ $tool }}</span>
                        @endforeach
                    </td>
                    <td style="font-size:12px">
                        @if($agent->horario)
                            {{ $agent->horario['desde'] ?? '' }}–{{ $agent->horario['hasta'] ?? '' }}
                            @if($agent->solo_fuera_de_horario) <span class="badge badge-info">solo fuera de horario</span> @endif
                        @else
                            Siempre
                        @endif
                    </td>
                    <td class="text-right">
                        ${{ number_format($agent->costo_hoy, 2) }}
                        @if($agent->tope_costo_diario)
                            <span class="text-muted">/ {{ number_format($agent->tope_costo_diario, 2) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('haveaccess','agents.toggle')
                        <button class="btn btn-sm {{ $agent->activo ? 'btn-success' : 'btn-outline-secondary' }} toggle-agent" data-id="{{ $agent->id }}">
                            {{ $agent->activo ? 'Activo' : 'Apagado' }}
                        </button>
                        @else
                        <span class="badge {{ $agent->activo ? 'badge-success' : 'badge-secondary' }}">{{ $agent->activo ? 'Activo' : 'Apagado' }}</span>
                        @endcan
                    </td>
                    <td class="text-right">
                        <a href="{{ route('whatsapp.agents.runs', $agent->id) }}" class="btn btn-sm btn-outline-info" title="Log de ejecuciones"><i class="fas fa-list"></i></a>
                        @can('haveaccess','agents.crud')
                        <a href="{{ route('whatsapp.agents.edit', $agent->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">
                    Todavía no hay agentes. Creá el primero para que atienda el WhatsApp automáticamente.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    $('.toggle-agent').on('click', function () {
        var $btn = $(this);
        $.post('{{ url('whatsapp/agents') }}/' + $btn.data('id') + '/toggle').done(function (res) {
            if (res.activo) {
                $btn.removeClass('btn-outline-secondary').addClass('btn-success').text('Activo');
            } else {
                $btn.removeClass('btn-success').addClass('btn-outline-secondary').text('Apagado');
            }
        });
    });
});
</script>
@endsection
