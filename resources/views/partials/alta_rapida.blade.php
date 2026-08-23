{{--
    Alta rápida de proveedor/cliente sin salir del formulario.
    Parámetros: $arPrefijo (id único), $arTitulo, $arRuta (endpoint POST),
    $arSelect (id del <select> destino), $arKey (clave del JSON: supplier/customer),
    $arPk (nombre del campo id: idproveedor/idcliente).
    Modal autocontenido: no depende de Bootstrap JS (funciona en BS4 y BS5).
--}}
<div id="{{ $arPrefijo }}-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;max-width:430px;width:92%;padding:22px 24px;box-shadow:0 20px 50px rgba(0,0,0,.3);">
        <h5 style="font-weight:700;margin-bottom:14px;"><i class="fas fa-plus-circle" style="color:#2563EB;"></i> {{ $arTitulo }}</h5>
        <div class="form-group mb-2">
            <label style="font-size:13px;font-weight:600;">Nombre <span class="text-danger">*</span></label>
            <input type="text" id="{{ $arPrefijo }}-nombre" class="form-control" maxlength="200" placeholder="Nombre o razón social">
        </div>
        <div class="form-group mb-2">
            <label style="font-size:13px;font-weight:600;">CUIT / DNI</label>
            <input type="text" id="{{ $arPrefijo }}-cuit" class="form-control" maxlength="20" placeholder="Opcional">
        </div>
        <div class="form-group mb-2">
            <label style="font-size:13px;font-weight:600;">Teléfono</label>
            <input type="text" id="{{ $arPrefijo }}-telefono" class="form-control" maxlength="20" placeholder="Opcional">
        </div>
        <div class="form-group mb-3">
            <label style="font-size:13px;font-weight:600;">Email</label>
            <input type="email" id="{{ $arPrefijo }}-email" class="form-control" maxlength="200" placeholder="Opcional">
        </div>
        <div id="{{ $arPrefijo }}-error" class="text-danger" style="font-size:13px;margin-bottom:8px;display:none;"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('{{ $arPrefijo }}-overlay').style.display='none'">Cancelar</button>
            <button type="button" class="btn btn-primary" id="{{ $arPrefijo }}-guardar"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>
<script>
(function () {
    var pref = '{{ $arPrefijo }}';
    var overlay = document.getElementById(pref + '-overlay');

    window['abrirAltaRapida_' + pref] = function () {
        overlay.style.display = 'flex';
        document.getElementById(pref + '-error').style.display = 'none';
        setTimeout(function () { document.getElementById(pref + '-nombre').focus(); }, 50);
    };

    document.getElementById(pref + '-guardar').addEventListener('click', function () {
        var btn = this;
        var nombre = document.getElementById(pref + '-nombre').value.trim();
        var errorEl = document.getElementById(pref + '-error');
        if (!nombre) {
            errorEl.textContent = 'El nombre es obligatorio.';
            errorEl.style.display = '';
            return;
        }
        btn.disabled = true;

        var datos = new FormData();
        datos.append('nombre', nombre);
        datos.append('{{ $arKey === 'customer' ? 'dni_cuit' : 'cuit' }}', document.getElementById(pref + '-cuit').value.trim());
        datos.append('telefono', document.getElementById(pref + '-telefono').value.trim());
        datos.append('email', document.getElementById(pref + '-email').value.trim());

        fetch('{{ $arRuta }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: datos
        })
        .then(function (r) {
            if (r.status === 422) return r.json().then(function (j) {
                throw new Error(Object.values(j.errors || {}).flat().join(' ') || 'Datos inválidos');
            });
            return r.json();
        })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'No se pudo crear');
            var nuevo = d['{{ $arKey }}'];
            var sel = document.getElementById('{{ $arSelect }}');
            var opt = document.createElement('option');
            opt.value = nuevo['{{ $arPk }}'];
            opt.textContent = nombre;
            sel.appendChild(opt);
            // Compatible con select2 si la página lo usa
            if (window.jQuery && window.jQuery(sel).data('select2')) {
                window.jQuery(sel).val(opt.value).trigger('change');
            } else {
                sel.value = opt.value;
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            overlay.style.display = 'none';
            ['nombre', 'cuit', 'telefono', 'email'].forEach(function (c) {
                document.getElementById(pref + '-' + c).value = '';
            });
        })
        .catch(function (e) {
            errorEl.textContent = e.message;
            errorEl.style.display = '';
        })
        .finally(function () { btn.disabled = false; });
    });
})();
</script>
