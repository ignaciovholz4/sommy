$(document).ready(function () {
    $('#table_historial').DataTable({
        paging: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '/caja/' + $('.btn-abrir-principal').data('id') + '/historial/data',
            type: 'GET'
        },
        columns: [
            { data: 'fecha_apertura', name: 'fecha_apertura' },
            { data: 'fecha_cierre', name: 'fecha_cierre',
              render: function(data){ return data ? data : '—'; } },
            { data: 'fondo_inicial', name: 'fondo_inicial',
              render: function(data){ return parseFloat(data).toFixed(2); } },
            { data: 'estado', name: 'estado', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });
});

// ✅ Botón principal de abrir caja (arriba del historial)
$(document).on('click', '.btn-abrir-principal', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Abrir caja',
        html: `
            <div class="text-start">
                <label class="form-label">Fondo inicial</label>
                <input id="fondoInicialInput" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
                <small class="text-muted">Se registra en la moneda configurada de la caja.</small>
            </div>
        `,
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Abrir',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const valor = document.getElementById('fondoInicialInput').value;
            if (valor === '' || Number(valor) < 0) {
                Swal.showValidationMessage('Ingresá un fondo inicial válido (>= 0).');
            }
            return valor;
        }
    }).then((result) => {
        if (result.value) {
            fetch('/caja/' + id + '/abrir', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ fondo_inicial: result.value })
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire('Éxito', data.mensaje, 'success').then(() => {
                        location.reload(); // refresca historial
                    });
                } else {
                    Swal.fire('Error', data.mensaje || 'No se pudo abrir la caja', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error inesperado al abrir la caja', 'error');
            });
        }
    });
});

// ✅ Botón de cerrar caja (en filas del historial)
$(document).on('click', '.btn-cerrar', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Cerrar caja',
        text: 'Se registrará la fecha de cierre en este momento.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Cerrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            fetch('/caja/' + id + '/cerrar', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire('Éxito', data.mensaje, 'success').then(() => {
                        location.reload(); // refresca historial
                    });
                } else {
                    Swal.fire('Error', data.mensaje || 'No se pudo cerrar la caja', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error inesperado al cerrar la caja', 'error');
            });
        }
    });
});

// ✅ (Opcional) Botón de abrir en filas cerradas (si querés permitir abrir desde cada fila)
$(document).on('click', '.btn-abrir', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Abrir nueva apertura',
        html: `
            <div class="text-start">
                <label class="form-label">Fondo inicial</label>
                <input id="fondoInicialInputRow" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
            </div>
        `,
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Abrir',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const valor = document.getElementById('fondoInicialInputRow').value;
            if (valor === '' || Number(valor) < 0) {
                Swal.showValidationMessage('Ingresá un fondo inicial válido (>= 0).');
            }
            return valor;
        }
    }).then((result) => {
        if (result.value) {
            console.log()
            fetch('/caja/' + id + '/abrir', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ fondo_inicial: result.value })
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 1) {
                    Swal.fire('Éxito', data.mensaje, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.mensaje || 'No se pudo abrir la caja', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error inesperado al abrir la caja', 'error');
            });
        }
    });
});

// ✅ Utilidad para formatear fecha/hora
function formatDateTime(value) {
    if (!value) return '—';
    const d = new Date(value);
    const pad = n => String(n).padStart(2, '0');
    return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

// ✅ Mostrar resumen de caja
$(document).on('click', '.btn-resumen', function() {
    const aperturaId = $(this).data('apertura-id');

    fetch(`/caja/apertura/${aperturaId}/resumen`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(result => {
        if (result.status === 1) {
            // Cargar datos en el modal
            document.querySelector("#name_empresa").textContent = result.settings?.name || '—';
            document.querySelector("#date_operation").textContent = formatDateTime(result.cierre);
            document.querySelector("#name_cajero").textContent = result.name_cajero || '—';
            document.querySelector("#fondo_caja").textContent = result.inicio;
            document.querySelector("#resumen_ingresos").textContent = result.ingresos;
            document.querySelector("#resumen_egresos").textContent = result.egresos;
            document.querySelector("#efectivo_caja").textContent = result.final;
            document.querySelector("#sale_efectivo").textContent = result.total;

            const datafaltante = document.querySelector("#faltante_caja");
            datafaltante.textContent = Number(result.faltante).toFixed(2);

            const datasobrante = document.querySelector("#sobrante_caja");
            datasobrante.textContent = Number(result.sobrante).toFixed(2);

            // Colorear faltante/sobrante
            if (Number(result.faltante) > 0) {
                datafaltante.style.color = "red";
                datasobrante.style.color = "";
            } else if (Number(result.sobrante) > 0) {
                datasobrante.style.color = "green";
                datafaltante.style.color = "";
            } else {
                datasobrante.style.color = "";
                datafaltante.style.color = "";
            }

            // Mostrar modal
            const myModal = new bootstrap.Modal(document.getElementById('detailCajaModal'));
            myModal.show();
        } else {
            Swal.fire('Error', result.message || 'No se pudo obtener el resumen', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Error inesperado al obtener el resumen', 'error');
    });
});