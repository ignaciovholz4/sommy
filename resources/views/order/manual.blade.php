@extends('layouts.admin')

@section('title', 'Pedido manual')

@section('contenido')
<style>
    .man-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; max-width: 900px; margin: 18px auto; }
    .man-card {
        background: #fff; border: 1px solid #E7EAF2; border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27,43,90,.08); padding: 24px;
        margin-bottom: 16px;
    }
    .man-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 14px; }
    .man-wrap label { font-size: 13px; font-weight: 500; color: #1B2B5A; margin-bottom: 4px; }
    .man-wrap .form-control, .man-wrap .custom-select {
        border-radius: 10px; border-color: #E7EAF2; font-size: 14px;
    }
    .man-canales { display: flex; gap: 10px; flex-wrap: wrap; }
    .man-canal input { display: none; }
    .man-canal span {
        display: inline-block; padding: 8px 18px; border-radius: 999px;
        border: 1.5px solid #E7EAF2; font-size: 13.5px; font-weight: 500;
        color: #47536F; cursor: pointer; transition: all .15s;
    }
    .man-canal input:checked + span { background: #1B2B5A; border-color: #1B2B5A; color: #fff; }
    #tabla-items td { vertical-align: middle; font-size: 14px; }
    .man-total { font-size: 22px; font-weight: 700; }
</style>

<div class="man-wrap">
    <h2 style="font-size:20px;font-weight:600;margin-bottom:14px;">
        <i class="fas fa-plus-circle" style="color:#2563EB;"></i> Cargar pedido de otro canal
    </h2>

    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:12px;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ url('/orders/manual') }}" id="formManual">
        @csrf

        <div class="man-card">
            <h3>¿De qué canal viene el pedido?</h3>
            <div class="man-canales">
                @foreach ([
                    'meli' => 'MercadoLibre',
                    'whatsapp' => 'WhatsApp',
                    'instagram' => 'Instagram',
                    'facebook' => 'Facebook',
                    'local' => 'Local / Mostrador',
                ] as $valCanal => $lblCanal)
                <label class="man-canal">
                    <input type="radio" name="origen" value="{{ $valCanal }}" {{ old('origen', 'meli') === $valCanal ? 'checked' : '' }}>
                    <span>{{ $lblCanal }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="man-card">
            <h3>Cliente</h3>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Nombre y apellido *</label>
                    <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Teléfono / WhatsApp</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Dirección de entrega</label>
                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
                </div>
            </div>
            <small class="text-muted">Si el email o teléfono ya existen, el pedido se asocia al cliente existente.</small>
        </div>

        <div class="man-card">
            <h3>Productos</h3>
            <div class="row align-items-end">
                <div class="col-md-6 form-group">
                    <label>Producto</label>
                    <select id="selProducto" class="custom-select">
                        @foreach ($productos as $prod)
                        <option value="{{ $prod->idarticulo }}" data-precio="{{ $prod->pventa_con_iva }}" data-nombre="{{ $prod->nombre }}">
                            {{ $prod->nombre }} (stock: {{ $prod->stock }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Cantidad</label>
                    <input type="number" id="selCantidad" class="form-control" value="1" min="1">
                </div>
                <div class="col-md-2 form-group">
                    <label>Precio unit.</label>
                    <input type="number" id="selPrecio" class="form-control" step="0.01" min="0">
                </div>
                <div class="col-md-2 form-group">
                    <button type="button" class="btn btn-primary w-100" onclick="agregarItem()">Agregar</button>
                </div>
            </div>

            <table class="table mt-2" id="tabla-items" style="display:none;">
                <thead>
                    <tr>
                        <th>Producto</th><th class="text-center">Cant.</th>
                        <th class="text-right">Precio</th><th class="text-right">Total</th><th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="{{ url('/orders/order') }}" class="btn btn-secondary">Cancelar</a>
                <div class="text-right">
                    <div class="man-total">Total: $<span id="totalPedido">0</span></div>
                    <button type="submit" class="btn btn-primary mt-2 px-4">Registrar pedido</button>
                </div>
            </div>
            <div id="itemsInputs"></div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
let items = [];

// Precio por defecto al elegir producto
const selProducto = document.getElementById('selProducto');
const selPrecio = document.getElementById('selPrecio');
function syncPrecio() {
    const opt = selProducto.options[selProducto.selectedIndex];
    if (opt) selPrecio.value = opt.dataset.precio;
}
selProducto.addEventListener('change', syncPrecio);
syncPrecio();

function agregarItem() {
    const opt = selProducto.options[selProducto.selectedIndex];
    if (!opt) return;
    const cantidad = parseInt(document.getElementById('selCantidad').value, 10) || 1;
    const precio = parseFloat(selPrecio.value) || 0;
    items.push({ producto_id: opt.value, nombre: opt.dataset.nombre, cantidad, precio });
    renderItems();
}

function quitarItem(i) {
    items.splice(i, 1);
    renderItems();
}

function renderItems() {
    const tabla = document.getElementById('tabla-items');
    const tbody = tabla.querySelector('tbody');
    const inputs = document.getElementById('itemsInputs');
    tbody.innerHTML = '';
    inputs.innerHTML = '';
    let total = 0;

    items.forEach(function (it, i) {
        const sub = it.cantidad * it.precio;
        total += sub;
        tbody.innerHTML += '<tr>' +
            '<td>' + it.nombre + '</td>' +
            '<td class="text-center">' + it.cantidad + '</td>' +
            '<td class="text-right">$' + it.precio.toLocaleString('es-AR') + '</td>' +
            '<td class="text-right">$' + sub.toLocaleString('es-AR') + '</td>' +
            '<td class="text-right"><button type="button" class="btn btn-sm" onclick="quitarItem(' + i + ')"><i class="fas fa-trash text-danger"></i></button></td>' +
            '</tr>';
        inputs.innerHTML +=
            '<input type="hidden" name="items[' + i + '][producto_id]" value="' + it.producto_id + '">' +
            '<input type="hidden" name="items[' + i + '][cantidad]" value="' + it.cantidad + '">' +
            '<input type="hidden" name="items[' + i + '][precio]" value="' + it.precio + '">';
    });

    tabla.style.display = items.length ? 'table' : 'none';
    document.getElementById('totalPedido').textContent = total.toLocaleString('es-AR');
}
</script>
@endsection
