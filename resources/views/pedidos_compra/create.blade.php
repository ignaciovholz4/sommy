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
            <h4 class="mb-3"><i class="fas fa-file-signature mr-2"></i> Nuevo Pedido de Compra <span class="badge badge-warning">Borrador — no afecta stock ni caja</span></h4>

            <form action="{{ route('pedidos-compra.store') }}" method="POST" id="formCompra">
                @csrf

                <div class="compra-head-grid">
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor</label>
                        <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->idproveedor }}">
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_comprobante_id">Tipo de comprobante</label>
                        <select name="tipo_comprobante_id" id="tipo_comprobante_id" class="form-control" required>
                            <option value="">Seleccione tipo</option>
                            @foreach($tiposComprobantes as $tc)
                                <option value="{{ $tc->idtipo_comprobante }}">
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
                                <option value="{{ $s->id }}">
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
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2" maxlength="1000" placeholder="Notas internas del pedido (opcional)"></textarea>
                </div>

                <div class="form-group mt-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="a_credito" name="a_credito" value="1">
                        <label class="custom-control-label" for="a_credito">
                            Al convertir en compra será <strong>a crédito</strong> (genera deuda en Cuentas por Pagar según el plazo del proveedor)
                        </label>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Pedido</button>
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
@endpush
@endsection
