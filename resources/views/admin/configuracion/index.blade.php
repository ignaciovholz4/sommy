@extends('layouts.admin')
@section('contenido')
<section class="content margindivsection">    
    <div class="card card-primary card-outline">
        <div class="card-header">
        <h3 class="card-title">Datos generales de la empresa</h3>
        </div>
        <div class="card-body">
            @include('custom.message')
            <form action="saveconf" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <input type="text" name="identificador" value="{{$empresa['num']}}" hidden="true">
            <div class="input-group mb-4">
                <div class="input-group-prepend">
                <span class="input-group-text bg-info"><i class="fas fa-pen-square"></i></span>
                </div>
                <input type="text" class="form-control" value="{{$empresa['name']}}" name="name" placeholder="Nombre de la empresa">
                <div class="input-group-prepend">
                <span class="input-group-text bg-info">Nombre de la empresa</span>
                </div>
            </div>

            <div class="input-group mb-4">
                <div class="input-group-prepend">
                <span class="input-group-text bg-info"><i class="fas fa-globe"></i></span>
                </div>
                <input type="text" class="form-control" value="{{$empresa['subdomain'] ?? ''}}" name="subdomain" placeholder="subdominio">
                <div class="input-group-prepend">
                <span class="input-group-text bg-info">.{{ parse_url(env('APP_URL'), PHP_URL_HOST) ?? 'facturarg.com' }}</span>
                </div>
            </div>
            <small class="form-text text-muted mb-4">Su URL completa: <span class="font-weight-bold text-primary">{{ $empresa['subdomain'] ? 'https://' . $empresa['subdomain'] . '.' . (parse_url(env('APP_URL'), PHP_URL_HOST) ?? 'facturarg.com') : 'No configurado' }}</span></small>

            <div class="input-group mb-4">
                <input type="file" name="file" id="file" class="form-control">
                <div class="input-group-append">
                <span class="input-group-text bg-info">Logo 80px - 60px</span>
                </div>
            </div>

            <div class="container text-center mb-4" >
                <!--div class="col-md-6 thumbnail">
                    <img src="{{asset('dist/img/logo.png')}}" class="img-fluid" alt="...">
                </div>
                <div class="col-md-6 thumbnail">
                    <img src="{{asset('dist/img/logo.png')}}" class="img-fluid" alt="...">
                </div-->
                <div class="row gx-3 gy-2 align-items-center">
                    <div class="col-sm-3">
                        <label for="">Imagen actual del logo</label>
                    </div>
                    <div class="col-sm-3">
                        <div class=" text-left">
                            <div class="img" ><img src="{{asset($empresa['image'])}}" alt="..." width="100" height="100" class="img-thumbnail"></div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="">Imagen nueva del logo</label>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-left" id="imagepreview">
                            <!-- img src="{{asset('dist/img/logo.png')}}" alt="..." width="120" height="130" class="img-thumbnail"-->
                            <img src="//placehold.it/100?text=IMAGEN" class="img-thumbnail" id="preview" width="100" height="100"/>
                        </div>
                    </div>
                </div>
                <!-- div class="row">
                    <div class="col-md-6 bg-success">
                        <label for="">Imagen actual</label>
                        <div class="img" ><img src="{{asset('dist/img/logo.png')}}" alt="..." width="120" height="130" class="img-thumbnail"></div>
                    </div>
                    <div class="col-md-6 bg-danger">
                        <label for="">Imagen nueva</label>
                        <div class="img" ><img src="{{asset('dist/img/logo.png')}}" alt="..." width="120" height="130" class="img-thumbnail"></div>
                    </div>
                </div-->
            </div>

            <div class="input-group mb-4" >
                <div class="input-group-prepend">
                <span class="input-group-text bg-info"><i class="far fa-building"></i></span>
                </div>
                <input type="text" class="form-control" value="{{$empresa['adress']}}" name="adress" placeholder="Direccion de la empresa">
                <div class="input-group-append">
                <span class="input-group-text bg-info">Direccion de la empresa</span>
                </div>
            </div>

            <div class="input-group mb-4">
                <div class="input-group-prepend">
                <span class="input-group-text bg-info">Email de la empresa</span>
                </div>
                <input type="email" class="form-control" value="{{$empresa['email']}}" name="email"  placeholder="Email">
                <div class="input-group-prepend">
                <span class="input-group-text bg-info"><i class="fas fa-envelope"></i></span>
                </div>
            </div>

            <div class="input-group mb-4">
                <input type="text" class="form-control" name="phone" value="{{$empresa['phone']}}" placeholder="Telefono de contacto" onkeypress="return soloNumeros(event, this);">
                <div class="input-group-append">
                <span class="input-group-text bg-info"><i class="fas fa-phone-square"></i></span>
                </div>
            </div>

            <hr>
            <h5 class="mb-3"><i class="fas fa-file-invoice-dollar"></i> Datos fiscales y de cobro (checkout de la tienda)</h5>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text bg-info">CUIT</span>
                        </div>
                        <input type="text" class="form-control" name="cuit" value="{{$empresa['cuit']}}" placeholder="Ej: 30123456789" maxlength="13">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text bg-info">Razón social</span>
                        </div>
                        <input type="text" class="form-control" name="razon_social" value="{{$empresa['razon_social']}}" placeholder="Razón social" maxlength="200">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text bg-info">CBU</span>
                        </div>
                        <input type="text" class="form-control" name="cbu" value="{{$empresa['cbu']}}" placeholder="22 dígitos" maxlength="22" onkeypress="return soloNumeros(event, this);">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text bg-info">Alias CBU</span>
                        </div>
                        <input type="text" class="form-control" name="alias_cbu" value="{{$empresa['alias_cbu']}}" placeholder="Ej: colchones.mp" maxlength="50">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text bg-info">% Desc. transferencia</span>
                        </div>
                        <input type="number" class="form-control" name="descuento_transferencia" value="{{$empresa['descuento_transferencia']}}" min="0" max="100" step="0.01">
                        <div class="input-group-append">
                        <span class="input-group-text bg-info">%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                        <span class="input-group-text bg-info"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                        </div>
                        <input type="text" class="form-control" name="whatsapp" value="{{$empresa['whatsapp']}}" placeholder="Ej: 5493511234567 (con 549 adelante)" maxlength="20" onkeypress="return soloNumeros(event, this);">
                    </div>
                </div>
            </div>
            <small class="form-text text-muted mb-4">
                El CBU/alias y el % de descuento se muestran al cliente cuando elige pagar por transferencia.
                El WhatsApp es el número al que llegan los pedidos de la tienda.
                Las credenciales de MercadoPago se configuran en el archivo .env del servidor (MERCADOPAGO_ACCESS_TOKEN).
            </small>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn1"><i class="fas fa-check-circle"></i> Aceptar</button>
            </div>
            </form>
        </div>
    </div>
</section>          
@endsection
@section('scripts')
    <script src="{{asset('js/funciones_configuracion/configuracion.js')}}" type="module"></script>
@endsection