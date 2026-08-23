@extends('layouts.admin')

@section('contenido')

<style>
    .compra-head-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1.4fr 1.4fr;
        gap: 14px;
        margin-bottom: 6px;
    }
    .compra-head-grid .form-group { margin-bottom: 8px; }
    @media (max-width: 991px) { .compra-head-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 575px) { .compra-head-grid { grid-template-columns: 1fr; } }
</style>

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-3"><i class="fas fa-file-signature mr-2"></i> Editar Pedido de Compra <strong>{{ $pedido->num_folio }}</strong> <span class="badge badge-warning">Borrador</span></h4>

            <form action="{{ route('pedidos-compra.update', $pedido->id) }}" method="POST" id="formCompra">
                @csrf

                <div class="compra-head-grid">
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor</label>
                        <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->idproveedor }}" {{ $pedido->proveedor_id == $p->idproveedor ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ \Carbon\Carbon::parse($pedido->fecha)->format('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_comprobante_id">Tipo de comprobante</label>
                        <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control" required>
                            <option value="">Seleccione tipo</option>
                            @foreach($tiposComprobantes as $tc)
                                <option value="{{ $tc->idtipo_comprobante }}" {{ $pedido->tipo_comprobante_id == $tc->idtipo_comprobante ? 'selected' : '' }}>
                                    {{ $tc->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sucursal_id">Sucursal</label>
                        <select name="sucursal_id" id="sucursal_id" class="form-control" required>
                            <option value="">Seleccione una sucursal</option>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ $pedido->sucursal_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="articuloSelect">Agregar artículo</label>
                    <div class="input-group">
                        <select id="articuloSelect" class="form-control">
                            <option value="">Seleccione un artículo</option>
                        </select>
                        <div class="input-group-append">
                            <button type="button" id="addArticuloBtn" class="btn btn-success">Agregar</button>
                        </div>
                    </div>
                </div>

                <h4>Artículos</h4>
                <div class="table-responsive">
                    <table id="detallesCompra" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Lista de precios</th>
                                <th>Descuento (%)</th>
                                <th>IVA</th>
                                <th>Subtotal Neto</th>
                                <th>Subtotal con IVA</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="compraItems">
                            @foreach($pedido->detalles as $i => $d)
                            <tr data-idarticulo="{{ $d->articulo_id }}" data-combinacion="{{ $d->combinacion_id ?? '' }}" data-tipo="{{ $d->tipo_producto_id }}">
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][idarticulo]" value="{{ $d->articulo_id }}">
                                    <input type="hidden" name="items[{{ $i }}][combinacion_id]" value="{{ $d->combinacion_id ?? '' }}">
                                    <input type="hidden" name="items[{{ $i }}][tipo_producto_id]" value="{{ $d->tipo_producto_id }}">
                                    {{ $d->articulo->nombre_compra ?: $d->articulo->nombre }}{{ $d->combinacion ? ' - ' . $d->combinacion->combinacion : '' }}
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][cantidad]" value="{{ $d->cantidad }}" min="1" class="form-control cantidad">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][precio_unitario]" value="{{ $d->precio_unitario }}" step="0.01" class="form-control precio">
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][price_list_id]" class="form-control price-list" data-selected="{{ $d->price_list_id ?? '' }}" data-articulo="{{ $d->articulo_id }}">
                                        <option value="">Sin lista</option>
                                        @if($d->priceList)
                                            <option value="{{ $d->price_list_id }}" selected>{{ $d->priceList->name }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][descuento]" value="{{ $d->descuento }}" min="0" max="100" class="form-control descuento">
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][iva]" class="form-control iva" data-selected="{{ $d->iva }}">
                                        @foreach($ivas as $iva)
                                            <option value="{{ $iva->value_iva }}" {{ (float)$d->iva === (float)$iva->value_iva ? 'selected' : '' }}>
                                                {{ $iva->tipo_iva }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="subtotalNeto"></td>
                                <td class="subtotalConIva"></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm removeItem">Eliminar</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-right"><strong>Total Neto</strong></td>
                                <td colspan="2"><strong id="totalNeto">0</strong></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-right"><strong>IVA discriminado</strong></td>
                                <td colspan="2" id="ivaDiscriminado"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-right"><strong>Total con IVA</strong></td>
                                <td colspan="2"><strong id="totalConIva">0</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <input type="hidden" name="total_neto" id="inputTotalNeto" value="0">
                <input type="hidden" name="total_con_iva" id="inputTotalConIva" value="0">

                <div class="form-group mt-3">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2" maxlength="1000" placeholder="Notas internas del pedido (opcional)">{{ $pedido->observaciones }}</textarea>
                </div>

                <div class="form-group mt-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="a_credito" name="a_credito" value="1" {{ $pedido->a_credito ? 'checked' : '' }}>
                        <label class="custom-control-label" for="a_credito">
                            Al convertir en compra será <strong>a crédito</strong> (genera deuda en Cuentas por Pagar según el plazo del proveedor)
                        </label>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Actualizar Pedido</button>
                    <a href="{{ route('pedidos-compra.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    const ivaOptions = `
        @foreach($ivas as $iva)
            <option value="{{ $iva->value_iva }}" data-arca="{{ $iva->value_arca }}">
                {{ $iva->tipo_iva }}
            </option>
        @endforeach
    `;
</script>

@push('ScriptCompraCreate')
<script src="{{ asset('js/funciones_compra/create_compra.js') }}"></script>
<script>
    // Inicializar las filas ya existentes del pedido (eventos + listas de precios + totales)
    document.addEventListener("DOMContentLoaded", () => {
        // Cargar los artículos disponibles de la sucursal ya seleccionada
        const sucursalSelect = document.querySelector("#sucursal_id");
        if (sucursalSelect && sucursalSelect.value) {
            sucursalSelect.dispatchEvent(new Event('change'));
        }

        document.querySelectorAll("#compraItems tr").forEach(row => {
            inicializarFila(row);

            // Completar el select de listas de precios aplicables al artículo
            const priceListSelect = row.querySelector(".price-list");
            const articuloId = priceListSelect.dataset.articulo;
            const selected = priceListSelect.dataset.selected;

            fetch(`/price-lists/applicable/${articuloId}?context=purchase`)
                .then(res => res.json())
                .then(lists => {
                    let options = '<option value="">Sin lista</option>';
                    lists.forEach(l => {
                        options += `<option value="${l.id}" ${String(l.id) === selected ? 'selected' : ''}>${l.name}</option>`;
                    });
                    priceListSelect.innerHTML = options;
                });
        });

        calcularTotal();
    });
</script>
@endpush
@endsection
