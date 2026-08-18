@extends('layouts.admin')

@section('title', $agent->exists ? 'Editar agente' : 'Nuevo agente')

@section('contenido')
<div class="py-3">
    <h4><i class="fas fa-robot text-primary"></i> {{ $agent->exists ? 'Editar agente: ' . $agent->nombre : 'Nuevo agente de venta IA' }}</h4>
</div>

@if($errors->any())
<div class="alert alert-danger py-2">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $agent->exists ? route('whatsapp.agents.update', $agent->id) : route('whatsapp.agents.store') }}">
    @csrf
    @if($agent->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header py-2"><strong>Identidad y comportamiento</strong></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nombre del agente</label>
                        <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $agent->nombre) }}" placeholder="Ej: Vendedor Sommy">
                    </div>
                    <div class="form-group">
                        <label>Instrucciones (system prompt)</label>
                        <textarea name="system_prompt" class="form-control" rows="14" required>{{ old('system_prompt', $agent->system_prompt) }}</textarea>
                        <small class="text-muted">Definí quién es, cómo habla y qué puede hacer. Las reglas de precios/stock reales ya están cubiertas por las herramientas.</small>
                    </div>
                    <div class="form-group">
                        <label>Mensaje al derivar a un humano</label>
                        <textarea name="mensaje_derivacion" class="form-control" rows="2">{{ old('mensaje_derivacion', $agent->mensaje_derivacion) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header py-2"><strong>Motor</strong></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Proveedor</label>
                            <select name="provider" class="form-control">
                                <option value="openai" {{ old('provider', $agent->provider) == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                <option value="anthropic" {{ old('provider', $agent->provider) == 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label>Modelo</label>
                            <input type="text" name="model" class="form-control" required value="{{ old('model', $agent->model) }}">
                            <small class="text-muted">ej: gpt-4o-mini, claude-sonnet-5</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Temperatura</label>
                            <input type="number" name="temperature" step="0.1" min="0" max="2" class="form-control" value="{{ old('temperature', $agent->temperature) }}">
                        </div>
                        <div class="form-group col-6">
                            <label>Tope de gasto diario (USD)</label>
                            <input type="number" name="tope_costo_diario" step="0.5" min="0" class="form-control" value="{{ old('tope_costo_diario', $agent->tope_costo_diario) }}" placeholder="Sin tope">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2"><strong>Herramientas habilitadas</strong></div>
                <div class="card-body">
                    @foreach(\App\Models\AiAgent::TOOLS as $key => $label)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="tool-{{ $key }}" name="tools_enabled[]" value="{{ $key }}"
                            {{ in_array($key, old('tools_enabled', $agent->tools_enabled ?? [])) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="tool-{{ $key }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2"><strong>Operación</strong></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Atiende desde</label>
                            <input type="time" name="horario_desde" class="form-control" value="{{ old('horario_desde', $agent->horario['desde'] ?? '') }}">
                        </div>
                        <div class="form-group col-6">
                            <label>Hasta</label>
                            <input type="time" name="horario_hasta" class="form-control" value="{{ old('horario_hasta', $agent->horario['hasta'] ?? '') }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-2">Dejá los horarios vacíos para que atienda siempre.</small>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="solo-fuera" name="solo_fuera_de_horario" value="1"
                            {{ old('solo_fuera_de_horario', $agent->solo_fuera_de_horario) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="solo-fuera">Atender solo FUERA del horario (cubre cuando no hay vendedores)</label>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Máx. respuestas sin humano</label>
                            <input type="number" name="max_turnos_sin_humano" min="1" max="100" class="form-control" value="{{ old('max_turnos_sin_humano', $agent->max_turnos_sin_humano) }}">
                        </div>
                        <div class="form-group col-6">
                            <label>Sucursal de stock</label>
                            <select name="sucursal_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ old('sucursal_id', $agent->sucursal_id) == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" {{ old('activo', $agent->activo) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="activo"><strong>Agente activo</strong></label>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary btn-block">{{ $agent->exists ? 'Guardar cambios' : 'Crear agente' }}</button>
            <a href="{{ route('whatsapp.agents.index') }}" class="btn btn-link btn-block">Cancelar</a>
        </div>
    </div>
</form>
@endsection
