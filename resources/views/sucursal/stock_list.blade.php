<style>
    /* Estética de Tabla Facturarg */
    .card-stock {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        background: #fff;
        overflow: hidden;
    }

    .table-facturarg thead th {
        background-color: #0f172a;
        color: white;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 1.5rem !important; /* Padding interno del recuadro */
        border: none;
    }

    .table-facturarg tbody td {
        padding: 1.5rem !important; /* Espaciado interno de celdas */
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    /* Modales con Estilo Unificado */
    .modal-facturarg .modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
    }

    .modal-facturarg .modal-header {
        background-color: #0f172a;
        color: white;
        padding: 1.5rem 2rem;
        border: none;
    }

    .modal-facturarg .modal-body {
        padding: 2.5rem !important; /* Aire por dentro del modal */
    }

    /* Inputs con padding interno */
    .form-control-facturarg {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 18px !important;
        font-weight: 600;
    }

    .form-control-facturarg:focus {
        border-color: #1591a3;
        box-shadow: none;
    }

    .btn-facturarg-save {
        background-color: #0f172a;
        color: white;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 700;
        border: none;
        transition: 0.3s;
    }

    .btn-facturarg-save:hover {
        background-color: #1e293b;
        color: #22d3ee;
    }

    .table-grey {
        background-color: #e9e9e9 !important;
    }
</style>

<div class="card-stock">
    <div class="card-body p-0"> <div class="table-responsive">
            <table class="table table-facturarg mb-0" id="tablaStockSucursal">
                <thead>
                    <tr>
                        <th class="ps-4">Artículo / Combinación</th>
                        <th>Código</th>
                        <th>Stock Actual</th>
                        <th>Ubicación</th>
                        <th class="text-center pe-4" width="200">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('sucursal') }}" class="btn btn-link text-decoration-none text-muted fw-bold">
        <i class="fa fa-arrow-left me-2"></i> Volver a Sucursales
    </a>
</div>

{{-- Modal Agregar Artículo --}}
<div class="modal fade modal-facturarg" id="modalAgregarArticuloSucursal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">VINCULAR ARTÍCULO A SUCURSAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Buscá y seleccioná los productos que querés dar de alta en esta sucursal.</p>
                <div class="row mb-3">
                    <div class="col-md-6 d-flex">
                        <select id="filtroCategoria" class="form-select me-2"></select>
                        <button type="button" class="btn btn-outline-success" id="btnSelectCategoria">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="col-md-6 d-flex">
                        <select id="filtroMarca" class="form-select me-2"></select>
                        <button type="button" class="btn btn-outline-success" id="btnSelectMarca">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleSeleccion">
                        <i class="fas fa-check-square me-2"></i> Seleccionar / Deseleccionar todo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-facturarg table-hover" id="tablaBuscarArticulosSucursal">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Artículo / Combinación</th>
                                <th>Código</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-primary px-4" id="btnAgregarSeleccionados">
                    <i class="fas fa-plus me-2"></i> AGREGAR SELECCIONADOS
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar Stock (Unificado para Art y Comb) --}}
<div class="modal fade modal-facturarg" id="modalEditarStockSucursal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1591a3;">
                <h5 class="modal-title fw-bold text-uppercase">
                    <i class="fas fa-boxes me-2 text-white"></i> Ajuste de Inventario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- 🔹 Identificadores ocultos -->
                <input type="hidden" id="registro_id">
                <input type="hidden" id="tipo_registro">
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small uppercase">Cantidad en Stock</label>
                    <input type="number" class="form-control form-control-facturarg" id="stock_edit" placeholder="0">
                </div>
                <div class="d-grid mt-4">
                    <button class="btn-facturarg-save" id="btnGuardarStockSucursal" style="background-color: #1591a3;">
                        <i class="fas fa-save me-2"></i> GUARDAR CAMBIOS
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar Ubicación (Unificado para Art y Comb) --}}
<div class="modal fade modal-facturarg" id="modalEditarUbicacionSucursal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1591a3;">
                <h5 class="modal-title fw-bold text-uppercase">
                    <i class="fas fa-map-marker-alt me-2 text-white"></i> Ubicación Interna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- 🔹 Identificadores ocultos -->
                <input type="hidden" id="registro_id_ubicacion">
                <input type="hidden" id="tipo_registro_ubicacion">
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small uppercase">Referencia de Rack / Estantería</label>
                    <input type="text" class="form-control form-control-facturarg" id="ubicacion_edit" placeholder="Ej: Pasillo A - Estante 2">
                </div>
                <div class="d-grid mt-4">
                    <button class="btn-facturarg-save" id="btnGuardarUbicacionSucursal" style="background-color: #1591a3;">
                        <i class="fas fa-save me-2"></i> ACTUALIZAR UBICACIÓN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>