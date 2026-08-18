$(document).ready(function () {
    const cajaId = $('#table_movimientos').data('caja-id');
    const aperturaId = $('#table_movimientos').data('apertura-id');

    // Inicializar DataTable
    const tablaMovimientos = $('#table_movimientos').DataTable({
        paging: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        language: {
            emptyTable: "No hay movimientos registrados",
            zeroRecords: "No se encontraron movimientos",
            processing: "Cargando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ movimientos",
            infoEmpty: "Mostrando 0 a 0 de 0 movimientos",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        ajax: {
            url: `/caja/${cajaId}/movimientos/data`,
            type: 'GET',
            data: function(d){
                if (aperturaId) d.apertura = aperturaId;
            }
        },
        columns: [
            { data: 'fecha', name: 'fecha',
              render: function(data){ return data ? data : '—'; } },
            { data: 'estado', name: 'tipo', orderable: false, searchable: false },
            { data: 'cliente_proveedor', name: 'cliente_proveedor' },
            { data: 'comprobante', name: 'comprobante' },
            { data: 'observaciones', name: 'observaciones' },
            { data: 'efectivo', name: 'efectivo',
              render: function(data){ return parseFloat(data).toFixed(2); } },
            { data: 'bancos', name: 'bancos',
              render: function(data){ return parseFloat(data).toFixed(2); } },
            { data: 'tarjetas', name: 'tarjetas',
              render: function(data){ return parseFloat(data).toFixed(2); } },
            { data: 'monto', name: 'total', orderable: true, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });

    // ============================
    // Modal "Agregar Movimiento"
    // ============================

    // Abrir modal
    $(document).on('click', '#btnAgregarMovimiento', function() {
        $('#formAgregarMovimiento')[0].reset();
        $('#mov_total').val('0.00');
        $('#mov_comprobante').val('');
        const modal = new bootstrap.Modal(document.getElementById('agregarMovimientoModal'));
        modal.show();
    });

    // Generar comprobante automático según tipo
    $('#mov_tipo').on('change', function() {
        const tipo = $(this).val();
        const prefix = tipo === 'ingreso' ? 'MRC' : (tipo === 'egreso' ? 'MPC' : '');
        if (prefix) {
            // Simulación: timestamp como número único
            $('#mov_comprobante').val(`${prefix}-${Date.now()}`);
        } else {
            $('#mov_comprobante').val('');
        }
    });

    // Calcular total en tiempo real (efectivo + bancos + tarjetas)
    function calcularTotal() {
        const efectivo = parseFloat($('#mov_efectivo').val()) || 0;
        const bancos   = parseFloat($('#mov_bancos').val()) || 0;
        const tarjetas = parseFloat($('#mov_tarjetas').val()) || 0;
        const total    = efectivo + bancos + tarjetas;
        $('#mov_total').val(total.toFixed(2));
    }

    $('#mov_efectivo, #mov_bancos, #mov_tarjetas').on('input', calcularTotal);

    // Envío del formulario
    $('#formAgregarMovimiento').on('submit', function(e) {
        e.preventDefault();

        const data = {
            tipo: $('#mov_tipo').val(),
            cliente_proveedor: $('#mov_cliente').val(),
            comprobante: $('#mov_comprobante').val(),
            observaciones: $('#mov_observaciones').val(),
            efectivo: parseFloat($('#mov_efectivo').val()) || 0,
            bancos: parseFloat($('#mov_bancos').val()) || 0,
            tarjetas: parseFloat($('#mov_tarjetas').val()) || 0,
            total: parseFloat($('#mov_total').val()) || 0,
            apertura_id: aperturaId
        };

        if (!data.tipo || !data.comprobante) {
            Swal.fire('Error', 'Debe seleccionar el tipo de movimiento', 'error');
            return;
        }

        fetch(`/caja/${cajaId}/movimientos`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(result => {
            if (result.estado === 1) {
                Swal.fire('Éxito', result.mensaje, 'success').then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('agregarMovimientoModal')).hide();
                    tablaMovimientos.ajax.reload(null, false); // refresca sin perder página
                });
            } else {
                Swal.fire('Error', result.mensaje || 'No se pudo registrar el movimiento', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Error inesperado al registrar el movimiento', 'error');
        });
    });
});