@extends('layouts.admin')

@section('contenido')

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('presupuestos.update', $presupuesto->idpresupuesto) }}" method="POST" id="formPresupuesto">
                @csrf
                @method('PUT')

                <!-- Cliente -->
                <div class="form-group">
                    <label for="cliente_id">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-control" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->idcliente }}" 
                                {{ $presupuesto->idcliente == $c->idcliente ? 'selected' : '' }}>
                                {{ $c->nombre }} {{ $c->paterno }} {{ $c->materno }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha -->
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" 
                           value="{{ $presupuesto->fecha }}" required>
                </div>

                <!-- Sucursal -->
                <div class="form-group">
                    <label for="sucursal_id">Sucursal</label>
                    <select name="sucursal_id" id="sucursal_id" class="form-control" required>
                        <option value="">Seleccione una sucursal</option>
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" 
                                {{ optional($presupuesto->sucursal)->id == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botón para agregar artículo -->
                <div class="form-group">
                    <label for="articuloSelect">Agregar artículo</label>
                    <div class="input-group">
                        <select id="articuloSelect" class="form-control">
                            <option value="">Seleccione un artículo</option>
                            @foreach($articulos as $sa)
                                <option value="{{ $sa->articulo->idarticulo }}" 
                                        data-precio="{{ $sa->articulo->pventa_con_iva }}" 
                                        data-iva="{{ $sa->articulo->ivaVenta->value_iva }}"
                                        data-descuento="{{ $sa->articulo->descuento ?? 0 }}">
                                    {{ $sa->articulo->nombre }}
                                </option>
                            @endforeach
                            @foreach($combinaciones as $c)
                                <option value="{{ $c->combinacion->producto->idarticulo }}"
                                        data-combinacion="{{ $c->combinacion->idcombinacion }}"
                                        data-tipo="2"
                                        data-precio="{{ $c->combinacion->pventa_variante }}"
                                        data-iva="{{ $c->combinacion->producto->ivaVenta->value_iva ?? 0 }}"
                                        data-descuento="0">
                                    {{ $c->combinacion->producto->nombre }} - {{ $c->combinacion->combinacion }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" id="addArticuloBtn" class="btn btn-success">Agregar</button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de artículos -->
                <h4>Artículos</h4>
                <div class="table-responsive">
                    <table id="detallesPresupuesto" class="table table-bordered">
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
                        <tbody id="presupuestoItems">
                        @foreach($presupuesto->detalles as $i => $detalle)
                            <tr data-idarticulo="{{ $detalle->idarticulo }}"
                                data-combinacion="{{ $detalle->combinacion_id ?? '' }}"
                                data-tipo="{{ $detalle->tipo_producto_id }}"
                                data-selected-list="{{ $detalle->price_list_id }}">
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][idarticulo]" value="{{ $detalle->idarticulo }}">
                                    <input type="hidden" name="items[{{ $i }}][combinacion_id]" value="{{ $detalle->combinacion_id }}">
                                    <input type="hidden" name="items[{{ $i }}][tipo_producto_id]" value="{{ $detalle->tipo_producto_id }}">
                                    {{ optional($detalle->articulo)->nombre ?? 'Producto eliminado del catálogo' }}
                                    @if($detalle->combinacion)
                                        - {{ $detalle->combinacion->combinacion }}
                                    @endif
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][cantidad]" 
                                        value="{{ $detalle->cantidad }}" min="1" 
                                        class="form-control cantidad">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][precio_unitario]" 
                                        value="{{ $detalle->precio_unitario }}" step="0.01" 
                                        class="form-control precio">
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][price_list_id]" class="form-control price-list">
                                        <!-- El JS llenará las opciones dinámicamente -->
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][descuento]" 
                                        value="{{ $detalle->descuento ?? 0 }}" min="0" max="100" 
                                        class="form-control descuento">
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][iva]" class="form-control iva">
                                        @foreach($ivas as $iva)
                                            <option value="{{ $iva->value_iva }}" 
                                                    {{ $detalle->iva == $iva->value_iva ? 'selected' : '' }}>
                                                {{ $iva->tipo_iva }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="subtotalNeto">{{ $detalle->subtotal_neto }}</td>
                                <td class="subtotalConIva">{{ $detalle->subtotal_con_iva }}</td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm removeItem">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-right"><strong>Total Neto</strong></td>
                                <td colspan="2"><strong id="totalNeto">{{ $presupuesto->total_neto }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-right"><strong>IVA discriminado</strong></td>
                                <td colspan="2" id="ivaDiscriminado"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-right"><strong>Total con IVA</strong></td>
                                <td colspan="2"><strong id="totalConIva">{{ $presupuesto->total_con_iva }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Hidden inputs para enviar totales -->
                <input type="hidden" name="total_neto" id="inputTotalNeto" value="{{ $presupuesto->total_neto }}">
                <input type="hidden" name="total_con_iva" id="inputTotalConIva" value="{{ $presupuesto->total_con_iva }}">

                <!-- Botón guardar -->
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Actualizar Presupuesto</button>
                    <a href="{{ route('presupuestos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Pasamos las opciones de IVA al JS -->
<script>
    const ivaOptions = `
        @foreach($ivas as $iva)
            <option value="{{ $iva->value_iva }}" data-arca="{{ $iva->value_arca }}">
                {{ $iva->tipo_iva }}
            </option>
        @endforeach
    `;
</script>

@push('ScriptPresupuestoEdit')
<script src="{{ asset('js/funciones_presupuesto/create_presupuesto.js') }}"></script>
@endpush
@endsection