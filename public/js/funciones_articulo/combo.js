document.addEventListener('DOMContentLoaded', function () {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const productos = window.PRODUCTOS_COMBO || [];

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': token }
    });

    const table = $('#combo_table').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: { url: '/showcombos', type: 'GET' },
        columns: [
            { data: 'imagen_fmt', name: 'imagen_fmt', orderable: false, searchable: false },
            { data: 'nombre', name: 'nombre' },
            { data: 'descuento_fmt', name: 'combo_descuento_pct', searchable: false },
            { data: 'relacionados_fmt', name: 'relacionados', orderable: false },
            { data: 'regalos_fmt', name: 'regalos', orderable: false },
            { data: 'precio_venta_fmt', name: 'precio_venta_fmt', orderable: false, searchable: false },
            { data: 'ganancia_fmt', name: 'ganancia_fmt', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']]
    });
    window.refreshComboTable = () => table.ajax.reload(null, false);

    const modalEl = document.getElementById('ModalCombo');
    const modal = new bootstrap.Modal(modalEl);
    const $productoSelect = $('#combo_producto_id');
    const $relacionadosSelect = $('#combo_relacionados');
    const regalosRows = document.getElementById('combo_regalos_rows');

    $productoSelect.select2({ placeholder: 'Elegí un producto...', width: '100%', dropdownParent: $('#ModalCombo') });
    $relacionadosSelect.select2({ placeholder: 'Buscar productos relacionados...', width: '100%', dropdownParent: $('#ModalCombo') });

    function opcionesProductoHtml(seleccionadoId) {
        return productos.map(p => `<option value="${p.id}" ${String(p.id) === String(seleccionadoId) ? 'selected' : ''}>${p.nombre}</option>`).join('');
    }

    function agregarRegaloRow(regaloId, cantidad) {
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-2 align-items-center combo-regalo-row';
        row.innerHTML = `
            <select class="form-control regalo-producto">${opcionesProductoHtml(regaloId)}</select>
            <input type="number" class="form-control regalo-cantidad" min="1" max="99" value="${cantidad || 1}" style="max-width:100px;" title="Cantidad">
            <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-regalo"><i class="fas fa-times"></i></button>
        `;
        regalosRows.appendChild(row);
        $(row.querySelector('.regalo-producto')).select2({ width: '220px', dropdownParent: $('#ModalCombo') });
        row.querySelector('.btn-quitar-regalo').addEventListener('click', () => row.remove());
    }

    document.getElementById('btnAgregarRegaloRow').addEventListener('click', () => agregarRegaloRow(null, 1));

    /* ---------- Imágenes del combo ----------
       Van contra el producto ancla, así que se suben apenas hay un producto
       elegido (no hace falta guardar el combo antes). La primera de la lista
       es la que se muestra en la vidriera. */
    const imagenesBox = document.getElementById('combo_imagenes_box');
    const imagenesVacio = document.getElementById('combo_imagenes_vacio');
    const imagenesGrid = document.getElementById('combo_imagenes_grid');
    const imagenesInput = document.getElementById('combo_imagenes_input');
    let imagenesCombo = [];

    function productoActual() {
        return $productoSelect.val();
    }

    function pintarImagenes() {
        const hayProducto = !!productoActual();
        imagenesBox.style.display = hayProducto ? '' : 'none';
        imagenesVacio.style.display = hayProducto ? 'none' : '';

        if (!hayProducto) {
            imagenesGrid.innerHTML = '';
            return;
        }

        if (!imagenesCombo.length) {
            imagenesGrid.innerHTML = '<div class="text-muted small">Sin imágenes propias: el combo muestra la foto del producto.</div>';
            return;
        }

        imagenesGrid.innerHTML = imagenesCombo.map((img, i) => `
            <div class="combo-img-thumb" data-id="${img.id}">
                ${i === 0 ? '<span class="combo-img-principal">Vidriera</span>' : ''}
                <img src="${img.url}" alt="${img.alt || ''}">
                <div class="combo-img-acciones">
                    <button type="button" class="combo-img-mover" data-dir="-1" ${i === 0 ? 'disabled' : ''} title="Mover a la izquierda"><i class="fas fa-arrow-left"></i></button>
                    <button type="button" class="combo-img-borrar" title="Eliminar"><i class="fas fa-trash"></i></button>
                    <button type="button" class="combo-img-mover" data-dir="1" ${i === imagenesCombo.length - 1 ? 'disabled' : ''} title="Mover a la derecha"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        `).join('');
    }

    function cargarImagenes(productoId) {
        imagenesCombo = [];
        pintarImagenes();
        if (!productoId) return;

        $.get('/combo-imagenes/' + productoId, function (data) {
            imagenesCombo = data.imagenes || [];
            pintarImagenes();
        });
    }

    $productoSelect.on('change', function () {
        cargarImagenes(productoActual());
    });

    document.getElementById('btnSubirComboImagenes').addEventListener('click', function () {
        const productoId = productoActual();
        if (!productoId) {
            toastr.error('Elegí primero el producto principal');
            return;
        }
        if (!imagenesInput.files.length) {
            toastr.error('Elegí al menos una imagen');
            return;
        }

        const form = new FormData();
        Array.from(imagenesInput.files).forEach(f => form.append('imagenes[]', f));

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = 'Subiendo...';

        fetch('/combo-imagenes/' + productoId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: form
        })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i> Subir';
                if (status === 422) {
                    toastr.error(Object.values(data.errors || {}).flat().join(' '));
                    return;
                }
                imagenesInput.value = '';
                imagenesCombo = data.imagenes || [];
                pintarImagenes();
                toastr.success(data.mensaje || 'Imágenes subidas');
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i> Subir';
                toastr.error('No se pudieron subir las imágenes');
            });
    });

    imagenesGrid.addEventListener('click', function (e) {
        const thumb = e.target.closest('.combo-img-thumb');
        if (!thumb) return;
        const id = Number(thumb.dataset.id);

        if (e.target.closest('.combo-img-borrar')) {
            $.post('/combo-imagen-eliminar', { id: id }, function (data) {
                imagenesCombo = data.imagenes || [];
                pintarImagenes();
                toastr.success(data.mensaje);
            });
            return;
        }

        const mover = e.target.closest('.combo-img-mover');
        if (!mover) return;

        const dir = Number(mover.dataset.dir);
        const i = imagenesCombo.findIndex(img => Number(img.id) === id);
        const j = i + dir;
        if (i < 0 || j < 0 || j >= imagenesCombo.length) return;

        [imagenesCombo[i], imagenesCombo[j]] = [imagenesCombo[j], imagenesCombo[i]];
        pintarImagenes();

        $.post('/combo-imagenes-orden', {
            producto_id: productoActual(),
            ids: imagenesCombo.map(img => img.id)
        }, function (data) {
            imagenesCombo = data.imagenes || imagenesCombo;
            pintarImagenes();
        });
    });

    function resetForm() {
        document.getElementById('form_combo').reset();
        document.getElementById('combo_id_original').value = '';
        $productoSelect.val(null).trigger('change');
        $relacionadosSelect.val(null).trigger('change');
        regalosRows.innerHTML = '';
        imagenesCombo = [];
        if (imagenesInput) imagenesInput.value = '';
        pintarImagenes();
        $('.print-save-error-msg').hide();
    }

    document.getElementById('btnshowmodalcombo').addEventListener('click', function () {
        resetForm();
        document.querySelector('#ModalCombo .modal-title').textContent = 'Crear combo';
        modal.show();
    });

    window.edit_combo = function (id) {
        $.get('/combo-list/' + id, function (data) {
            resetForm();
            document.querySelector('#ModalCombo .modal-title').textContent = 'Editar combo';
            document.getElementById('combo_id_original').value = data.idarticulo;
            $productoSelect.val(data.idarticulo).trigger('change');
            document.getElementById('combo_descuento_pct').value = data.combo_descuento_pct;
            $relacionadosSelect.val((data.relacionados || []).map(String)).trigger('change');
            (data.regalos || []).forEach(r => agregarRegaloRow(r.regalo_id, r.cantidad));
            imagenesCombo = data.imagenes || [];
            pintarImagenes();
            modal.show();
        });
    };

    window.delete_combo = function (id) {
        Swal.fire({
            title: '¿Eliminar este combo?',
            text: 'Se apaga el armador de combo y se borran sus relacionados y regalos configurados. El producto en sí no se borra.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.value) return;
            $.post('/deletecombo', { id: id }, function (data) {
                if (data.estado === 1) {
                    toastr.success(data.mensaje);
                    window.refreshComboTable();
                } else {
                    toastr.error(data.mensaje);
                }
            });
        });
    };

    document.getElementById('btnGuardarCombo').addEventListener('click', function () {
        $('.print-save-error-msg').hide();

        const regalos = Array.from(regalosRows.querySelectorAll('.combo-regalo-row')).map(row => ({
            id: row.querySelector('.regalo-producto').value,
            cantidad: row.querySelector('.regalo-cantidad').value
        })).filter(r => r.id);

        const payload = {
            producto_id: $productoSelect.val(),
            combo_descuento_pct: document.getElementById('combo_descuento_pct').value,
            relacionados: $relacionadosSelect.val() || [],
            regalos: regalos
        };

        if (!payload.producto_id) {
            toastr.error('Elegí un producto principal');
            return;
        }
        if (!payload.combo_descuento_pct || Number(payload.combo_descuento_pct) <= 0) {
            toastr.error('El descuento del combo tiene que ser mayor a 0');
            return;
        }

        $('#btnGuardarCombo').html('Guardando...');

        fetch('/savecombo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                $('#btnGuardarCombo').html('<i class="fas fa-check-circle text-success mr-2"></i>Guardar');
                if (status === 422) {
                    const msgs = Object.values(data.errors || {}).flat();
                    $('.print-save-error-msg').find('ul').html(msgs.map(m => `<li>${m}</li>`).join(''));
                    $('.print-save-error-msg').show();
                    return;
                }
                if (data.estado === 1) {
                    toastr.success(data.mensaje);
                    modal.hide();
                    window.refreshComboTable();
                } else {
                    toastr.error(data.mensaje || 'Ocurrió un error');
                }
            })
            .catch(() => {
                $('#btnGuardarCombo').html('<i class="fas fa-check-circle text-success mr-2"></i>Guardar');
                toastr.error('Ocurrió un error inesperado');
            });
    });
});
