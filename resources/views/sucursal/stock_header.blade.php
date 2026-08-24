<style>
    /* ADN Visual Facturarg */
    :root {
        --facturarg-dark: #0f172a;    
        --facturarg-cyan: #1591a3;    
        --facturarg-bg: #f1f5f9;      
        --facturarg-accent: #22d3ee;  
    }

    /* Contenedor con Padding Interno Amplio */
    .section-header-facturarg {
        background-color: white;
        border-radius: 16px;
        padding: 2rem 2.5rem; /* Padding por dentro del recuadro */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
        border: 1px solid #edf2f7;
    }

    /* Botón Principal Estilizado */
    .btn-facturarg-action {
        background-color: var(--facturarg-dark);
        color: white;
        border-radius: 10px;
        padding: 14px 24px; /* Más aire interno en el botón */
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .btn-facturarg-action:hover {
        background-color: #1e293b;
        color: var(--facturarg-accent);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 23, 42, 0.1);
    }

    .btn-facturarg-action i {
        font-size: 1.1rem;
        margin-right: 10px;
    }

    /* Badge de Sucursal */
    .sucursal-info-badge {
        background-color: var(--facturarg-bg);
        padding: 12px 20px; /* Padding interno del badge */
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
    }

    .sucursal-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-right: 8px;
        letter-spacing: 0.5px;
    }

    .sucursal-name {
        color: var(--facturarg-dark);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .btn-export-pdf {
        background: #fff1f2;
        color: #e11d48 !important;
        border: 1px solid #fecdd3;
        border-radius: 10px;
        padding: 14px 24px;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }
    .btn-export-pdf i { margin-right: 10px; }
    .btn-export-pdf:hover { background: #e11d48; color: white !important; transform: translateY(-2px); }

    .btn-export-excel {
        background: #f0fdf4;
        color: #16a34a !important;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 14px 24px;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }
    .btn-export-excel i { margin-right: 10px; }
    .btn-export-excel:hover { background: #16a34a; color: white !important; transform: translateY(-2px); }
</style>

<section class="content">
    <div class="section-header-facturarg">
        <div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">

            <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                <a href="#" id="btnAgregarArticuloSucursal" class="btn-facturarg-action">
                    <i class="fa fa-plus-circle"></i>
                    <span>Agregar artículo a la sucursal</span>
                </a>
                <a href="{{ route('sucursal.stock.pdf', $sucursal->id) }}" class="btn-export-pdf">
                    <i class="fas fa-file-pdf"></i>
                    <span>Exportar a PDF</span>
                </a>
                <a href="{{ route('sucursal.stock.excel', $sucursal->id) }}" class="btn-export-excel">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar a Excel</span>
                </a>
            </div>

            <div class="d-flex align-items-center mt-3 mt-sm-0">
                <div class="sucursal-info-badge">
                    <span class="sucursal-label">Sucursal:</span>
                    <span class="sucursal-name">{{ $sucursal->nombre }}</span>
                </div>
            </div>
            
        </div>
    </div>
</section>