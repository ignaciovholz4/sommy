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
      <div>
	      <button type="button" class="btn btn6 btn-sm mr-3" id="btnBulkUploadBanner">
          <i class="fas fa-layer-group mr-2"></i>
          <strong> Subir varios videos de una</strong>
        </button>
        <input type="file" id="bulkBannerInput" accept="video/mp4,video/quicktime,video/webm" multiple style="display:none;">
      </div>
		</div>
	</div>
  <div id="bulkUploadStatusBanner" class="card mt-2" style="display:none;">
    <div class="card-body py-2">
      <strong class="d-block mb-2">Subiendo varios banners de video...</strong>
      <p class="text-muted small mb-2">Cada video se guarda como un banner nuevo, sin título/subtítulo (ocupa todo el ancho). Podés editarlos después desde la lista para agregarles texto u orden.</p>
      <ul id="bulkUploadListBanner" class="list-unstyled mb-0"></ul>
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
            <label class="d-block mb-2"><strong>Tipo de banner</strong></label>
            <div class="btn-group mb-3" role="group" id="bannerTipoGroup">
              <input type="radio" class="btn-check" name="tipo" id="tipoImagen" value="imagen" checked>
              <label class="btn btn-outline-primary" for="tipoImagen"><i class="fas fa-image me-1"></i> Imagen</label>
              <input type="radio" class="btn-check" name="tipo" id="tipoVideo" value="video">
              <label class="btn btn-outline-primary" for="tipoVideo"><i class="fas fa-video me-1"></i> Video</label>
            </div>
            <p class="text-muted small mb-3">
                <i class="fas fa-circle-info me-1"></i>
                Si cargás <strong>Título, Subtítulo o Texto del botón</strong>, el banner queda dividido: el texto a un lado y la imagen/video ocupando el otro 54%.
                Si dejás esos tres campos <strong>vacíos</strong>, la imagen/video pasa a ocupar <strong>todo el ancho del banner</strong> (el texto, si lo lleva, tiene que venir ya incluido en la propia imagen/video) —
                en ese caso subí un archivo más ancho (ver medidas para "sin texto" en cada hint de abajo).
            </p>
            <hr>
            <div id="bloqueDesktop">
              <span id="hintDesktop">Imagen horizontal, solo se ve en escritorio. Se recorta para llenar un rectángulo apaisado: con texto al lado usá algo cercano a 1400px x 740px (mínimo 900x475); sin texto (ocupa todo el ancho) usá algo más panorámico, cercano a 1900px x 700px (mínimo 1320x475).</span><br>
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
            </div>
            <hr>
            <div id="bloqueMovil">
              <span id="hintMovil">Imagen para celular (opcional: si no la cargás, se usa la de escritorio recortada, se ve peor). Se muestra completa, sin recortar — cualquier proporción funciona, pero se ve mejor si es más alta que ancha (vertical). Mínimo 700x350.</span><br>
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
