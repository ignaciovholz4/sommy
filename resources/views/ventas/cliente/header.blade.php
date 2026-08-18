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
<section class="content margindivsection">
    <div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center">
            <div>
                <button type="button" class="btn-facturarg-main" id="btnshowmodalcliente" data-bs-toggle="modal" data-bs-target="#ModalSaveCliente">
                    <i class="fa fa-archive me-2"></i>
                    <strong>Registrar nuevo cliente</strong>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- MODAL PARA AGREGAR NUEVO CLIENTE-->
<div class="modal fade" id="ModalSaveCliente" tabindex="-1" role="dialog" aria-labelledby="ModalSaveClienteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="ModalSaveClienteLabel">Agregar nuevo cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body style-modal-form">
            <form id="save_cliente" autocomplete="off">
            @csrf
            <div class="form-group mb-3">
            <label>Nombre<i class="text-danger"><strong>*</strong></i></label>
                <input type="text" class="form-control style-input" name="nombre" required placeholder="Ingresa el nombre del cliente">
            </div>
             <div class="form-group">
            <label>Direccion<i class="text-danger"><strong>*</strong></i></label>
                <input type="text" class="form-control style-input" name="direccion" required placeholder="Ingresa la direccion del cliente">
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                <label>Localidad</label>
                    <input type="text" class="form-control style-input" name="localidad" placeholder="Localidad">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                <label>Provincia</label>
                    <input type="text" class="form-control style-input" name="provincia" placeholder="Provincia">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                <label>C.P.</label>
                    <input type="text" class="form-control style-input" name="codigo_postal" maxlength="10" placeholder="CP">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                <label>DNI / CUIT</label>
                    <input type="text" class="form-control style-input" name="dni_cuit" maxlength="13" placeholder="DNI o CUIT">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                <label>Condición fiscal</label>
                    <select class="form-control style-input" name="condicion_fiscal">
                        @foreach (\App\Models\Cliente::CONDICIONES_FISCALES as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
              </div>
            </div>
             <div class="form-group">
            <label>Telefono<i class="text-danger"><strong>*</strong></i></label>
                <input type="number" class="form-control style-input" name="telefono" required placeholder="Ingresa el telefono del cliente">
            </div>
            <div class="form-group">
            <label>Email<i class="text-danger"><strong>*</strong></i></label>
                <input type="text" class="form-control style-input" name="email" required placeholder="Ingresa el email del cliente">
            </div>
             @include('custom.validate_save_form_ajax')
      </div>
      <div class="modal-footer style-modal-form">
            <button type="button" class="btn btn5" id="btn_hide_save_modal" data-dismiss="modal"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
            <button type="button" class="btn btn6" id="btnsavecliente"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
            </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL PARA ACTUALIZAR NUEVO CLIENTE-->
<div class="modal fade" id="ModalUpdateCliente" tabindex="-1" role="dialog" aria-labelledby="ModalSaveClienteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="">Actualizar cliente</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body style-modal-form">
        <form id="update_cliente" autocomplete="off">
          @csrf
          <input type="text" id="clienteupdate" name="clienteupdate" hidden="true">
          <div class="form-group">
          <label>Nombre<i class="text-danger"><strong>*</strong></i></label>
              <input type="text" class="form-control style-input" name="updnombre" id="updnombre" required placeholder="Ingresa el nombre del cliente">
          </div>
            <div class="form-group">
          <label>Direccion<i class="text-danger"><strong>*</strong></i></label>
              <input type="text" class="form-control style-input" name="upddireccion" id="upddireccion" required placeholder="Ingresa la direccion del cliente">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
              <label>Localidad</label>
                  <input type="text" class="form-control style-input" name="updlocalidad" id="updlocalidad" placeholder="Localidad">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
              <label>Provincia</label>
                  <input type="text" class="form-control style-input" name="updprovincia" id="updprovincia" placeholder="Provincia">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
              <label>C.P.</label>
                  <input type="text" class="form-control style-input" name="updcodigo_postal" id="updcodigo_postal" maxlength="10" placeholder="CP">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
              <label>DNI / CUIT</label>
                  <input type="text" class="form-control style-input" name="upddni_cuit" id="upddni_cuit" maxlength="13" placeholder="DNI o CUIT">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
              <label>Condición fiscal</label>
                  <select class="form-control style-input" name="updcondicion_fiscal" id="updcondicion_fiscal">
                      @foreach (\App\Models\Cliente::CONDICIONES_FISCALES as $valor => $etiqueta)
                          <option value="{{ $valor }}">{{ $etiqueta }}</option>
                      @endforeach
                  </select>
              </div>
            </div>
          </div>
            <div class="form-group">
          <label>Telefono<i class="text-danger"><strong>*</strong></i></label>
              <input type="number" class="form-control style-input" name="updtelefono" id="updtelefono" required placeholder="Ingresa el telefono del cliente">
          </div>
          <div class="form-group">
          <label>Email<i class="text-danger"><strong>*</strong></i></label>
              <input type="text" class="form-control style-input" name="updemail" id="updemail" required placeholder="Ingresa el email del cliente">
          </div>
          @include('custom.validate_update_form_ajax')
        </form>
      </div>
      <div class="modal-footer style-modal-form">
            <button type="button" class="btn btn5" id="hide_update_modal" data-bs-dismiss="modal"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
            <button type="button" class="btn btn6" id="btnupdatecliente"><i class="fas fa-check-circle text-success mr-2"></i>Actualizar cliente</button>
      </div>
    </div>
  </div>
</div>
