document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Poblar los selects de cuenta (caja/banco) con saldo, en cualquier página de inversores
    const selects = document.querySelectorAll('#reparto_cuenta, #movimiento_cuenta');
    if (selects.length > 0) {
        fetch('/cuentas-abiertas')
            .then(r => r.json())
            .then(data => {
                let options = '';
                (data.cajas || []).forEach(c => {
                    options += `<option value="caja-${c.id}">${c.nombre} (Caja - ${c.moneda}, ${c.sucursal}) — ${c.saldo.toFixed(2)}</option>`;
                });
                (data.bancos || []).forEach(b => {
                    options += `<option value="banco-${b.id}">${b.nombre} (Banco - ${b.moneda}, ${b.sucursal}) — ${b.saldo.toFixed(2)}</option>`;
                });
                selects.forEach(sel => { sel.innerHTML += options; });
            })
            .catch(() => {});
    }

    // Reparto de ganancias
    const formReparto = document.getElementById('formReparto');
    if (formReparto) {
        formReparto.addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            fetch('/finanzas/inversores/reparto-ganancias', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: fd
            })
            .then(r => r.json())
            .then(d => {
                if (d.estado === 1) {
                    const detalle = (d.repartos || []).map(r => `${r.inversor}: $${r.monto.toFixed(2)} (${r.porcentaje}%)`).join('\n');
                    alert('Reparto registrado:\n' + detalle);
                    location.reload();
                } else {
                    alert(d.mensaje || 'No se pudo repartir.');
                }
            })
            .catch(() => alert('Error al repartir.'));
        });
    }

    // Aporte / retiro individual
    const formMovimiento = document.getElementById('formMovimientoInversor');
    if (formMovimiento) {
        formMovimiento.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = this.dataset.inversorId;
            const fd = new FormData(this);
            fetch(`/finanzas/inversores/${id}/movimiento`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: fd
            })
            .then(r => r.json())
            .then(d => {
                if (d.estado === 1) {
                    location.reload();
                } else {
                    alert(d.mensaje || 'No se pudo registrar el movimiento.');
                }
            })
            .catch(() => alert('Error al registrar el movimiento.'));
        });
    }
});
