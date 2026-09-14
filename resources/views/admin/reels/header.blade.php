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
            <p class="text-muted small">Subí el video del reel (descargalo desde la app de Instagram: Compartir &rsaquo; Guardar video, o desde Meta Business Suite). Se reproduce con un video propio, sin el marco ni los botones de Instagram.</p>
            <span id="hintVideo">Formato vertical (como el reel original). Máximo 100MB, pero para que cargue bien en el celular de tus clientes tratá de que pese <strong>bajo 10-15MB</strong> (en CapCut/InShot/el editor que uses: exportar en 720p en vez de 4K/1080p baja mucho el peso sin notarse la diferencia en el celular). mp4, mov o webm.</span><br>
            <div class="input-group mb-3">
              <input type="file" id="reelVideo" name="video" class="form-control" accept="video/mp4,video/quicktime,video/webm">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-video"></i>
                  </div>
              </div>
            </div>
            <div id="reelPreview" class="mt-3 mb-3"></div>
            <span>Imagen de portada <small class="text-muted">(opcional: se ve mientras el video no terminó de cargar; si no cargás una, se ve en negro hasta que arranca)</small></span><br>
            <div class="input-group mb-3">
              <input type="file" id="reelPoster" name="poster" class="form-control" accept="image/*">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-image"></i>
                  </div>
              </div>
            </div>
            <hr>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-align-left"></i>
                  </div>
              </div>
              <input type="text" id="reelTitulo" name="titulo" class="form-control style-input" placeholder="Título/leyenda que se ve sobre el video (opcional)" maxlength="120">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fab fa-instagram"></i>
                  </div>
              </div>
              <input type="text" id="reelUrl" name="url" class="form-control style-input" placeholder="Link al posteo de Instagram (opcional, para el ícono que lleva al perfil)">
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                  <div class="input-group-text style-icon-fas">
                      <i class="fas fa-sort-numeric-down"></i>
                  </div>
              </div>
              <input type="number" id="reelOrden" name="orden" class="form-control style-input" placeholder="Orden (0 = primero)" min="0">
            </div>
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
