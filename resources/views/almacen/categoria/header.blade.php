<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Estilo de Botón Principal */
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
        background: #00A3E0;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 163, 224, 0.3);
    }

    /* Modales Premium */
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
    }

    /* Formulario */
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

    /* Dropzone de Imagen */
    .image-upload-wrapper {
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s;
    }

    .image-upload-wrapper:hover {
        border-color: #00A3E0;
        background: #f0f9ff;
    }

    .modal-footer {
        border: none;
        padding: 15px 24px 24px;
    }

    .btn-modal-save {
        background: #10b981;
        color: white;
        border-radius: 12px;
        font-weight: 700;
        padding: 12px 24px;
        border: none;
        transition: all 0.3s;
    }

    .btn-modal-save:hover {
        background: #059669;
        transform: translateY(-2px);
    }
</style>
<br>  
<section class="margindivsection mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center">
            <div>
                <button type="button" class="btn-facturarg-main" id="btnshowmodalcategory" data-bs-toggle="modal" data-bs-target="#ModalCategoria">
                    <i class="fas fa-tags"></i>
                    <strong>Agregar nueva categoría</strong>
                </button>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="ModalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['id'=>'save_categorie', 'autocomplete'=>'off', 'enctype' => 'multipart/form-data'])!!}
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label>Nombre de Categoría <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control style-input" placeholder="Ej: Electrónica, Bebidas...">
                </div>
                
                <div class="form-group mb-3">
                    <label>Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" class="form-control style-input" placeholder="Breve descripción de la categoría">
                </div>

                <div class="form-group mb-2">
                    <label>Imagen de Categoría</label>
                    <div class="image-upload-wrapper">
                        <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: #cbd5e1;"></i>
                        <input type="file" id="file" name="imagen" class="form-control" accept="image/*" style="font-size: 0.8rem;">
                        <div class="mt-2 small text-muted">Tamaño recomendado: 150px × 127px</div>
                    </div>
                </div>
                
                @include('custom.validate_save_form_ajax')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-modal-save" id="btnsavecategoria">
                    <i class="fas fa-check-circle me-1"></i> Guardar Categoría
                </button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

<div class="modal fade" id="ModalCategoriaUpdate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #334155;">
                <h5 class="modal-title">Editar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['id'=>'update_categorie','autocomplete'=>'off'])!!}
            <div class="modal-body p-4">
                <input type="text" id="upid" name="upid" hidden="true">
                
                <div class="form-group mb-3">
                    <label>Nombre</label>
                    <input type="text" id="upnombre" name="upnombre" class="form-control style-input" placeholder="Nombre...">
                </div>

                <div class="form-group mb-3">
                    <label>Descripción</label>
                    <input type="text" id="updescripcion" name="updescripcion" class="form-control style-input" placeholder="Descripción...">
                </div>

                <div class="text-center mt-3" id="imgPreview">
                    </div>

                @include('custom.validate_update_form_ajax')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-modal-save" style="background: #3b82f6;" id="btnupdatecategoria">
                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                </button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>