<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="margindivsection">
	<div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
		<div class="d-flex align-items-center">
      <div>
	      <button type="button" class="btn btn6 btn-sm mr-3" id="btnshowModalVariante">
          <i class="fa fa-archive mr-2"></i>
          <strong> Agregar nueva Variante</strong>
        </button>
      </div>
		</div>
	</div>
</section>

<!--MODAL PARA AGREGAR O ACTUALIZAR LA NUEVA MARCA--> 
<div class="modal fade" id="ModalVariante" tabindex="-1" role="dialog" aria-labelledby="ModalVarianteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="">Agregar nueva variante</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body style-modal-form">
        <form id="save_variante">
          <div>
            <input type="hidden" name="id" id="id">
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-box-open"></i>
                  </div>
              </div>
            	<input type="text" id="name" name="name" class="form-control style-input" placeholder="Nombre" >
            </div>
            <div class="input-group mb-3 ">
              <select class="form-select style-input" aria-label="Default select example" id="option_type" name="option_type" required>
                <option value="">Seleccionar tipo</option>
                <option value="Color">Color</option>
                <option value="Boton">Boton</option>
                <!--option value="Imagen">Imagen</option>-->
              </select>
            </div>
          </div>
          <div>
            <hr>
            <p style="margin-bottom: 0px;">Variaciones</p>
            <small style="color:red;margin-top: 0px;">Al terminar de escribir la variacion dar un enter</small>
            <div class="mb-3">
              <input type="text" class="form-control style-input" id="addVariaciones" placeholder="Ingresar el nombre">
            </div>
            <div class="mb-3 showTags">
            </div>
          </div>
          @include('custom.validate_save_form_ajax')
        </form>
      </div>
      <div class="modal-footer style-modal-form">
        <button type="button" class="btn btn5" data-bs-dismiss="modal"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
         <button type="submit" class="btn btn6" id="btnsavemarca"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>