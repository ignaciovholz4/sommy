@extends('layouts.admin')

@section('title', 'Ejecuciones — ' . $agent->nombre)

@section('contenido')
<div class="d-flex justify-content-between align-items-center py-3">
    <h4 class="mb-0"><i class="fas fa-list"></i> Ejecuciones de {{ $agent->nombre }}</h4>
    <a href="{{ route('whatsapp.agents.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0" style="font-size:13px">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Conversación</th>
                    <th>Herramientas usadas</th>
                    <th class="text-right">Tokens (in/out)</th>
                    <th class="text-right">Costo USD</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                <tr>
                    <td>{{ $run->created_at->format('d/m H:i') }}</td>
                    <td>{{ $run->conversation->profile_name ?? $run->conversation->phone_e164 ?? '—' }}</td>
                    <td style="font-size:12px">
                        @foreach(collect($run->tool_calls ?? [])->pluck('tool')->countBy() as $tool => $n)
                            <span class="badge badge-light border">{{ $tool }}{{ $n > 1 ? " ×$n" : '' }}</span>
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($run->prompt_tokens) }} / {{ number_format($run->completion_tokens) }}</td>
                    <td class="text-right">${{ number_format($run->costo_estimado, 4) }}</td>
                    <td>
                        <span class="badge badge-{{ ['ok' => 'success', 'error' => 'danger', 'escalated' => 'warning'][$run->status] ?? 'secondary' }}">{{ $run->status }}</span>
                        @if($run->error)<i class="fas fa-info-circle text-danger" title="{{ $run->error }}"></i>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Sin ejecuciones todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
