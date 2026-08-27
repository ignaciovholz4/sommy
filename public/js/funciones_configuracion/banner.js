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

const imageInputMovil = document.querySelector("#movilfile");
const previewContainerMovil = document.getElementById('previewContainerMovil');
const bannerTipo = document.querySelector("#bannerTipo");
const hintDesktop = document.querySelector("#hintDesktop");
const hintMovil = document.querySelector("#hintMovil");

function actualizarTipoBanner() {
    const esVideo = bannerTipo.value === 'video';
    imageInput.setAttribute('accept', esVideo ? 'video/*' : 'image/*');
    imageInputMovil.setAttribute('accept', esVideo ? 'video/*' : 'image/*');
    hintDesktop.textContent = esVideo
        ? 'Video para escritorio (mp4, mov, webm u ogg)'
        : 'Tamaño recomendado: 1366px 517px (Imagen Horizontal)';
    hintMovil.textContent = esVideo
        ? 'Video para móvil (opcional: si no lo cargás, se usa el mismo de escritorio)'
        : 'Tamaño recomendado para movil: 1410px 1780px  (Imagen Vertical)';
    previewContainer.innerHTML = '';
    previewContainerMovil.innerHTML = '';
}
bannerTipo.addEventListener('change', actualizarTipoBanner);

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
                   { data: 'tipo', name: 'tipo' },
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
    console.log("Clicked modal banner");
    formBanner.reset();
    previewContainer.innerHTML = "";
    previewContainerMovil.innerHTML = "";
    bannerId.value = 0;
    bannerTipo.value = 'imagen';
    actualizarTipoBanner();
    bannerLabel.textContent = 'Agregar nuevo banner';
    const myModalsavebanner = new bootstrap.Modal(idmodalbannercategory);
    myModalsavebanner.show();
});

btnsavebanner.addEventListener("click",(e) => {
    e.preventDefault();
    let fileBanner = imageInput.files[0];
    let fileBannerMovil = imageInputMovil.files[0];
    console.log(imageInputMovil);

    const formData = new FormData();
    if (fileBanner) formData.append("imagen", fileBanner); // Get the selected file
    formData.append("name", nombre.value);
    formData.append("tipo", bannerTipo.value);
    formData.append("bannerId", bannerId.value);
    if (fileBannerMovil) formData.append("imageMovil", fileBannerMovil);

    console.log(imageInput.files[0]);
    console.log(fileBannerMovil);
    //console.log(fileBannerMovil.files[0]);

    let url = "/savebanner";

    seend_data(url,formData).then((resp) => {
        console.log(resp);
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
  const MIN_WIDTH = 1366;//1239;
  const MIN_HEIGHT = 517//467;
  const file = this.files[0];
  if (bannerTipo.value === 'video') {
    previewContainer.innerHTML = '';
    if (file) {
      const video = document.createElement('video');
      video.src = URL.createObjectURL(file);
      video.className = 'img-thumbnail';
      video.style.maxWidth = '100%';
      video.controls = true;
      previewContainer.appendChild(video);
    }
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
  const MIN_WIDTH = 1410;//
  const MIN_HEIGHT = 1780//
  const file = this.files[0];
  console.log(file);
  if (bannerTipo.value === 'video') {
    previewContainerMovil.innerHTML = '';
    if (file) {
      const video = document.createElement('video');
      video.src = URL.createObjectURL(file);
      video.className = 'img-thumbnail';
      video.style.maxWidth = '100%';
      video.controls = true;
      previewContainerMovil.appendChild(video);
    }
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
            imageInput.value = ''; //reset input file banner
          }
        };
      };
      reader.readAsDataURL(file);// Read the file as a data URL
    }
  }
});
/**************************************************** */
const edit_banner = (id) => {
  console.log(id);
  const formData = new FormData();
  formData.append("id", id);
  let url = "/getByIdbanner";
  seend_data(url,formData).then((resp) => {
    console.log(resp);
    console.log(resp.data[0]);
    bannerLabel.textContent = 'Actualizar el banner';
    let dataRow = resp.data[0];
    bannerId.value = dataRow.banner_id;
    nombre.value = dataRow.name;
    bannerTipo.value = dataRow.tipo || 'imagen';
    actualizarTipoBanner();

    const esVideo = bannerTipo.value === 'video';
    previewContainer.innerHTML = '';
    const elDesktop = document.createElement(esVideo ? 'video' : 'img');
    elDesktop.src = `../imagenes/banner/${dataRow.name_image}`;
    elDesktop.className = 'img-thumbnail';
    elDesktop.style.maxWidth = '100%';
    elDesktop.style.height = 'auto';
    if (esVideo) elDesktop.controls = true; else { elDesktop.alt = 'imagen seleccionada'; }
    previewContainer.appendChild(elDesktop);
    /*********************************************** */
    previewContainerMovil.innerHTML = '';
    const elMovil = document.createElement(esVideo ? 'video' : 'img');
    elMovil.src = `../imagenes/banner/${dataRow.name_image_movil}`;
    elMovil.className = 'img-thumbnail';
    elMovil.style.maxWidth = '100%';
    elMovil.style.height = 'auto';
    if (esVideo) elMovil.controls = true; else { elMovil.alt = 'imagen seleccionada'; }
    previewContainerMovil.appendChild(elMovil);

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