<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="margindivsection">
	<div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
		<div class="d-flex align-items-center">
      <div>
	      <button type="button" class="btn btn6 btn-sm mr-3" id="btnshowmodalColor">
          <i class="fa fa-archive mr-2"></i>
          <strong> Agregar color</strong>
        </button>
      </div>
		</div>
	</div>
</section>

<!--MODAL PARA AGREGAR O ACTUALIZAR EL NUEVO COLOR--> 
<div class="modal fade" id="ModalColor" tabindex="-1" role="dialog" aria-labelledby="ModalCategoriaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="">Agregar nuevo color</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body style-modal-form">
      {!! Form::open(['id'=>'save_color', 'autocomplete'=>'off'])!!}
            <input type="hidden" name="id" id="id">
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-box-open"></i>
                  </div>
              </div>
            	<input type="text" id="nombre" name="nombre" class="form-control style-input" placeholder="Nombre">
            </div>
            <label for="exampleColorInput" class="form-label">Elige tu color</label>
            <input type="color" class="form-control form-control-color" id="findColor" title="Elige tu color">
            <div class="input-group mb-3 mt-3">
            	<input type="text" id="hexadecimal" name="hexadecimal" class="form-control style-input" placeholder="Hexadecimal">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-file-signature"></i>
                  </div>
              </div>
            </div>
             @include('custom.validate_save_form_ajax')
      </div>
      <div class="modal-footer style-modal-form">
        <button type="button" class="btn btn5" data-bs-dismiss="modal"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
         <button type="submit" class="btn btn6" id="btnsavecolor"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
      </div>
      {!!Form::close()!!}
    </div>
  </div>
</div>
