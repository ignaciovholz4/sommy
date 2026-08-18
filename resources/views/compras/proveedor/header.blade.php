<style>
    /* Estilo de Botón Principal (Mismo que Ventas/Compras) */
    .btn-facturarg-main {
        background: #0f172a;
        color: #ffffff !important;
        border-radius: 12px;
        padding: 10px 24px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }

    .btn-facturarg-main:hover {
        background: #00A3E0; /* Celeste branding */
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 163, 224, 0.3);
    }

    /* Modales Estilo Premium */
    .modal-content {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: #0f172a;
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 20px 24px;
        border: none;
    }

    .modal-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .modal-body {
        padding: 24px;
        background: #ffffff;
    }

    /* Formulario e Inputs */
    .form-group label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #64748b;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .style-input {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        height: auto;
        font-weight: 500;
        transition: all 0.2s;
    }

    .style-input:focus {
        border-color: #00A3E0;
        box-shadow: 0 0 0 4px rgba(0, 163, 224, 0.1);
    }

    /* Footer y Botones de Modal */
    .modal-footer {
        border: none;
        padding: 15px 24px 24px;
    }

    .btn-modal-close {
        background: #f1f5f9;
        color: #475569;
        border-radius: 12px;
        font-weight: 700;
        padding: 10px 20px;
        border: none;
        transition: all 0.2s;
    }

    .btn-modal-close:hover {
        background: #e2e8f0;
    }

    .btn-modal-save {
        background: #10b981;
        color: white;
        border-radius: 12px;
        font-weight: 700;
        padding: 10px 20px;
        border: none;
        transition: all 0.3s;
    }

    .btn-modal-save:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }
</style>
<br>
<section class="content margindivsection mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center">
            <div>
                <a href="#" class="btn-facturarg-main" id="btnshowmodalprovider" data-bs-toggle="modal" data-bs-target="#ModalSaveProveedor">
                    <i class="fas fa-truck-loading"></i>
                    <strong>Agregar nuevo proveedor</strong>
                </a>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="ModalSaveProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Proveedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="save_proveedor" autocomplete="off">
                <div class="modal-body">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Nombre Comercial <span class="text-danger">*</span></label>
                        <input type="text" class="form-control style-input" name="nombre" placeholder="Nombre de la empresa o contacto">
                    </div>
                    <div class="form-group mb-3">
                        <label>Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control style-input" name="direccion" placeholder="Ej: Av. Colón 123, Córdoba">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Teléfono <span class="text-danger">*</span></label>
                                <input type="number" class="form-control style-input" name="telefono" placeholder="351...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control style-input" name="email" placeholder="proveedor@mail.com">
                            </div>
                        </div>
                    </div>
                    @include('custom.validate_save_form_ajax')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-modal-save" id="btnsaveproveedor">
                        <i class="fas fa-check-circle me-1"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalUpdateProvider" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #334155;"> <h5 class="modal-title">Editar Proveedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="update_proveedor" autocomplete="off">
                <div class="modal-body">
                    @csrf
                    <input type="text" name="upid" id="upid" hidden="true">
                    <div class="form-group mb-3">
                        <label>Nombre Comercial <span class="text-danger">*</span></label>
                        <input type="text" class="form-control style-input" id="upnombre" name="upnombre" placeholder="Nombre del proveedor">
                    </div>
                    <div class="form-group mb-3">
                        <label>Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control style-input" id="updireccion" name="updireccion" placeholder="Dirección">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Teléfono <span class="text-danger">*</span></label>
                                <input type="number" class="form-control style-input" id="uptelefono" name="uptelefono" placeholder="Teléfono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="text" class="form-control style-input" id="upemail" name="upemail" placeholder="Email">
                            </div>
                        </div>
                    </div>
                    @include('custom.validate_update_form_ajax')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-modal-save" style="background: #3b82f6;">
                        <i class="fas fa-sync-alt me-1"></i> Actualizar Datos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>