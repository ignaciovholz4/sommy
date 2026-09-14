const tokenReel = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const btnsavereel = document.querySelector('#btnsavereel');
const btnshowmodalreel = document.querySelector('#btnshowmodalreel');
const formReel = document.querySelector('#formReel');
const btnclosemodalReel = document.querySelector('#btnhidereel');
const idmodalreel = document.querySelector('#ModalReel');
const reelId = document.querySelector('#reelId');
const reelLabel = document.querySelector('#reelLabel');
const reelUrl = document.querySelector('#reelUrl');
const reelTitulo = document.querySelector('#reelTitulo');
const reelOrden = document.querySelector('#reelOrden');
const reelPreview = document.querySelector('#reelPreview');
const reelVideoInput = document.querySelector('#reelVideo');

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#reel_table').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '/showreel',
            type: 'GET',
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'titulo', name: 'titulo' },
            { data: 'video', name: 'video' },
            { data: 'orden', name: 'orden' },
            { data: 'action', name: 'action' },
        ],
        order: [[3, 'asc']]
    });
});

const seend_data_reel = async (url, form) => {
    try {
        let res = await fetch(url, {
            method: 'post',
            headers: { 'X-CSRF-TOKEN': tokenReel },
            body: form,
        });
        return await res.json();
    } catch (error) {
        console.log(error);
    }
};

btnshowmodalreel.addEventListener('click', (e) => {
    e.preventDefault();
    formReel.reset();
    reelPreview.innerHTML = '';
    reelId.value = 0;
    reelLabel.textContent = 'Agregar reel';
    new bootstrap.Modal(idmodalreel).show();
});

reelVideoInput.addEventListener('change', function () {
    const file = this.files[0];
    reelPreview.innerHTML = '';
    if (!file) return;

    const url = URL.createObjectURL(file);
    const video = document.createElement('video');
    video.src = url;
    video.controls = true;
    video.muted = true;
    video.style.maxWidth = '260px';
    video.style.borderRadius = '8px';
    reelPreview.appendChild(video);
});

const btnsavereelHtmlOriginal = btnsavereel.innerHTML;

btnsavereel.addEventListener('click', (e) => {
    e.preventDefault();

    const formData = new FormData();
    const fileVideo = reelVideoInput.files[0];
    if (fileVideo) formData.append('video', fileVideo);
    const filePoster = document.getElementById('reelPoster').files[0];
    if (filePoster) formData.append('poster', filePoster);
    formData.append('url', reelUrl.value);
    formData.append('titulo', reelTitulo.value);
    formData.append('orden', reelOrden.value || 0);
    formData.append('reelId', reelId.value);

    // Un video pesado puede tardar varios minutos en subir: avisar que no está trabado.
    btnsavereel.disabled = true;
    btnsavereel.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subiendo video, puede tardar unos minutos...';

    seend_data_reel('/savereel', formData).then((resp) => {
        btnsavereel.disabled = false;
        btnsavereel.innerHTML = btnsavereelHtmlOriginal;

        if (resp.status === 1) {
            fnLoadTableReel();
            toastr.success(resp.message);
            formReel.reset();
            reelPreview.innerHTML = '';
            btnclosemodalReel.click();
        }
        if (resp.status === 0) {
            saveprintErrorMsg(resp.message);
            window.setTimeout(function () {
                $('.print-save-error-msg').slideUp(function () {});
            }, 5000);
        }
    });
});

const fnLoadTableReel = () => {
    $('#reel_table').dataTable().fnDraw(false);
};

const edit_reel = (id) => {
    const formData = new FormData();
    formData.append('id', id);
    seend_data_reel('/getByIdreel', formData).then((resp) => {
        const row = resp.data[0];
        reelLabel.textContent = 'Actualizar el reel';
        reelId.value = row.id;
        reelUrl.value = row.url || '';
        reelTitulo.value = row.titulo || '';
        reelOrden.value = row.orden || 0;

        reelPreview.innerHTML = '';
        if (row.video) {
            const video = document.createElement('video');
            video.src = `../imagenes/reels/${row.video}`;
            video.controls = true;
            video.muted = true;
            video.style.maxWidth = '260px';
            video.style.borderRadius = '8px';
            reelPreview.appendChild(video);
        }

        new bootstrap.Modal(idmodalreel).show();
    });
};

const delete_reel = (id) => {
    Swal.fire({
        title: '¿Eliminar este reel del carrusel?',
        text: 'Ya no se va a mostrar en la web.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            const formData = new FormData();
            formData.append('reelId', id);
            seend_data_reel('/deleteByIdreel', formData).then((resp) => {
                if (resp.status === 1) {
                    toastr.success(resp.message);
                    fnLoadTableReel();
                }
                if (resp.status === 0) {
                    toastr.error(resp.message);
                }
            });
        }
    });
};
