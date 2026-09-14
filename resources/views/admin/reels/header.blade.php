<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="margindivsection">
	<div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
		<div class="d-flex align-items-center">
      <div>
	      <button type="button" class="btn btn6 btn-sm mr-3" id="btnshowmodalreel">
          <i class="fab fa-instagram mr-2"></i>
          <strong> Agregar reel</strong>
        </button>
      </div>
		</div>
	</div>
</section>

<!--MODAL PARA AGREGAR/EDITAR UN REEL-->
<div class="modal fade" id="ModalReel" tabindex="-1" role="dialog" aria-labelledby="ModalReelLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="reelLabel">Agregar reel</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body style-modal-form">
        <form action="" id="formReel">
            <input type="hidden" name="reelId" id="reelId" value="0">
            <p class="text-muted small">Pegá el link del reel/publicación de Instagram (ej: https://www.instagram.com/reel/DdRwzc3JJm3/). Se embebe con el reproductor oficial de Instagram, no hace falta subir ningún video.</p>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fab fa-instagram"></i>
                  </div>
              </div>
            	<input type="text" id="reelUrl" name="url" class="form-control style-input" placeholder="https://www.instagram.com/reel/XXXXXXXXXXX/">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-align-left"></i>
                  </div>
              </div>
              <input type="text" id="reelTitulo" name="titulo" class="form-control style-input" placeholder="Título/leyenda que se ve arriba del reel (opcional)" maxlength="120">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-sort-numeric-down"></i>
                  </div>
              </div>
              <input type="number" id="reelOrden" name="orden" class="form-control style-input" placeholder="Orden (0 = primero)" min="0">
            </div>
            <div id="reelPreview" class="mt-3"></div>
             @include('custom.validate_save_form_ajax')
        </form>
      </div>
      <div class="modal-footer style-modal-form">
        <button type="button" class="btn btn5" data-bs-dismiss="modal" id="btnhidereel"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
         <button type="submit" class="btn btn6" id="btnsavereel"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>
