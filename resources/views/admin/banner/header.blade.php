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
            	<input type="text" id="name" name="name" class="form-control style-input" placeholder="Nombre interno (no se ve en la web)">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-heading"></i>
                  </div>
              </div>
              <input type="text" id="bannerTitulo" name="titulo" class="form-control style-input" placeholder="Título (ej: 5% extra en toda la tienda)" maxlength="120">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-align-left"></i>
                  </div>
              </div>
              <input type="text" id="bannerSubtitulo" name="subtitulo" class="form-control style-input" placeholder="Subtítulo (opcional)" maxlength="200">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-hand-pointer"></i>
                  </div>
              </div>
              <input type="text" id="bannerBotonTexto" name="boton_texto" class="form-control style-input" placeholder="Texto del botón (ej: Ver ofertas)" maxlength="40">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-link"></i>
                  </div>
              </div>
              <input type="text" id="bannerBotonUrl" name="boton_url" class="form-control style-input" placeholder="Link del botón (ej: /productos)">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-sort-numeric-down"></i>
                  </div>
              </div>
              <input type="number" id="bannerOrden" name="orden" class="form-control style-input" placeholder="Orden (0 = primero)" min="0">
            </div>
            <hr>
            <span id="hintDesktop">Imagen para escritorio. Se recorta para llenar un rectángulo apaisado: usá algo cercano a 1400px x 740px (mínimo 900x475).</span><br>
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
            <span id="hintMovil">Imagen para móvil (opcional: si no la cargás, se usa la de escritorio, recortada). Se recorta para llenar una franja ancha y baja: usá algo cercano a 1000px x 500px (mínimo 700x350).</span><br>
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
