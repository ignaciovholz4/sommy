<style>
    /* ADN Visual Facturarg - Inventario Pro */
    :root {
        --facturarg-dark: #0f172a;
        --facturarg-blue: #00A3E0;
        --facturarg-bg: #F8FAFC;
        --facturarg-border: #e2e8f0;
    }

    /* Card de Inventario con espaciado optimizado */
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        padding: 15px; /* Padding para que respire la tabla */
    }

    /* Botón de carga masiva con estilo secundario premium */
    #bulkAddToPriceListBtn {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 700;
        padding: 10px 18px;
        transition: all 0.2s;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    #bulkAddToPriceListBtn:hover {
        background: var(--facturarg-dark);
        color: white;
        transform: translateY(-2px);
    }

    /* Header de Tabla Dark - Tipografía Plus Jakarta */
    #producto_table thead th {
        background-color: var(--facturarg-dark);
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        text-transform: uppercase;
        font-size: 0.65rem; /* Un poco más chico por la cantidad de columnas */
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 18px 12px;
        border: none;
        vertical-align: middle;
    }

    /* Celdas con padding para evitar pegado */
    #producto_table tbody td {
        padding: 15px 12px;
        color: #475569;
        font-weight: 500;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--facturarg-border);
        vertical-align: middle;
    }

    /* Checkbox personalizado */
    input[type="checkbox"] {
        cursor: pointer;
        width: 18px;
        height: 18px;
        accent-color: var(--facturarg-blue);
    }

    /* Modales Estilo Facturarg */
    .modal-content {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: var(--facturarg-dark);
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 24px;
        border: none;
    }

    .modal-title {
        font-weight: 800;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Inputs y Selects del Modal */
    .form-control {
        border-radius: 12px;
        border: 1px solid var(--facturarg-border);
        padding: 12px 16px;
        height: auto;
        font-weight: 600;
    }

    .form-control:focus {
        border-color: var(--facturarg-blue);
        box-shadow: 0 0 0 4px rgba(0, 163, 224, 0.1);
    }

    .btn-confirmar-modal {
        background: var(--facturarg-blue);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        padding: 12px 24px;
        transition: all 0.3s;
    }

    .btn-confirmar-modal:hover {
        background: #0082b3;
        transform: translateY(-2px);
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="producto_table" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center"><input type="checkbox" id="selectAllProducts"></th>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Costo</th>
                        <th>Precio venta</th>
                        <th>Margen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===================== MODAL IMPRIMIR ETIQUETA CON CÓDIGO DE BARRAS ===================== --}}
<div class="modal fade" id="modalImprimirEtiqueta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; box-shadow:0 25px 60px rgba(0,0,0,0.18);">
            <div class="modal-header" style="background:var(--facturarg-dark); border-radius:24px 24px 0 0; padding:22px 28px; border:none;">
                <h5 class="modal-title" style="font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; font-size:1rem;">
                    <i class="fas fa-barcode me-2"></i> Imprimir Etiqueta con Código de Barras
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">

                    {{-- Columna izquierda: opciones --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Producto</label>
                            <div class="p-3" style="background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
                                <div class="fw-bold" id="etiqueta-nombre" style="color:#0f172a; font-size:0.9rem;"></div>
                                <small class="text-muted" id="etiqueta-codigo-display"></small>
                            </div>
                        </div>

                        {{-- Selector de medida/variante (solo productos con combinaciones) --}}
                        <div class="mb-3" id="etiqueta-medida-container" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Medida / Variante</label>
                            <select id="etiqueta-medida" class="form-select form-control"></select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Tamaño de etiqueta</label>
                            <select id="etiqueta-tamano" class="form-select form-control">
                                <option value="58x40">58 × 40 mm — Estándar chica</option>
                                <option value="50x30">50 × 30 mm — Mini (joyería/accesorios)</option>
                                <option value="100x50" selected>100 × 50 mm — Mediana</option>
                                <option value="100x70">100 × 70 mm — Grande</option>
                                <option value="148x100">148 × 100 mm — A6</option>
                                <option value="custom">Personalizado...</option>
                            </select>
                        </div>

                        <div id="etiqueta-custom-size" class="row g-2 mb-3" style="display:none !important;">
                            <div class="col-6">
                                <label class="form-label fw-bold" style="font-size:0.72rem; color:#64748b; text-transform:uppercase;">Ancho (mm)</label>
                                <input type="number" id="etiqueta-ancho-custom" class="form-control" value="80" min="20" max="300">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold" style="font-size:0.72rem; color:#64748b; text-transform:uppercase;">Alto (mm)</label>
                                <input type="number" id="etiqueta-alto-custom" class="form-control" value="50" min="15" max="300">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Cantidad de etiquetas</label>
                            <input type="number" id="etiqueta-cantidad" class="form-control" value="1" min="1" max="500">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Contenido de la etiqueta</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="etiqueta-mostrar-nombre" checked>
                                    <label class="form-check-label fw-600" for="etiqueta-mostrar-nombre">Mostrar nombre del producto</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="etiqueta-mostrar-precio" checked>
                                    <label class="form-check-label" for="etiqueta-mostrar-precio">Mostrar precio de venta</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="etiqueta-mostrar-codigo-texto" checked>
                                    <label class="form-check-label" for="etiqueta-mostrar-codigo-texto">Mostrar código debajo del barras</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Tipo de código de barras</label>
                            <select id="etiqueta-tipo-codigo" class="form-select form-control">
                                <option value="CODE128" selected>CODE 128 (Universal — recomendado)</option>
                                <option value="EAN13">EAN-13 (requiere 12 dígitos)</option>
                                <option value="EAN8">EAN-8 (requiere 7 dígitos)</option>
                                <option value="CODE39">CODE 39</option>
                                <option value="ITF14">ITF-14 (GTIN)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Columna derecha: preview --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-block" style="font-size:0.75rem; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Vista previa</label>
                        <div id="etiqueta-preview-container" style="
                            background:#f1f5f9;
                            border-radius:16px;
                            padding:20px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            min-height:220px;
                        ">
                            <div id="etiqueta-preview" style="
                                background:white;
                                border-radius:6px;
                                box-shadow:0 4px 16px rgba(0,0,0,0.12);
                                padding:10px 14px;
                                display:inline-flex;
                                flex-direction:column;
                                align-items:center;
                                justify-content:center;
                                gap:4px;
                                min-width:160px;
                                text-align:center;
                            ">
                                <div id="preview-nombre" style="font-size:10px; font-weight:700; color:#0f172a; max-width:180px; word-break:break-word; line-height:1.2;"></div>
                                <svg id="preview-barcode"></svg>
                                <div id="preview-precio" style="font-size:12px; font-weight:800; color:#0f172a;"></div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size:0.72rem;">
                            <i class="fas fa-info-circle me-1"></i> La vista previa es orientativa. El tamaño real depende del papel configurado en la impresora.
                        </small>
                    </div>

                </div>
            </div>

            <div class="modal-footer px-4 pb-4" style="border:none;">
                <button type="button" class="btn btn-light rounded-3 fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-imprimir-etiqueta" style="
                    background:var(--facturarg-dark);
                    color:white;
                    border:none;
                    border-radius:12px;
                    font-weight:700;
                    padding:12px 28px;
                    font-family:'Plus Jakarta Sans',sans-serif;
                    transition:all 0.2s;
                ">
                    <i class="fas fa-print me-2"></i> Imprimir etiqueta(s)
                </button>
            </div>
        </div>
    </div>
</div>
{{-- ==================================================================================== --}}

<div class="modal fade" id="bulkPriceListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i> Acción Masiva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <label class="form-label d-block text-start" style="font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 0.75rem;">Seleccione la lista de destino</label>
                    <select id="priceListSelect" class="form-select form-control"></select>
                </div>
                <div class="alert alert-info border-0 py-3" style="background: #f0f9ff; border-radius: 12px;">
                    <small style="color: #0369a1; font-weight: 600;">
                        <i class="fas fa-lightbulb me-1"></i> Solo se muestran listas por porcentaje general que estén activas.
                    </small>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="confirmBulkAttach" class="btn-confirmar-modal">
                    <i class="fas fa-upload me-1"></i> Confirmar Carga
                </button>
            </div>
        </div>
    </div>
</div>