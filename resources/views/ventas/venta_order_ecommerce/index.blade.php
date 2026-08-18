@extends('layouts.admin')
@section('contenido')
<section class="section margindivsection">
    <div class="card">
        <div class="card-header">
            <span style="font-size:20px;">Lista de ventas por ecommerce</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ventas_order_ecommerce_table" class="table table-bordered table-hover">
                    <thead>
                        <th>#</th>
                        <th>Nombre cliente</th>
                        <th>Telefono cliente</th>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
<!-- Modal show details sale ecommerce-->
<div class="modal fade" id="ModalVentasEcommerce" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Detalle de la venta por ecommerce</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="container">     
            <div class="row">
                <div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
                    <div class="form-group">
                        <label for="cliente">Cliente</label>
                        <p id="customer"></p>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
                    <div class="form-group">
                        <label for="tipo">Numero de orden</label>
                        <p id="num_order"></p>
                    </div>
                </div> 
                <!--div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
                    <div class="form-group">
                        <label for="folio">Folio de comprobante</label>
                        <p id="detalles_folio"></p>
                    </div>
                </div>  -->
            </div><!--fin del primer row-->
                <div id="" class="">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <div class="row">
                            <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="detalles" class="table text-center">
                                    <thead>
                                        <tr>
                                        <th>Articulo</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="show_details_order">
                                    
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td>Total: </td>
                                            <td id="show_total_order"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            </div>   
                        </div>
                    </div>
                </div>
        </div>  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <!--<button type="button" class="btn btn-primary">Save changes</button>-->
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{asset('js/funciones_venta/venta_order_ecommerce.js')}}"></script> 
@endsection