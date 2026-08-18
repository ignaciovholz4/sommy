@extends('layouts.admin')

@section('title', 'Centro de Documentación - Facturarg')

@section('styles')
<style>
    /* ADN Visual Facturarg - Documentation */
    :root {
        --facturarg-dark: #0f172a;    
        --facturarg-cyan: #1591a3;    
        --facturarg-bg: #f8fafc;
    }

    .main-content-doc {
        background-color: var(--facturarg-bg);
        min-height: 100vh;
        padding: 2rem;
    }

    /* Hero Search Section */
    .search-wrapper {
        background: linear-gradient(135deg, var(--facturarg-dark) 0%, #1e293b 100%);
        border-radius: 24px;
        padding: 4rem 2rem;
        margin-bottom: 3rem;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
        text-align: center;
        color: white;
    }

    .search-input-group {
        max-width: 700px;
        margin: 2rem auto 0;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px;
        border-radius: 16px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .search-input-group .form-control {
        background: white;
        border: none;
        border-radius: 12px;
        padding: 15px 25px;
        font-weight: 500;
    }

    /* Documentation Cards */
    .documentation-card {
        border: none;
        border-radius: 20px;
        background: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        border: 1px solid #e2e8f0;
    }

    .documentation-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.05);
        border-color: var(--facturarg-cyan);
    }

    .category-label {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1rem;
        background: #ecfeff;
        color: #0891b2;
    }

    .doc-title {
        font-weight: 700;
        color: var(--facturarg-dark);
        margin-bottom: 0.75rem;
        font-size: 1.15rem;
    }

    .doc-excerpt {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .category-section-title {
        font-weight: 800;
        color: var(--facturarg-dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-section-title::after {
        content: '';
        flex-grow: 1;
        height: 2px;
        background: #e2e8f0;
    }

    .btn-read {
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 8px 20px;
        background: var(--facturarg-dark);
        color: white;
        border: none;
        transition: 0.3s;
    }

    .btn-read:hover {
        background: var(--facturarg-cyan);
        color: white;
    }

</style>
@endsection

@section('contenido')
<div class="main-content-doc">
    <div class="search-wrapper">
        <h1 class="fw-bold mb-2">¿Cómo podemos ayudarte hoy?</h1>
        <p class="opacity-75">Explora guías, tutoriales y documentación técnica de Facturarg</p>
        
        <form method="GET" action="{{ route('documentation.index') }}" class="search-input-group">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Ej: Configurar API, Facturación Electrónica..." value="{{ request('search') }}">
                <select name="category" class="form-select border-0 ms-2 d-none d-md-block" style="max-width: 180px; border-radius: 10px; font-weight: 600;">
                    <option value="">Todas las áreas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-info ms-2 px-4 fw-bold text-white" style="border-radius: 10px;">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </form>
    </div>

    <div class="container-fluid">
        @if(request('search') || request('category'))
            <div class="mb-4">
                <h4 class="fw-bold">
                    @if(request('search'))
                        Resultados para "{{ request('search') }}"
                    @else
                        Categoría: {{ request('category') }}
                    @endif
                    <span class="badge bg-white text-dark shadow-sm ms-2 border" style="font-size: 0.9rem;">{{ $documentation->total() }} artículos</span>
                </h4>
            </div>

            @if($documentation->count() > 0)
                <div class="row">
                    @foreach($documentation as $doc)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card documentation-card p-4">
                                <div class="category-label">{{ $doc->category }}</div>
                                <h5 class="doc-title">{{ $doc->title }}</h5>
                                <p class="doc-excerpt">
                                    {{ Str::limit($doc->meta_description ?: $doc->content, 120) }}
                                </p>
                                <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> {{ $doc->created_at->diffForHumans() }}</small>
                                    <a href="{{ route('documentation.show', $doc->slug) }}" class="btn-read">Leer Guía</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $documentation->appends(request()->query())->links() }}
                </div>
            @else
                <div class="card-facturarg text-center py-5 shadow-sm bg-white" style="border-radius: 20px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="80" class="mb-3 opacity-50">
                    <h5 class="fw-bold">No encontramos lo que buscas</h5>
                    <p class="text-muted">Prueba con palabras clave más generales o navega por categorías.</p>
                    <a href="{{ route('documentation.index') }}" class="btn btn-dark px-4 py-2 mt-2" style="border-radius: 10px;">Ver todo</a>
                </div>
            @endif

        @else
            @foreach($categories as $category)
                @php
                    $categoryDocs = $documentation->where('category', $category)->take(3);
                @endphp
                @if($categoryDocs->count() > 0)
                    <div class="mb-5">
                        <h4 class="category-section-title">
                            <i class="fas fa-folder text-info"></i> {{ $category }}
                        </h4>
                        <div class="row">
                            @foreach($categoryDocs as $doc)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card documentation-card p-4">
                                        <h5 class="doc-title">{{ $doc->title }}</h5>
                                        <p class="doc-excerpt">
                                            {{ Str::limit($doc->meta_description ?: $doc->content, 110) }}
                                        </p>
                                        <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold text-info"><i class="fas fa-book-open me-1"></i> Tutorial</span>
                                            <a href="{{ route('documentation.show', $doc->slug) }}" class="btn-read">Explorar</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($documentation->where('category', $category)->count() > 3)
                        <div class="text-end">
                            <a href="{{ route('documentation.category', $category) }}" class="text-decoration-none fw-bold text-dark small">
                                Ver todos los artículos de {{ $category }} <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Sincronizar búsqueda cuando cambia la categoría en el select
    $('select[name="category"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection