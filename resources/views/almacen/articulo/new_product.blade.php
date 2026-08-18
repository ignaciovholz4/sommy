<div class="card">
  <div class="modal-body">
    <form id="save_producto" enctype="multipart/form-data" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
            <div class="form-group">
                <label for="nombre">Nombre<i class="text-danger"><strong>*</strong></i></label>
                <input type="text" name="nombre" class="form-control" placeholder="Nombre..." value="{{old('nombre')}}">
            </div>
        </div>
        <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
            <div class="form-group">
                <label for="">Categoria<i class="text-danger"><strong>*</strong></i></label>
                <select name="idcategoria" class="form-control">
                    <option value="">Seleciona una opción</option>
                    @foreach ($categoria as $cat)
                    <option value="{{$cat->idcategoria}}">{{$cat->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div> 
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true"><i class="fas fa-chart-pie fa-fw me-2"></i> Home</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false"><i class="fas fa-chart-line fa-fw me-2"></i>Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false"><i class="fas fa-cogs fa-fw me-2"></i>Contact</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="row mt-2">
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="codigo">Codigo<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="codigo" class="form-control" placeholder="Codigo del articulo" value="{{old('codigo')}}">
                        </div>
                    </div> 
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="stock">Stock del articulo<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="stock" class="form-control" placeholder="0.00" required value="0.00" >
                        </div>
                    </div> 
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="stock">p. compra<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="pcompra" class="form-control" placeholder="0.00" value="0.00" onkeypress="return filterFloatdecimal2(event,this);">
                        </div>
                    </div> 
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="stock">p. venta<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="pventa" class="form-control" placeholder="0.00"  value="0.00"  onkeypress="return filterFloatdecimal2(event,this);"> 
                        </div>
                    </div> 
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="nombre">Descripcion<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="descripcion" class="form-control" placeholder="descripcion del articulo" value="{{old('descripcion')}}">
                        </div>
                    </div> 
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12" >
                        <div class="form-group">
                            <label for="">Descuento $<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="articulo_des" id="articulo_des" value="0.00" class="form-control" placeholder="Ingresa el descuento $" onkeypress="return filterFloatdecimal2(event,this);">
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="nombre">Imagen del articulo</label>
                                    <input type="file" id="file" name="imagen" class="form-control" accept="image/*">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <div class="text-center" id="imagepreview">
                                        <img src="//placehold.it/100?text=IMAGEN" class="img-thumbnail" id="preview" width="100" height="100"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--START SEGOND TAB-->
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="row m-2" >
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="">variaciones<i class="text-danger"><strong>*</strong></i></label>
                            <select name="select-variaciones" id="select-variaciones" class="form-control">
                                <option value="">Seleciona una opción</option>
                                @foreach ($variaciones as $var)
                                <option value="{{$var->id}}">{{$var->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> 
                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="">variantes<i class="text-danger"><strong>*</strong></i></label>
                            <select id="select-variante" class="" multiple style="width:100%;">
                            </select>
                        </div>
                    </div> 
                </div>
            </div>
            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab"> Tab 3 content</div>
        </div>



        
        
        <!--div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
            <div class="form-group">
                <label for="">Lleva iva (%)</label><br>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="iva1" name="iva" value="1.16" class="custom-control-input">
                    <label class="custom-control-label" for="iva1">Si</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="iva2" name="iva" value="0.00" class="custom-control-input">
                    <label class="custom-control-label" for="iva2">No</label>
                </div>
            </div>
        </div-->
                  
    </div>
    </form>
    @include('message.show_error_form')
    <div class="col-md-12 text-center">
      <button type="button" class="btn btn5 mr-3"><i class="fas fa-window-close mr-2 "></i>Cerrar</button>
      <button class="btn btn6" id="btnsaveproducto" type="button"><i class="fas fa-check-circle text-success mr-2"></i>Guardar</button>
    </div>
  </div>
</div>