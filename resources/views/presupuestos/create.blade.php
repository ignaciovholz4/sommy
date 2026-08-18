@extends('layouts.admin')

@section('contenido')

<style>
    /* Cabecera del formulario en grilla compacta: todo visible sin scrollear */
    .presu-head-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1.4fr;
        gap: 14px;
        margin-bottom: 6px;
    }
    .presu-head-grid .form-group { margin-bottom: 8px; }
    @media (max-width: 991px) { .presu-head-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 575px) { .presu-head-grid { grid-template-columns: 1fr; } }
</style>

<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('presupuestos.store') }}" method="POST" id="formPresupuesto">
                @csrf

                {{-- Cabecera en grilla: Cliente · Fecha · Sucursal en una sola fila --}}
                <div class="presu-head-grid">
                    <div class="form-group">
                        <label for="cliente_id">Cliente</label>
                        <select name="cliente_id" id="cliente_id" class="form-control" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->idcliente }}">
                                    {{ $c->nombre }} {{ $c->paterno }} {{ $c->materno }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
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

                <!-- Botón para agregar artículo -->
                <div class="form-group">
                    <label for="articuloSelect">Agregar artículo</label>
                    <div class="input-group">
                        <select id="articuloSelect" class="form-control">
                            <option value="">Seleccione un artículo</option>
                            @foreach($articulos as $a)
                                <option value="{{ $a->idarticulo }}" 
                                        data-precio="{{ $a->pventa_con_iva }}" 
                                        data-iva="{{ $a->ivaVenta->value_iva }}">
                                    {{ $a->nombre }}
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

                <!-- Botón guardar -->
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Guardar Presupuesto</button>
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

@push('ScriptPresupuestoCreate')
<script src="{{ asset('js/funciones_presupuesto/create_presupuesto.js') }}"></script>
@endpush
@endsection