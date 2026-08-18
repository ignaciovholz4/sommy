
@extends('ecommerce.layouts.main-ecommerce')
@section('contentEcommerce')
<style>
    .input-form{
        border:1px solid #C8C8C8;
    }
    .card-title{
        float:left;
        font-size:1.1rem;
        font-weight:400;
        margin:0;
    }
    .card-tools{
        float:right;
        margin-right: -.625rem;
    }
</style>
<section class="py-5">
  <div class="container-fluid">
        <div class="container" id="div-content-order-global">
            <div class="row">
                <div class="col-md-8">
                    <div id="div-content-cart-detail">
                        <div class="card">
                            <div class="card-header">
                                <strong>Lista de productos agregados</strong> 
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">Color</th>
                                        <th scope="col">Talla</th>
                                        <th scope="col">Precio</th>
                                        <th scope="col">Total</th>
                                        <!--th scope="col"><i class="fa fa-trash" aria-hidden="true"></i></th>-->
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyshowproduct">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="div-content-section-order" style="display:none;">
                        <div style="">
                            <div class="card">
                                <div class="card-header">
                                    <!--strong>Email</strong> -->
                                    <!--h3 class="card-title">Select2 (Bootstrap4 Theme)</h3>-->
                                    <strong class="card-title">Email</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionEmail" data-card-widget="collapse">
                                            <!--i class="fas fa-minus"></i>-->
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-email" aria-hidden="true"></i>
                                        </button>
                                        <!--button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>-->
                                    </div><br>
                                    <div id="div-show-email" class="form-text"></div>
                                </div>
                                <div class="card-body" id="card-section-email">
                                    <form id="form-email">
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label">Ingresa tu correo electronico</label>
                                        <input type="email" class="form-control input-form" name="email" aria-describedby="emailHelp">
                                        <div id="emailHelp" class="form-text">Guardamos tu correo electronico para identificar tu perfil.</div>
                                    </div>
                                    </form>
                                    <button type="button" id="btnAddEmail" class="btn btn-primary">Continuar</button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:25px;">
                            <div class="card">
                                <div class="card-header">
                                    <strong class="card-title">Identificacion</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionIdentificacion" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-identificacion" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="display:none;" id="card-section-identification">
                                    <form class="row g-3" id="form-identificacion" >
                                        <div class="col-md-12">
                                            <label for="inputEmail4" class="form-label">Nombre</label>
                                            <input type="text" class="form-control input-form" name="name" id="name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputPassword4" class="form-label">Apellido materno</label>
                                            <input type="text" class="form-control input-form" name="materno" id="materno" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputPassword4" class="form-label">Apellido paterno</label>
                                            <input type="text" class="form-control input-form" name="paterno" id="paterno" required>
                                        </div>
                                        <div class="col-12">
                                            <label for="inputAddress" class="form-label">Telefono</label>
                                            <input type="text" class="form-control input-form" name="phone" id="phone" placeholder="XXX XXX XXXX" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" id="btnAddIdentificacion" class="btn btn-primary">Ir para la entrega</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:25px;">
                            <div class="card">
                                <div class="card-header">
                                    <strong class="card-title">Entrega</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionEntrega" data-card-widget="collapse">
                                            <i class="fa-solid fa-pencil text-secondary" id="icon-entrega" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="display:none;" id="card-section-entrega">
                                    <!--p class="card-titlee"><strong>Ingresar la direccion de entrega</strong></p>-->
                                    <form class="row g-3" id="form-entrega">
                                        <div class="col-md-12">
                                            <label for="inputEmail4" class="form-label">Calle</label>
                                            <input type="email" class="form-control input-form" name="calle" id="calle">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputPassword4" class="form-label">Numero exterior</label>
                                            <input type="number" min="0" class="form-control input-form" name="numberExterior" id="numberExterior">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputPassword4" class="form-label">Numero interior</label>
                                            <input type="number" min="0" class="form-control input-form" name="numberInterior" id="numberInterior" >
                                        </div>
                                        <div class="col-12">
                                            <label for="exampleFormControlTextarea1" class="form-label">Informacion adicional</label>
                                            <textarea class="form-control input-form" name="infoAdicional" id="" rows="3"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" id="btnAddEntrega" class="btn btn-primary">Ir al pedido</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!--div style="margin-top:25px;">
                            <div class="card">
                                <div class="card-header">
                                    <strong class="card-title">Ordena via WhatsApp</strong>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnSectionEmail" data-card-widget="collapse">
                                            <i class="fa fa-check-circle text-success" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="display:none;" id="card-section-ordenar-pedido">
                                    <div class="container text-center">
                                        <button class="btn btn-primary" type="button">Completar orden via WhatsApp</button>
                                    </div>
                                </div>
                            </div>
                        </div-->
                    </div>
                </div>
                <div class="col-md-4">
                      <div class="card">
                        <div class="card-header">
                            <h5>Resumen del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <!--p><strong>Envío:</strong> $5.00</p>-->
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <p><strong>Subtotal:</strong> </p>
                                <span class=""><strong id="showSubtotalPedido"></strong></span>
                            </li>
                            <hr>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <p><strong>Total:</strong></p>
                                <span class=""><strong id="showTotalPedido"></strong></span>
                            </li>
                            <div class="text-center mt-2">
                                <div class="d-grid gap-2">
                                <button class="btn btn-1 w-100" id="btnEndBuy" type="button">Finalizar compra</button>
                                <button class="btn btn-1 w-100" id="btnRegisterOrder" style="display:none;" type="button" disabled>Realizar pedido</button>
                                <a class="btn btn-lg btn-keep-shopping btn-sm w-100" id="btnKeepShopping" href="/">Seguir comprando</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" id="div-section-whatsapp" style="display:none;">
            <div class="row">
                <div class="p-5 mb-4 bg-light rounded-3">
                    <div class="container-fluid py-5 text-center">
                        <h2 class="display-5 fw-bold">Pedido realizado con exito</h2>
                        <p class="fs-4" id="p-name-customer">Gracias nombre apellido1 apellido2</p>
                        <p class="col-md-12 fs-4">Enviar los detalles de su pedido haciendo clic en el botón a continuación.</p>
                        <button type="button" class="btn btn-lg" id="btnSendDataOrderWhatsapp" style="background-color:#5FFC7B;color:#ffffff;">
                            Enviar SMS via WhatsApp 
                        </button><br>
                        <a class="btn btn-light btn-lg border border-2 mt-4" href="{{url('/')}}">Continuar comprando</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scriptEcommerce')
<script src="{{asset('js/ecommerce/cart-main-shopping.js')}}"></script>
<script src="{{asset('js/ecommerce/order-shopping-card.js')}}"></script>
@endsection