<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* ADN Visual Facturarg - Botones y Layout */
    .btn-facturarg-action {
        border-radius: 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 10px 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none !important;
    }

    /* Botón Primario (Crear/Categorías) */
    .btn-main-dark {
        background: #0f172a;
        color: #ffffff !important;
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
    }

    .btn-main-dark:hover {
        background: #00A3E0;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 163, 224, 0.3);
    }

    /* Botones de Exportación (Estilo Outline/Soft) */
    .btn-export-pdf {
        background: #fff1f2;
        color: #e11d48 !important; /* Rojo PDF */
        border: 1px solid #fecdd3;
    }

    .btn-export-pdf:hover {
        background: #e11d48;
        color: white !important;
        transform: translateY(-2px);
    }

    .btn-export-excel {
        background: #f0fdf4;
        color: #16a34a !important; /* Verde Excel */
        border: 1px solid #bbf7d0;
    }

    .btn-export-excel:hover {
        background: #16a34a;
        color: white !important;
        transform: translateY(-2px);
    }

    /* Contenedor con padding extra */
    .header-actions-container {
        padding: 10px 5px;
        margin-bottom: 10px;
    }

    .gap-buttons {
        gap: 12px;
    }
</style>

<section class="margindivsection header-actions-container">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center flex-wrap gap-buttons">
            
            <form method="get" action="/pdfinventario" class="m-0">
                <button type="submit" class="btn-facturarg-action btn-export-pdf">
                    <i class="fas fa-file-pdf"></i>
                    <span>Generar Inventario</span>
                </button>
            </form>

            <form method="get" action="/excelarticulo" class="m-0">
                <button type="submit" class="btn-facturarg-action btn-export-excel">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar a Excel</span>
                </button>
            </form>

            <a href="{{ url('almacen/lista-precios-pdf?tipo=minorista') }}" target="_blank" class="btn-facturarg-action btn-export-pdf" style="border-color:#2563EB;color:#2563EB;">
                <i class="fas fa-images"></i>
                <span>Catálogo Minorista</span>
            </a>

            <a href="{{ url('almacen/lista-precios-pdf?tipo=mayorista') }}" target="_blank" class="btn-facturarg-action btn-export-pdf" style="border-color:#7C3AED;color:#7C3AED;" title="Precios mayoristas: solo para el catálogo, nunca se muestran en la web">
                <i class="fas fa-boxes-stacked"></i>
                <span>Catálogo Mayorista</span>
            </a>

            <div class="d-none d-md-block mx-1" style="width: 1px; height: 24px; background: #e2e8f0;"></div>

            <a href="{{url('almacen/formproduct')}}" class="btn-facturarg-action btn-main-dark">
                <i class="fas fa-plus-circle"></i>
                <span>Crear Producto</span>
            </a>

            <a href="{{url('almacen/categoria')}}" class="btn-facturarg-action btn-main-dark" style="background: #334155;">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>

        </div>
    </div>
</section>