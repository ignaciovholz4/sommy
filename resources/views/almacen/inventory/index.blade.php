@extends('layouts.admin')
@section('contenido')
@include('almacen.inventory.header')

<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="section margindivsection">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="inventory_table" class="table table-bordered table-hover">
                    <thead>
                        <th>#</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Pesable</th>
                        @if($puedeVerCostos)
                        <th>Precio compra s/IVA</th>
                        <th>Precio compra c/IVA</th>
                        @endif
                        <th>Precio venta s/IVA</th>
                        <th>Precio venta c/IVA</th>
                        <th>IVA compra</th>
                        <th>IVA venta</th>
                        <th scope="col"><i class="fa fa-cog" aria-hidden="true"></i></th>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                        <tr>
                            <td>{{ $prod->idarticulo }}</td>
                            <td>{{ $prod->codigo }}</td>
                            <td>{{ $prod->nombre }}</td>
                            <td>{{ $prod->descripcion }}</td>
                            <td>{{ $prod->tipo_producto_id == 1 ? 'Simple' : 'Personalizado' }}</td>
                            <td>{{ $prod->articulo_pesable_balanza ? 'Sí' : 'No' }}</td>
                            @if($puedeVerCostos)
                            <td>${{ number_format($prod->pcompra_sin_iva, 2) }}</td>
                            <td>${{ number_format($prod->pcompra_con_iva, 2) }}</td>
                            @endif
                            <td>${{ number_format($prod->pventa_sin_iva, 2) }}</td>
                            <td>${{ number_format($prod->pventa_con_iva, 2) }}</td>
                            <td>{{ optional($prod->ivaCompra)->tipo_iva ?? '' }}</td>
                            <td>{{ optional($prod->ivaVenta)->tipo_iva ?? '' }}</td>
                            <td class="d-flex">
                                <button class="btn btn-primary btn-sm mr-2" id="btn{{ $prod->idarticulo }}" dataId="{{ $prod->idarticulo }}" name="btnAjustar">Ajustar</button>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#barcodeModal" onclick="fnShowModalBarcode({{ $prod->idarticulo }});">
                                    <i class="fa fa-barcode"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <div class="lg-12 col-md-12 col-sm-12 col-xs-12">
        <div></div>
    </div>
</div>

@include('almacen.inventory.modal-barcode')
@endsection

@section('scripts')
    <script src="{{ asset('js/funciones_articulo/inventario_index.js') }}"></script>
    <script src="{{ asset('js/funciones_articulo/inventario.js') }}" type="module"></script>
@endsection