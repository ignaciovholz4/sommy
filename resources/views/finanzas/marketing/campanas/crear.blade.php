@extends('layouts.admin')

@section('title', 'Nueva campaña de Meta Ads')

@section('contenido')
<style>
    .fin-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 760px; margin: 0 auto; }
    .fin-title { font-size: 21px; font-weight: 600; margin-bottom: 16px; }
    .fin-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); padding: 22px; }
    .fin-aviso { background:#FEF6E7; color:#9a6b0f; border-radius:12px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
</style>

<div class="fin-wrap">
    <div class="fin-title">
        <a href="{{ route('finanzas.marketing.campanas.index') }}" class="text-decoration-none text-dark"><i class="fas fa-arrow-left"></i></a>
        <i class="fas fa-bullhorn" style="color:#2563EB;"></i> Nueva campaña de Meta Ads
    </div>

    <div class="fin-aviso">
        <i class="fas fa-circle-info"></i> La campaña nace <strong>pausada</strong> en Meta Ads. Activarla es una acción aparte
        desde el listado, con confirmación explícita — así no sale a gastar plata por accidente.
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="fin-card">
        <form action="{{ route('finanzas.marketing.campanas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Nombre de la campaña</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                <small class="text-muted">Usá el mismo nombre que el <code>utm_campaign</code> de tus links, para que el tablero de ROI cruce bien.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Objetivo</label>
                <select name="objetivo" class="form-select">
                    <option value="OUTCOME_TRAFFIC" selected>Tráfico</option>
                    <option value="OUTCOME_ENGAGEMENT">Interacción / mensajes</option>
                    <option value="OUTCOME_SALES">Ventas</option>
                    <option value="OUTCOME_LEADS">Leads</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Presupuesto diario (USD)</label>
                <input type="number" name="presupuesto_diario" class="form-control" step="0.01" min="1" value="{{ old('presupuesto_diario') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Link de destino</label>
                <input type="url" name="link_destino" class="form-control" placeholder="https://..." value="{{ old('link_destino') }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Texto del anuncio</label>
                <textarea name="texto" class="form-control" rows="3" maxlength="500">{{ old('texto') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Imagen o video del anuncio</label>
                <input type="file" name="archivo" class="form-control" accept="image/*,video/mp4,video/quicktime,video/x-msvideo" required>
                <small class="text-muted">Subida manual — no se genera con IA. Video hasta 40&nbsp;MB (mp4/mov/avi, límite del servidor); si el video queda "procesando" en Meta, puede tardar un momento en generarse la miniatura.</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-plus"></i> Crear campaña (queda en pausa)
            </button>
        </form>
    </div>
</div>
@endsection
