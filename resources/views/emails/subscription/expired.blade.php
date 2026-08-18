@component('mail::message')
# Suscripción Expirada

Hola {{ $subscription->tenant->name }},

Tu suscripción al plan **{{ $subscription->plan->name }}** ha expirado el día 
**{{ $subscription->ends_at->format('d/m/Y') }}**.

Para seguir utilizando el sistema, por favor renueva tu plan o contacta al administrador.

@component('mail::button', ['url' => route('superadmin.plans.index')])
Renovar Suscripción
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent