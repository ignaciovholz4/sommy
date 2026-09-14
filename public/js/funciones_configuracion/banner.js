const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const btnsavebanner = document.querySelector('#btnsavebanner');
const btnshowmodalbanner = document.querySelector("#btnshowmodalbanner");
const formBanner = document.querySelector('#formBanner');
const btnclosemodalBanner = document.querySelector('#btnhidebanner');
const previewContainer = document.getElementById('previewContainer');
const idmodalbannercategory = document.querySelector("#ModalBanner");
const bannerId = document.querySelector("#bannerId");
const bannerLabel = document.querySelector("#bannerLabel");

const imageInput = document.getElementById("file");
const nombre = document.getElementById("name");
const bannerTitulo = document.querySelector("#bannerTitulo");
const bannerSubtitulo = document.querySelector("#bannerSubtitulo");
const bannerBotonTexto = document.querySelector("#bannerBotonTexto");
const bannerBotonUrl = document.querySelector("#bannerBotonUrl");
const bannerOrden = document.querySelector("#bannerOrden");

const imageInputMovil = document.querySelector("#movilfile");
const previewContainerMovil = document.getElementById('previewContainerMovil');

const tipoImagenRadio = document.querySelector('#tipoImagen');
const tipoVideoRadio = document.querySelector('#tipoVideo');
const hintDesktop = document.querySelector('#hintDesktop');
const hintMovil = document.querySelector('#hintMovil');

const esTipoVideo = () => tipoVideoRadio.checked;

const fnAplicarTipoBanner = () => {
    if (esTipoVideo()) {
        imageInput.accept = 'video/mp4,video/quicktime,video/webm';
        imageInputMovil.accept = 'video/mp4,video/quicktime,video/webm';
        hintDesktop.textContent = 'Video horizontal, solo se ve en escritorio. Con texto al lado usá formato apaisado ~1400x740; sin texto (ocupa todo el ancho) usá algo más panorámico, ~1900x700. Máximo 25MB — comprimilo en 720p (no 4K) antes de subirlo, si no la home va a cargar muy lento en el celular.';
        hintMovil.textContent = 'Video vertical para celular (opcional: si no lo cargás, se usa el de escritorio). Formato vertical (ej: 1080x1920). Máximo 25MB, comprimido en 720p.';
    } else {
        imageInput.accept = 'image/*';
        imageInputMovil.accept = 'image/*';
        hintDesktop.textContent = 'Imagen horizontal, solo se ve en escritorio. Se recorta para llenar un rectángulo apaisado: usá algo cercano a 1400px x 740px (mínimo 900x475).';
        hintMovil.textContent = 'Imagen para celular (opcional: si no la cargás, se usa la de escritorio recortada, se ve peor). Se muestra completa, sin recortar — cualquier proporción funciona, pero se ve mejor si es más alta que ancha (vertical). Mínimo 700x350.';
    }
};

tipoImagenRadio.addEventListener('change', fnAplicarTipoBanner);
tipoVideoRadio.addEventListener('change', fnAplicarTipoBanner);

const fnPreviewVideo = (file, container) => {
    container.innerHTML = '';
    const video = document.createElement('video');
    video.src = URL.createObjectURL(file);
    video.controls = true;
    video.muted = true;
    video.style.maxWidth = '100%';
    video.style.borderRadius = '6px';
    container.appendChild(video);
};

$(document).ready( function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
    $('#banner_table').DataTable({
           "autoWidth": false,
           processing: true,
           serverSide: true,
           ajax: {
             url:'/showbanner',
            type: 'GET',
           },
           columns: [
                   { data: 'banner_id', name: 'banner_id'},
                   { data: 'name', name: 'name' },
                   { data: 'titulo', name: 'titulo' },
                   { data: 'orden', name: 'orden' },
                   { data: 'name_image', name: 'name_image' },
                   { data: 'name_image_movil', name: 'name_image_movil' },
                   {data: 'action', name:'action'}
                 ],
          order: [[0, 'desc']]
        });
    });
});

const seend_data = async (url,form) => {
  try {
    let seend = await fetch(url, {
      method: "post",
      headers: {
          'X-CSRF-TOKEN': token,
      },
      body: form,
    });
    let data = await seend.json();
    console.log(data);
    return data;
  } catch (error) {
      console.log(error);
  }
}
btnshowmodalbanner.addEventListener('click', (e) =>{
    e.preventDefault();
    formBanner.reset();
    previewContainer.innerHTML = "";
    previewContainerMovil.innerHTML = "";
    bannerId.value = 0;
    bannerLabel.textContent = 'Agregar nuevo banner';
    tipoImagenRadio.checked = true;
    fnAplicarTipoBanner();
    const myModalsavebanner = new bootstrap.Modal(idmodalbannercategory);
    myModalsavebanner.show();
});

btnsavebanner.addEventListener("click",(e) => {
    e.preventDefault();
    let fileBanner = imageInput.files[0];
    let fileBannerMovil = imageInputMovil.files[0];

    const formData = new FormData();
    if (fileBanner) formData.append("imagen", fileBanner);
    formData.append("name", nombre.value);
    formData.append("tipo", esTipoVideo() ? 'video' : 'imagen');
    formData.append("titulo", bannerTitulo.value);
    formData.append("subtitulo", bannerSubtitulo.value);
    formData.append("boton_texto", bannerBotonTexto.value);
    formData.append("boton_url", bannerBotonUrl.value);
    formData.append("orden", bannerOrden.value || 0);
    formData.append("bannerId", bannerId.value);
    if (fileBannerMovil) formData.append("imageMovil", fileBannerMovil);

    let url = "/savebanner";

    seend_data(url,formData).then((resp) => {
      if(resp.status === 1) {
        fnLoadTable();
        toastr.success(resp.message);
        $('#formBanner').trigger("reset");
        btnclosemodalBanner.click();
      }
      if(resp.status === 0) {

        saveprintErrorMsg(resp.message);
        window.setTimeout(function() {
          $(".print-save-error-msg").slideUp(function() {
          });
        },  5000);
      }
    });

});

const fnLoadTable = () => {
  let TableRefresh = $('#banner_table').dataTable();
  TableRefresh.fnDraw(false);
}
let saveprintErrorMsg = (msg) => {
  $(".print-save-error-msg").find("ul").html('');
  $(".print-save-error-msg").css('display','block');
  $.each(msg, function(key, value) {
    $(".print-save-error-msg").find("ul").append('<li>'+value+'</li>');
  });
}

imageInput.addEventListener('change', function () {
  const MIN_WIDTH = 900;
  const MIN_HEIGHT = 475;
  const file = this.files[0];
  if (file && esTipoVideo()) {
    fnPreviewVideo(file, previewContainer);
    return;
  }
  if (file) {
    if (file.type.startsWith('image/')) {
      const img = new Image();
      const reader = new FileReader();
      reader.onload = function (e) { // Load the image and get its dimensions
        img.src = e.target.result;
        img.onload = function () {
          const width = img.width;
          const height = img.height;
          if (img.width >= MIN_WIDTH && img.height >= MIN_HEIGHT) {
            previewContainer.innerHTML = '';
            const img = document.createElement('img');
            img.src = e.target.result; // Set the image source
            img.alt = 'imagen seleccionada';
            img.className = 'img-thumbnail'; // Add Bootstrap 5 class
            img.style.maxWidth = '100%'; // Make sure it fits within the container
            img.style.height = 'auto';
            // Append the image to the preview container
            previewContainer.appendChild(img);
            // Display the dimensions
            console.log(`Width: ${width}px, Height: ${height}px`)
          }else {
            previewContainer.textContent = `Las dimensiones de la imagen son demasiado pequeñas. Mínimo requerido: ${MIN_WIDTH}x${MIN_HEIGHT}. Actual: ${img.width}x${img.height}.`;
            imageInput.value = ''; //reset input file banner
          }
        };
      };
      reader.readAsDataURL(file);// Read the file as a data URL
    }else{
      previewContainer.textContent = 'Please select a valid image file.';
    }
  } else {
    previewContainer.textContent = 'Please select an image file.';
  }
});

/**************************************************** */
imageInputMovil.addEventListener('change', function () {
  const MIN_WIDTH = 700;
  const MIN_HEIGHT = 350;
  const file = this.files[0];
  if (file && esTipoVideo()) {
    fnPreviewVideo(file, previewContainerMovil);
    return;
  }
  if (file) {
    if (file.type.startsWith('image/')) {
      const img = new Image();
      const reader = new FileReader();
      reader.onload = function (e) { // Load the image and get its dimensions
        img.src = e.target.result;
        img.onload = function () {
          const width = img.width;
          const height = img.height;
          if (img.width >= MIN_WIDTH && img.height >= MIN_HEIGHT) {
            previewContainerMovil.innerHTML = '';
            const img = document.createElement('img');
            img.src = e.target.result; // Set the image source
            img.alt = 'imagen seleccionada';
            img.className = 'img-thumbnail'; // Add Bootstrap 5 class
            img.style.maxWidth = '100%'; // Make sure it fits within the container
            img.style.height = 'auto';
            // Append the image to the preview container
            previewContainerMovil.appendChild(img);
            // Display the dimensions
            console.log(`Width: ${width}px, Height: ${height}px`)
          }else {
            previewContainerMovil.textContent = `Las dimensiones de la imagen son demasiado pequeñas. Mínimo requerido: ${MIN_WIDTH}x${MIN_HEIGHT}. Actual: ${img.width}x${img.height}.`;
            imageInputMovil.value = ''; //reset input file banner movil
          }
        };
      };
      reader.readAsDataURL(file);// Read the file as a data URL
    }
  }
});
/**************************************************** */
const edit_banner = (id) => {
  const formData = new FormData();
  formData.append("id", id);
  let url = "/getByIdbanner";
  seend_data(url,formData).then((resp) => {
    let dataRow = resp.data[0];
    bannerLabel.textContent = 'Actualizar el banner';
    bannerId.value = dataRow.banner_id;
    nombre.value = dataRow.name;
    bannerTitulo.value = dataRow.titulo || '';
    bannerSubtitulo.value = dataRow.subtitulo || '';
    bannerBotonTexto.value = dataRow.boton_texto || '';
    bannerBotonUrl.value = dataRow.boton_url || '';
    bannerOrden.value = dataRow.orden || 0;

    const esVideo = dataRow.tipo === 'video';
    tipoImagenRadio.checked = !esVideo;
    tipoVideoRadio.checked = esVideo;
    fnAplicarTipoBanner();

    previewContainer.innerHTML = '';
    if (esVideo) {
      const vidDesktop = document.createElement('video');
      vidDesktop.src = `../imagenes/banner/${dataRow.name_image}`;
      vidDesktop.controls = true;
      vidDesktop.muted = true;
      vidDesktop.style.maxWidth = '100%';
      previewContainer.appendChild(vidDesktop);
    } else {
      const elDesktop = document.createElement('img');
      elDesktop.src = `../imagenes/banner/${dataRow.name_image}`;
      elDesktop.alt = 'imagen seleccionada';
      elDesktop.className = 'img-thumbnail';
      elDesktop.style.maxWidth = '100%';
      elDesktop.style.height = 'auto';
      previewContainer.appendChild(elDesktop);
    }
    /*********************************************** */
    previewContainerMovil.innerHTML = '';
    if (esVideo) {
      const vidMovil = document.createElement('video');
      vidMovil.src = `../imagenes/banner/${dataRow.name_image_movil}`;
      vidMovil.controls = true;
      vidMovil.muted = true;
      vidMovil.style.maxWidth = '100%';
      previewContainerMovil.appendChild(vidMovil);
    } else {
      const elMovil = document.createElement('img');
      elMovil.src = `../imagenes/banner/${dataRow.name_image_movil}`;
      elMovil.alt = 'imagen seleccionada';
      elMovil.className = 'img-thumbnail';
      elMovil.style.maxWidth = '100%';
      elMovil.style.height = 'auto';
      previewContainerMovil.appendChild(elMovil);
    }

    const myModalShow = new bootstrap.Modal(idmodalbannercategory);
    myModalShow.show();
  });
}

const delete_banner = (id) => {
  Swal.fire({
    title: 'Estas seguro de eliminar la imagen del banner?',
    text: "La imagen ya no estara disponible!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.value) {
      let url = "/deleteByIdbanner";
      let formData = new FormData();
      formData.append('bannerId', id);
      seend_data(url,formData).then((resp) => {
          console.log(resp);
        if(resp.status === 1){
          toastr.success(resp.message);
          fnLoadTable();
        }
        if(resp.status === 0){
          toastr.error(resp.message);
        }
      });
    }

  });
}

/**************** SUBIDA MÚLTIPLE DE BANNERS DE VIDEO ****************/
const bulkBannerInput = document.querySelector('#bulkBannerInput');
const btnBulkUploadBanner = document.querySelector('#btnBulkUploadBanner');
const bulkUploadStatusBanner = document.querySelector('#bulkUploadStatusBanner');
const bulkUploadListBanner = document.querySelector('#bulkUploadListBanner');

btnBulkUploadBanner?.addEventListener('click', () => bulkBannerInput.click());

bulkBannerInput?.addEventListener('change', async function () {
    const files = Array.from(this.files);
    if (!files.length) return;

    bulkUploadStatusBanner.style.display = '';
    bulkUploadListBanner.innerHTML = '';
    const filas = files.map((file) => {
        const li = document.createElement('li');
        li.className = 'mb-1';
        li.innerHTML = `<i class="fas fa-clock text-muted mr-2"></i> ${file.name} <span class="text-muted small">(${(file.size / 1024 / 1024).toFixed(1)}MB)</span>`;
        bulkUploadListBanner.appendChild(li);
        return li;
    });

    let subidos = 0;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        filas[i].innerHTML = `<i class="fas fa-spinner fa-spin text-primary mr-2"></i> ${file.name} — subiendo...`;

        const formData = new FormData();
        formData.append('imagen', file);
        formData.append('name', file.name.replace(/\.[^.]+$/, ''));
        formData.append('tipo', 'video');
        formData.append('titulo', '');
        formData.append('subtitulo', '');
        formData.append('boton_texto', '');
        formData.append('boton_url', '');
        formData.append('orden', i);
        formData.append('bannerId', 0);

        const resp = await seend_data('/savebanner', formData);

        if (resp && resp.status === 1) {
            filas[i].innerHTML = `<i class="fas fa-check text-success mr-2"></i> ${file.name} — listo`;
            subidos++;
        } else {
            const motivo = resp && resp.message ? (Array.isArray(resp.message) ? resp.message.join(', ') : resp.message) : 'Error desconocido';
            filas[i].innerHTML = `<i class="fas fa-triangle-exclamation text-danger mr-2"></i> ${file.name} — <span class="text-danger">${motivo}</span>`;
        }
    }

    fnLoadTable();
    toastr[subidos === files.length ? 'success' : 'warning'](`Se subieron ${subidos} de ${files.length} banners`);
    bulkBannerInput.value = '';
});
