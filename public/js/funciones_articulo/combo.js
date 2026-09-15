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
            { data: 'nombre', name: 'nombre' },
            { data: 'descuento_fmt', name: 'combo_descuento_pct', searchable: false },
            { data: 'relacionados_fmt', name: 'relacionados', orderable: false },
            { data: 'regalos_fmt', name: 'regalos', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
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

    function resetForm() {
        document.getElementById('form_combo').reset();
        document.getElementById('combo_id_original').value = '';
        $productoSelect.val(null).trigger('change');
        $relacionadosSelect.val(null).trigger('change');
        regalosRows.innerHTML = '';
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
