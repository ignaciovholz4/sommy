@extends('layouts.admin')

@section('contenido')
<h2 class="mb-4">Elige tu plan</h2>
<div class="row">
    @foreach($plans as $plan)
    <div class="col-md-4">
        <div class="card shadow-sm mb-4 
            {{ $currentSubscription && $currentSubscription->plan_id == $plan->id ? 'border-primary' : '' }}
            {{ $pendingRequest && $pendingRequest->plan_id == $plan->id ? 'border-warning' : '' }}">
            
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">{{ $plan->name }}</h5>
            </div>
            <div class="card-body">
                <p class="card-text text-muted">
                    {{ $plan->features['nota'] ?? 'Sin descripción' }}
                </p>
                <ul class="list-unstyled mb-3">
                    <li><strong>Precio:</strong> ${{ number_format($plan->price,2) }}</li>
                    <li><strong>Duración:</strong> {{ $plan->duration_days }} días</li>
                    @if(isset($plan->features['usuarios']))
                        <li><strong>Usuarios:</strong> {{ $plan->features['usuarios'] }}</li>
                    @endif
                    @if(isset($plan->features['facturas_mensuales']))
                        <li><strong>Facturas mensuales:</strong> {{ $plan->features['facturas_mensuales'] }}</li>
                    @endif
                    @if(isset($plan->features['soporte']))
                        <li><strong>Soporte:</strong> {{ $plan->features['soporte'] }}</li>
                    @endif
                </ul>

                {{-- Mostrar estados --}}
                @if($currentSubscription && $currentSubscription->plan_id == $plan->id)
                    <span class="badge bg-primary">Tu plan actual</span>
                @elseif($pendingRequest && $pendingRequest->plan_id == $plan->id)
                    <span class="badge bg-warning">Solicitud pendiente</span>
                @else
                    {{-- Si hay solicitud pendiente, deshabilitar los demás --}}
                    @if($pendingRequest)
                        <span class="badge bg-secondary">No disponible</span>
                    @else
                        <form action="{{ route('tenant.portal.plans.request',$plan->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-check-circle"></i> Solicitar este plan
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection