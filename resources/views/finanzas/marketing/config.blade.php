@extends('layouts.admin')

@section('title', 'Topes de gasto — Meta Ads')

@section('contenido')
<style>
    .fin-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 620px; margin: 0 auto; }
    .fin-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; }
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 22px; }
</style>

<div class="fin-wrap">
    <div class="fin-title">
        <a href="{{ route('finanzas.marketing.campanas.index') }}" class="text-decoration-none text-dark"><i class="fas fa-arrow-left"></i></a>
        <i class="fas fa-shield-halved" style="color:#2563EB;"></i> Topes de gasto de Meta Ads
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="fin-card">
        <p class="text-muted" style="font-size:13px;">
            Si una acción de crear campaña o subir presupuesto supera estos topes, se rechaza automáticamente
            antes de llegar a Meta. Dejar en blanco = sin límite.
        </p>
        <form action="{{ route('finanzas.marketing.config.guardar') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Tope de presupuesto diario (por campaña/conjunto, USD)</label>
                <input type="number" name="tope_presupuesto_diario" class="form-control" step="0.01" min="0"
                    value="{{ old('tope_presupuesto_diario', $config->tope_presupuesto_diario) }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Tope de presupuesto total (lifetime, USD)</label>
                <input type="number" name="tope_presupuesto_total" class="form-control" step="0.01" min="0"
                    value="{{ old('tope_presupuesto_total', $config->tope_presupuesto_total) }}">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Tope de gasto diario de la cuenta completa (USD)</label>
                <input type="number" name="tope_gasto_diario_cuenta" class="form-control" step="0.01" min="0"
                    value="{{ old('tope_gasto_diario_cuenta', $config->tope_gasto_diario_cuenta) }}">
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Guardar topes</button>
        </form>
    </div>
</div>
@endsection
