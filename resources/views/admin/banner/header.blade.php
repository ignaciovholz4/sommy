<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="margindivsection">
	<div class="d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
		<div class="d-flex align-items-center">
      <div>
	      <button type="button" class="btn btn6 btn-sm mr-3" id="btnshowmodalbanner">
          <i class="fa fa-archive mr-2"></i>
          <strong> Agregar nuevo banner</strong>
        </button>
      </div>
		</div>
	</div>
</section>

<!--MODAL PARA AGREGAR NUEVO BANNER-->
<div class="modal fade" id="ModalBanner" tabindex="-1" role="dialog" aria-labelledby="ModalBannerLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header style-modal-form">
        <h5 class="modal-title" id="bannerLabel">Agregar nuevo banner</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body style-modal-form">
        <form action="" id="formBanner">
            <input type="hidden" name="bannerId" id="bannerId" value="0">
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-file-signature"></i>
                  </div>
              </div>
            	<input type="text" id="name" name="name" class="form-control style-input" placeholder="Nombre">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-photo-video"></i>
                  </div>
              </div>
              <select id="bannerTipo" name="tipo" class="form-control style-input">
                <option value="imagen">Imagen</option>
                <option value="video">Video</option>
              </select>
            </div>
            <span id="hintDesktop">Tamaño recomendado:	1366px 517px (Imagen Horizontal)</span><br>
            <div class="input-group mb-3">
              <input type="file" id="file" name="file" class="form-control" accept="image/*">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-image"></i>
                  </div>
              </div>
            </div>
            <p class="text-muted mt-3">La vista previa aparecerá aquí...</p>
            <div id="previewContainer" class="mt-3 mb-2">
            </div>
            <hr>
            <span id="hintMovil">Tamaño recomendado para movil: 1410px 1780px  (Imagen Vertical)</span><br>
            <div class="input-group mb-3">
              <input type="file" id="movilfile" name="movilfile" class="form-control" accept="image/*">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-image"></i>
                  </div>
              </div>
            </div>
            <p class="text-muted mt-3">La vista previa aparecerá aquí...</p>
            <div id="previewContainerMovil" class="mt-3 mb-2">
            </div>
             @include('custom.validate_save_form_ajax')
        </form>
      </div>
      <div class="modal-footer style-modal-form">
        <button type="button" class="btn btn5" data-bs-dismiss="modal" id="btnhidebanner" ><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
         <button type="submit" class="btn btn6" id="btnsavebanner"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>
