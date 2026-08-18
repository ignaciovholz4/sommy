// El historial de sesiones se renderiza del lado del servidor (sin DataTable).

// ✅ Botón principal de abrir caja (arriba del historial)
$(document).on('click', '.btn-abrir-principal', function() {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Abrir caja',
        html: `
            <div class="text-start">
                <label class="form-label">Fondo inicial</label>
                <input id="fondoInicialInput" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
                <small class="text-muted">Se registra en la moneda configurada de la cuenta.</small>
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
            fetch('/cuentas/' + id + '/abrir', {
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
            fetch('/cuentas/' + id + '/cerrar', {
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
                        location.reload();
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


// ✅ Mostrar resumen de caja
$(document).on('click', '.btn-resumen', function() {
    const aperturaId = $(this).data('apertura-id');

    fetch(`/cuentas/apertura/${aperturaId}/resumen`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(result => {
        if (result.status === 1) {
            // Cargar datos en el modal
            document.querySelector("#name_empresa").textContent = result.settings?.name || '—';
            document.querySelector("#date_operation").textContent = formatDateTime(result.cierre);
            document.querySelector("#name_responsable").textContent = result.name_cajero || '—';
            document.querySelector("#fondo_cuenta").textContent = result.inicio;
            document.querySelector("#resumen_ingresos").textContent = result.ingresos;
            document.querySelector("#resumen_egresos").textContent = result.egresos;
            document.querySelector("#efectivo_cuenta").textContent = result.final;
            document.querySelector("#sale_efectivo").textContent = result.total;

            const datafaltante = document.querySelector("#faltante_cuenta");
            datafaltante.textContent = Number(result.faltante).toFixed(2);

            const datasobrante = document.querySelector("#sobrante_cuenta");
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
            const myModal = new bootstrap.Modal(document.getElementById('detailCuentaModal'));
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

// ✅ Utilidad para formatear fecha/hora
function formatDateTime(value) {
    if (!value) return '—';
    const d = new Date(value);
    const pad = n => String(n).padStart(2, '0');
    return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}