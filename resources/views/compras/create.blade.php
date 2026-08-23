@extends('layouts.admin')

@section('contenido')

<style>
    /* Cabecera del formulario en grilla compacta: todo visible sin scrollear */
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
            @can('haveaccess', 'compras.ocr_ia')
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalOcrComprobante">
                    <i class="fa fa-magic"></i> Cargar con IA
                </button>
                <small class="text-muted d-block mt-1">Subí la foto o el PDF de la factura/remito del proveedor y se precarga el formulario. Siempre revisá antes de guardar.</small>
            </div>
            @endcan

            <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
                @csrf

                {{-- Cabecera en grilla: Proveedor · Fecha · Comprobante · Sucursal en una sola fila --}}
                <div class="compra-head-grid">
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor</label>
                        <div style="display:flex;gap:6px;">
                            <select name="proveedor_id" id="proveedor_id" class="form-control" required style="flex:1;">
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $p)
                                    <option value="{{ $p->idproveedor }}">
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" onclick="abrirAltaRapida_qprovCompra()" title="Crear proveedor rápido">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    @include('partials.alta_rapida', ['arPrefijo' => 'qprovCompra', 'arTitulo' => 'Crear proveedor rápido', 'arRuta' => route('quick_create_supplier'), 'arSelect' => 'proveedor_id', 'arKey' => 'supplier', 'arPk' => 'idproveedor'])

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

                {{-- Agregar artículo, a lo ancho --}}
                <div class="form-group">
                    <label for="articuloSelect">Agregar artículo</label>
                    <div class="input-group">
                        <select id="articuloSelect" class="form-control">
                            <option value="">Seleccione un artículo</option>
                            {{-- Se llenará dinámicamente según la sucursal seleccionada --}}
                        </select>
                        <div class="input-group-append">
                            <button type="button" id="addArticuloBtn" class="btn btn-success">Agregar</button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de artículos -->
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
                            <!-- filas dinámicas -->
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

                <!-- Hidden inputs para enviar totales -->
                <input type="hidden" name="total_neto" id="inputTotalNeto" value="0">
                <input type="hidden" name="total_con_iva" id="inputTotalConIva" value="0">

                <!-- Compra a crédito: genera deuda en Cuentas por Pagar con vencimiento según el plazo del proveedor -->
                <div class="form-group mt-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="a_credito" name="a_credito" value="1">
                        <label class="custom-control-label" for="a_credito">
                            Compra a crédito (genera deuda en <strong>Cuentas por Pagar</strong> con vencimiento según el plazo de pago del proveedor)
                        </label>
                    </div>
                </div>

                <!-- Botón guardar -->
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Compra</button>
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

@can('haveaccess', 'compras.ocr_ia')
<div class="modal fade" id="modalOcrComprobante" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cargar comprobante con IA</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="ocrArchivo">Foto o PDF de la factura/remito del proveedor</label>
                    <input type="file" id="ocrArchivo" class="form-control-file" accept=".jpg,.jpeg,.png,.webp,.pdf">
                </div>
                <div id="ocrSpinner" class="text-center d-none">
                    <i class="fa fa-spinner fa-spin"></i> Leyendo comprobante, puede tardar unos segundos...
                </div>
                <div id="ocrError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="ocrProcesarBtn" class="btn btn-primary">Procesar</button>
            </div>
        </div>
    </div>
</div>
@endcan

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

@can('haveaccess', 'compras.ocr_ia')
<script>
    const ocrUploadUrl = "{{ route('compras.ocr-upload') }}";
</script>
@endcan

@push('ScriptCompraCreate')
<script src="{{ asset('js/funciones_compra/create_compra.js') }}"></script>
@can('haveaccess', 'compras.ocr_ia')
<script src="{{ asset('js/funciones_compra/compra_ocr.js') }}"></script>
@endcan
@endpush
@endsection