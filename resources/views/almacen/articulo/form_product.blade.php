<div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Formulario del producto</h5>
        </div>
    <input type="hidden" name="typeProductId" id="typeProductId"
           value="{{ isset($product->tipo_producto_id) ? $product->tipo_producto_id : 0 }}">
    <div class="card-body">
        <!-- <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab"> -->
            <form id="save_producto" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <input type="hidden" name="productoId" id="productoId"
                       value="{{ isset($product->idarticulo) ? $product->idarticulo : 0 }}">
            <div class="row mt-2">
                <div class="row">
                    <hr>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="">Tipo de producto<i class="text-danger"><strong>*</strong></i></label>
                            <select name="tipo_producto" id="select-tipo-producto" class="form-control"
                                {{ isset($product->idarticulo) ? 'disabled' : '' }}>
                                <option value="">Selecciona una opción</option>
                                @foreach ($tipoProducto as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        {{ old('tipo_producto', isset($product->tipo_producto_id) ? $product->tipo_producto_id : null) == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(isset($product->idarticulo))
                                <input type="hidden" name="tipo_producto" value="{{ $product->tipo_producto_id }}">
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="nombre">Nombre (venta)<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Nombre comercial (ventas, web, presupuestos)..."
                                   value="{{ old('nombre', isset($product->nombre) ? $product->nombre : '') }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="nombre_compra">Nombre para compras <small class="text-muted">(como lo llama el proveedor)</small></label>
                            <input type="text" name="nombre_compra" class="form-control"
                                   placeholder="Opcional: se usa en compras y pedidos de compra"
                                   value="{{ old('nombre_compra', isset($product->nombre_compra) ? $product->nombre_compra : '') }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Categoria<i class="text-danger"><strong>*</strong></i></label>
                            <div class="input-group">
                                <select name="idcategoria" id="categoria-select" class="form-control">
                                    <option value="">Selecciona una opción</option>
                                    @foreach ($categoria as $cat)
                                        <option value="{{ $cat->idcategoria }}"
                                            {{ old('idcategoria', isset($product->categoria_id) ? $product->categoria_id : null) == $cat->idcategoria ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quickCategoryModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Marca<i class="text-danger"><strong>*</strong></i></label>
                            <div class="input-group">
                                <select name="idmarca" id="marca-select" class="form-control">
                                    <option value="">Selecciona una opción</option>
                                    @foreach ($marca as $m)
                                        <option value="{{ $m->idmarca }}"
                                            {{ old('idmarca', isset($product->marca_id) ? $product->marca_id : null) == $m->idmarca ? 'selected' : '' }}>
                                            {{ $m->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quickMarcaModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="proveedor_id">Proveedor</label>
                            <div class="input-group">
                                <select name="proveedor_id" id="proveedor-select" class="form-control">
                                    <option value="">Sin proveedor</option>
                                    @foreach ($proveedores ?? [] as $prov)
                                        <option value="{{ $prov->idproveedor }}"
                                            {{ old('proveedor_id', isset($product->proveedor_id) ? $product->proveedor_id : null) == $prov->idproveedor ? 'selected' : '' }}>
                                            {{ $prov->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="abrirAltaRapida_qprovProd()" title="Crear proveedor rápido">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="codigo_proveedor">Código del proveedor</label>
                            <input type="text" name="codigo_proveedor" class="form-control"
                                   placeholder="Código en la lista del proveedor (ej: Bardo)"
                                   value="{{ old('codigo_proveedor', isset($product->codigo_proveedor) ? $product->codigo_proveedor : '') }}">
                        </div>
                    </div>
                    {{-- Campo pesable/balanza oculto: no aplica al rubro colchonería --}}
                    <input type="hidden" name="articulo_pesable_balanza" value="0">
                    <div class="col-lg-4" id="unidades">
                        <div class="form-group">
                            <label for="">Unidad<i class="text-danger"><strong>*</strong></i></label>
                            <div class="input-group">
                                <select name="idunidad" id="unidad-select" class="form-control">
                                    <option value="">Selecciona una opción</option>
                                    @foreach ($unidad as $u)
                                        <option value="{{ $u->idunidad }}"
                                            {{ old('idunidad', isset($product->unidad_id) ? $product->unidad_id : null) == $u->idunidad ? 'selected' : '' }}>
                                            {{ $u->nombre }} ({{ $u->nombre_corto }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quickUnidadModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="codigo">Codigo<i class="text-danger"><strong>*</strong></i></label>
                            <input type="text" id="codigo" name="codigo" class="form-control"
                                   placeholder="Código del artículo"
                                   value="{{ old('codigo', isset($product->codigo) ? $product->codigo : '') }}"
                                   {{ isset($product->codigo) ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <hr>
                    {{-- Con variantes (tipo 2) el precio va por variante: esta sección se oculta --}}
                    <div class="row" id="seccion-precios-base">
                        <div class="col-lg-6">
                            <div class="row">
                                <h3>Detalles Compra</h3>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="">IVA:<i class="text-danger"><strong>*</strong></i></label>
                                        <select class="form-control" name="iva_compra_id" id="iva_compra">
                                            <option value="">Seleccione</option>
                                            <option value="" data-rate="">Ninguno</option>
                                            @foreach($iva as $item)
                                                <option value="{{ $item->idiva }}"
                                                        data-rate="{{ $item->value_iva }}"
                                                        {{ old('iva_compra_id', isset($product->iva_compra_id) ? $product->iva_compra_id : null) == $item->idiva ? 'selected' : '' }}>
                                                    {{ $item->tipo_iva }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6"></div>
                                <div class="col-sm-6" id="div-content-pcompra-sin-iva">
                                    <div class="form-group">
                                        <label for="stock">Sin IVA:<i class="text-danger"><strong>*</strong></i></label>
                                        <input type="text" name="pcompra-sin-iva" class="form-control solo-numeros"
                                           placeholder="0.00"
                                           value="{{ old('pcompra-sin-iva', isset($product->pcompra_sin_iva) ? $product->pcompra_sin_iva : '') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6" id="div-content-pcompra-con-iva">
                                    <div class="form-group">
                                        <label for="stock">Con IVA<i class="text-danger"><strong>*</strong></i></label>
                                        <input type="text" name="pcompra-con-iva" class="form-control solo-numeros"
                                           placeholder="0.00"
                                           value="{{ old('pcompra-con-iva', isset($product->pcompra_con_iva) ? $product->pcompra_con_iva : '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row">
                                <h3>Detalles Venta</h3>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="">IVA:<i class="text-danger"><strong>*</strong></i></label>
                                        <select class="form-control" name="iva_venta_id" id="iva_venta">
                                            <option value="">Seleccione</option>
                                            <option value="" data-rate="">Ninguno</option>
                                            @foreach($iva as $item)
                                                <option value="{{ $item->idiva }}"
                                                        data-rate="{{ $item->value_iva }}"
                                                        {{ old('iva_venta_id', isset($product->iva_venta_id) ? $product->iva_venta_id : null) == $item->idiva ? 'selected' : '' }}>
                                                    {{ $item->tipo_iva }}
                                                </option>
                                            @endforeach
                                        </select>                           
                                    </div>
                                </div>
                                <div class="col-sm-6" id="div-content-margen-pventa">
                                    <div class="form-group">
                                        <label for="stock">Margen ganancia (%):<i class="text-danger"><strong>*</strong></i></label>
                                        <input type="text" name="margen-pventa" class="form-control solo-numeros"
                                           placeholder="25.00"
                                           value="{{ old('margen-pventa', 25.00) }}">
                                    </div>
                                </div>
                                <div class="col-sm-6" id="div-content-pventa-sin-iva">
                                    <div class="form-group">
                                        <label for="stock">Sin IVA:<i class="text-danger"><strong>*</strong></i></label>
                                        <input type="text" name="pventa-sin-iva" class="form-control solo-numeros"
                                           placeholder="0.00"
                                           value="{{ old('pventa-sin-iva', isset($product->pventa_sin_iva) ? $product->pventa_sin_iva : '') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6" id="div-content-pventa-con-iva">
                                    <div class="form-group">
                                        <label for="stock">Con IVA<i class="text-danger"><strong>*</strong></i></label>
                                        <input type="text" name="pventa-con-iva" class="form-control solo-numeros"
                                           placeholder="0.00"
                                           value="{{ old('pventa-con-iva', isset($product->pventa_con_iva) ? $product->pventa_con_iva : '') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6" id="div-content-pventa-mayorista">
                                    <div class="form-group">
                                        <label for="stock">Precio mayorista <small class="text-muted">(solo catálogo, no se ve en la web)</small></label>
                                        <input type="text" name="pventa-mayorista" class="form-control solo-numeros"
                                           placeholder="0.00"
                                           value="{{ old('pventa-mayorista', isset($product->pventa_mayorista) ? $product->pventa_mayorista : '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-lg-4" id="div-content-descuento">
                        <div class="form-group">
                            <label for="">Descuento (%)<i class="text-danger"><strong>*</strong></i></label>
                            <input type="number" name="articulo_des" id="articulo_des" class="form-control"
                                placeholder="Ingresa el descuento %"
                                value="{{ old('articulo_des', isset($product->descuento) ? $product->descuento : 0) }}"
                                min="0" max="100" step="0.01">
                        </div>
                    </div>
                    <hr>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="relacionados-select">Productos relacionados <small class="text-muted">(se recomiendan al agregar este producto al carrito)</small></label>
                            <select name="relacionados[]" id="relacionados-select" class="form-control" multiple>
                                @foreach($productosDisponibles ?? [] as $p)
                                    <option value="{{ $p->idarticulo }}"
                                        {{ in_array($p->idarticulo, $relacionadosIds ?? []) ? 'selected' : '' }}>
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                </div>

                <!-- seguir incorporando -->

                <div class="col-sm-12">
                    <div class="accordion mb-3" id="accordionFichaTecnica">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFicha">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFicha" aria-expanded="false" aria-controls="collapseFicha">
                                    <i class="fas fa-bed mr-2"></i>&nbsp;Ficha técnica (colchón)
                                </button>
                            </h2>
                            <div id="collapseFicha" class="accordion-collapse collapse" aria-labelledby="headingFicha" data-bs-parent="#accordionFichaTecnica">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="tipo_colchon">Tipo de colchón</label>
                                                <select name="tipo_colchon" class="form-control">
                                                    <option value="">Sin especificar</option>
                                                    @foreach ($tiposColchon ?? [] as $valor => $etiqueta)
                                                        <option value="{{ $valor }}" {{ old('tipo_colchon', $product->tipo_colchon ?? null) == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="firmeza">Firmeza</label>
                                                <select name="firmeza" class="form-control">
                                                    <option value="">Sin especificar</option>
                                                    @foreach ($firmezas ?? [] as $valor => $etiqueta)
                                                        <option value="{{ $valor }}" {{ old('firmeza', $product->firmeza ?? null) == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="plazas">Plazas</label>
                                                <select name="plazas" class="form-control">
                                                    <option value="">Sin especificar</option>
                                                    @foreach ($plazasOpts ?? [] as $valor => $etiqueta)
                                                        <option value="{{ $valor }}" {{ old('plazas', $product->plazas ?? null) == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="altura_cm">Altura (cm)</label>
                                                <input type="number" name="altura_cm" class="form-control" min="0" step="0.1"
                                                       placeholder="Ej: 28"
                                                       value="{{ old('altura_cm', $product->altura_cm ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="densidad_kg_m3">Densidad (kg/m³)</label>
                                                <input type="number" name="densidad_kg_m3" class="form-control" min="0" step="0.01"
                                                       placeholder="Ej: 30"
                                                       value="{{ old('densidad_kg_m3', $product->densidad_kg_m3 ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="peso_max_kg">Peso máx. por plaza (kg)</label>
                                                <input type="number" name="peso_max_kg" class="form-control" min="0" step="1"
                                                       placeholder="Ej: 110"
                                                       value="{{ old('peso_max_kg', $product->peso_max_kg ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="garantia_anios">Garantía (años)</label>
                                                <input type="number" name="garantia_anios" class="form-control" min="0" max="50" step="1"
                                                       placeholder="Ej: 5"
                                                       value="{{ old('garantia_anios', $product->garantia_anios ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="noches_prueba">Noches de prueba</label>
                                                <input type="number" name="noches_prueba" class="form-control" min="0" max="365" step="1"
                                                       placeholder="Ej: 30"
                                                       value="{{ old('noches_prueba', $product->noches_prueba ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="tela">Tela</label>
                                                <input type="text" name="tela" class="form-control" maxlength="100"
                                                       placeholder="Ej: Jacquard"
                                                       value="{{ old('tela', $product->tela ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="certificaciones">Certificaciones</label>
                                                <input type="text" name="certificaciones" class="form-control" maxlength="500"
                                                       placeholder="Ej: Antialérgico, espuma certificada"
                                                       value="{{ old('certificaciones', $product->certificaciones ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="pillow_top">Pillow top</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="pillow_top" name="pillow_top"
                                                        {{ old('pillow_top', $product->pillow_top ?? 0) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="pillow_top">Tiene pillow top</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6" style="align-content: center;">
                    <div class="row">
                        <div class="form-group">
                            <label for="nombre">Descripcion<i class="text-danger"><strong>*</strong></i></label>
                            <textarea name="descripcion" class="form-control" rows="4"
                                   placeholder="Descripción del artículo">{{ old('descripcion', isset($product->descripcion) ? $product->descripcion : '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <div class="col-lg-8" style="display: flex;justify-content:center;align-items: center;">
                            <div class="form-group">
                                <label for="nombre">Imagen principal del articulo</label>
                                <input type="file" id="file" name="imagen" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-lg-4" >
                            <div class="form-group" style="display: flex;justify-content:center;align-items: center;width: 100%;height: 100%;">
                                <div class="text-center" id="imagepreview" >
                                    <!--img src="//placehold.it/100?text=IMAGEN" class="img-thumbnail" id="preview" width="100" height="100"/>-->
                                    <img src="{{isset($product->imagen)?asset('imagenes/articulos/'.$product->imagen):'https://place-hold.it/300'}}" class="img-thumbnail" id="preview" width="100" height="100"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" id="div-content-images-ecommerce">
                    <div class="form-group">
                        <label>Galería de fotos y videos <small class="text-muted">(hasta 8 archivos: corte interior, tela, ambiente, un video del producto, etc.)</small></label>
                        @if(!isset($product->idarticulo))
                            <input type="file" name="imageInput[]" id="galeriaInput" class="form-control" accept="image/*,video/*" multiple>
                        @else
                            <input type="file" name="updateImage[]" id="galeriaInput" class="form-control" accept="image/*,video/*" multiple>
                        @endif
                    </div>
                    <input type="hidden" name="imagenes_eliminar" id="imagenes_eliminar" value="">
                    <input type="hidden" name="imagenes_orden" id="imagenes_orden" value="">
                    <div id="galeria-previews" class="d-flex flex-wrap gap-2 mt-2">
                        @foreach ($imagenesGaleria ?? [] as $img)
                            <div class="galeria-item text-center border rounded p-1" data-id="{{ $img->id }}" style="width:120px;">
                                @if($img->tipo === 'video')
                                    <video src="{{ asset($img->path) }}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;" muted></video>
                                    <div class="small text-muted"><i class="fas fa-video"></i> Video</div>
                                @else
                                    <img src="{{ asset($img->path) }}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
                                @endif
                                <div class="d-flex justify-content-center align-items-center gap-1 mt-1">
                                    <input type="number" class="form-control form-control-sm galeria-orden" value="{{ $img->orden }}" min="0" style="width:55px;" title="Orden">
                                    <button type="button" class="btn btn-sm btn-danger galeria-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            </form>
            @include('message.show_error_form')
        </div>
    </div>
    <div id="card-variantes" class="card mb-4" style="display:none;">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Variantes del producto</h5>
        </div>
        <div class="card-body">
            <button class="btn btn-outline-primary mt-3" onclick="openAtributosModal('editar')">
                <i class="fas fa-cogs"></i> Gestionar atributos
            </button>

            <!-- Aquí se mostrarán las combinaciones -->
            <div class="mt-4">
                <h5>Combinaciones generadas</h5>
                <table class="table table-bordered" id="combinaciones-table">
                    <thead>
                    <tr>
                        <th>Combinación</th>
                        <th>Imagen</th>
                        <th>SKU <small class="text-muted">(vacío = automático)</small></th>
                        <th>P. compra</th>
                        <th>Margen (%)</th>
                        <th>P. venta</th>
                        <th>P. mayorista <small class="text-muted">(catálogo)</small></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12 text-center">
        <a href="{{url('almacen/articulo')}}" type="button" class="btn btn5 mr-3"><i class="fas fa-window-close mr-2 "></i>Regresar</a>
        <button class="btn btn6" id="btnsaveproducto" type="button"><i class="fas fa-check-circle text-success mr-2"></i>{{ isset($product->descuento)?'Actualizar':'Guardar' }} </button>
    </div>

<!-- Modal -->
<div class="modal fade" id="atributosModal" tabindex="-1" aria-labelledby="atributosModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="atributosModalLabel">Agregar atributos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="atributos-container"></div>
        <div class="text-center mt-3">
          <button id="btn-add-atributo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar atributo
          </button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="btn-save-atributos">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- Quick Category Modal -->
<div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-labelledby="quickCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickCategoryModalLabel">
                    <i class="fas fa-plus-circle"></i> Crear Categoría Rápida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickCategoryForm">
                    @csrf
                    <div class="form-group">
                        <label for="categoryName">Nombre de la Categoría <i class="text-danger"><strong>*</strong></i></label>
                        <input type="text" class="form-control" id="categoryName" name="nombre" placeholder="Ej: Electrónicos" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="categoryDescription">Descripción</label>
                        <textarea class="form-control" id="categoryDescription" name="descripcion" rows="3" placeholder="Descripción de la categoría (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveQuickCategory">
                    <i class="fas fa-save"></i> Guardar Categoría
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Marca Modal -->
<div class="modal fade" id="quickMarcaModal" tabindex="-1" aria-labelledby="quickMarcaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickMarcaModalLabel">
                    <i class="fas fa-plus-circle"></i> Crear Marca Rápida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickMarcaForm">
                    @csrf
                    <div class="form-group">
                        <label for="marcaName">Nombre de la Marca <i class="text-danger"><strong>*</strong></i></label>
                        <input type="text" class="form-control" id="marcaName" name="nombre" placeholder="Ej: Samsung" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="marcaDescription">Descripción</label>
                        <textarea class="form-control" id="marcaDescription" name="descripcion" rows="3" placeholder="Descripción de la marca (opcional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveQuickMarca">
                    <i class="fas fa-save"></i> Guardar Marca
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Unidad Modal -->
<div class="modal fade" id="quickUnidadModal" tabindex="-1" aria-labelledby="quickUnidadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickUnidadModalLabel">
                    <i class="fas fa-plus-circle"></i> Crear Unidad Rápida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickUnidadForm">
                    @csrf
                    <div class="form-group">
                        <label for="unidadName">Nombre de la Unidad <i class="text-danger"><strong>*</strong></i></label>
                        <input type="text" class="form-control" id="unidadName" name="nombre" placeholder="Ej: Kilogramo" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="unidadShortName">Nombre Corto <i class="text-danger"><strong>*</strong></i></label>
                        <input type="text" class="form-control" id="unidadShortName" name="nombre_corto" maxlength="10" placeholder="Ej: Kg" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="unidadDecimal">¿Permite decimales?</label>
                        <select class="form-control" id="unidadDecimal" name="decimal">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveQuickUnidad">
                    <i class="fas fa-save"></i> Guardar Unidad
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Bloque Compra
    const selectIvaCompra = document.getElementById("iva_compra");
    const inputCompraSinIva = document.querySelector("input[name='pcompra-sin-iva']");
    const inputCompraConIva = document.querySelector("input[name='pcompra-con-iva']");

    // Bloque Venta
    const selectIvaVenta = document.getElementById("iva_venta");
    const inputMargen = document.querySelector("input[name='margen-pventa']");
    const inputVentaSinIva = document.querySelector("input[name='pventa-sin-iva']");
    const inputVentaConIva = document.querySelector("input[name='pventa-con-iva']");

    setTimeout(() => {
        updateMargen();
    }, 50);

    // Funciones auxiliares
    function getRate(select) {
        const option = select.options[select.selectedIndex];
        const rate = parseFloat(option.getAttribute("data-rate")) || 0;
        return rate / 100;
    }

    // --- BLOQUE COMPRA ---
    function updateCompraConIva() {
        const sinIva = parseFloat(inputCompraSinIva.value) || 0;
        const rate = getRate(selectIvaCompra);
        inputCompraConIva.value = (sinIva * (1 + rate)).toFixed(2);
        updateVentaSinIva();
    }

    function updateCompraSinIva() {
        const conIva = parseFloat(inputCompraConIva.value) || 0;
        const rate = getRate(selectIvaCompra);
        if (rate > 0) {
            inputCompraSinIva.value = (conIva / (1 + rate)).toFixed(2);
        } else {
            inputCompraSinIva.value = conIva.toFixed(2);
        }
        updateVentaSinIva();
    }

    // --- BLOQUE VENTA ---
    function updateVentaSinIva() {
        let baseCompra = parseFloat(inputCompraConIva.value) || 0;
        const margen = parseFloat(inputMargen.value) || 0;

        if (!baseCompra || baseCompra === 0) {
            baseCompra = parseFloat(inputVentaSinIva.value) || 0;
        }

        const ventaSinIva = baseCompra * (1 + margen / 100);
        inputVentaSinIva.value = ventaSinIva.toFixed(2);
        updateVentaConIva();
    }

    function updateVentaConIva() {
        const ventaSinIva = parseFloat(inputVentaSinIva.value) || 0;
        const rate = getRate(selectIvaVenta);
        inputVentaConIva.value = (ventaSinIva * (1 + rate)).toFixed(2);
    }

    function updateVentaSinIvaFromConIva() {
        const ventaConIva = parseFloat(inputVentaConIva.value) || 0;
        const rate = getRate(selectIvaVenta);
        if (rate > 0) {
            inputVentaSinIva.value = (ventaConIva / (1 + rate)).toFixed(2);
        } else {
            inputVentaSinIva.value = ventaConIva.toFixed(2);
        }
        updateMargen(); // recalcular margen si se cambia con IVA
    }

    // --- NUEVO: recalcular margen ---
    function updateMargen() {
        const baseCompra = parseFloat(inputCompraConIva.value) || 0;
        const ventaSinIva = parseFloat(inputVentaSinIva.value) || 0;

        if (baseCompra > 0 && ventaSinIva > 0) {
            const margen = ((ventaSinIva - baseCompra) / baseCompra) * 100;
            inputMargen.value = margen.toFixed(2);
        }
    }

    // --- EVENTOS COMPRA ---
    selectIvaCompra.addEventListener("change", updateCompraConIva);
    inputCompraSinIva.addEventListener("input", function () {
        if (document.activeElement === inputCompraSinIva) updateCompraConIva();
    });
    inputCompraConIva.addEventListener("input", function () {
        if (document.activeElement === inputCompraConIva) updateCompraSinIva();
    });

    // --- EVENTOS VENTA ---
    selectIvaVenta.addEventListener("change", updateVentaConIva);

    inputMargen.addEventListener("input", function () {
        if (document.activeElement === inputMargen) updateVentaSinIva();
    });

    inputVentaSinIva.addEventListener("input", function () {
        if (document.activeElement === inputVentaSinIva) {
            updateVentaConIva();
            updateMargen(); // recalcular margen si se cambia sin IVA
        }
    });

    inputVentaConIva.addEventListener("input", function () {
        if (document.activeElement === inputVentaConIva) {
            updateVentaSinIvaFromConIva();
        }
    });
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const balanzaCheckbox = document.getElementById("balanza");
        const codigoInput = document.getElementById("codigo");
        if (!balanzaCheckbox || !codigoInput) return;

        balanzaCheckbox.addEventListener("change", function() {
            if (this.checked) {
                // Limitar a 5 caracteres y solo números
                codigoInput.setAttribute("maxlength", "5");
                codigoInput.addEventListener("input", validarNumeros);

                // Truncar inmediatamente si ya hay más de 5 dígitos
                codigoInput.value = codigoInput.value.replace(/[^0-9]/g, '').substring(0, 5);
            } else {
                // Quitar restricción
                codigoInput.removeAttribute("maxlength");
                codigoInput.removeEventListener("input", validarNumeros);
            }
        });

        function validarNumeros(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, ''); // solo números
        }
    });
</script>

<script>
    $(document).ready(function() {
        $('#relacionados-select').select2({
            placeholder: 'Buscar productos para recomendar...',
            width: '100%'
        });
    });
</script>
@include('partials.alta_rapida', ['arPrefijo' => 'qprovProd', 'arTitulo' => 'Crear proveedor rápido', 'arRuta' => route('quick_create_supplier'), 'arSelect' => 'proveedor-select', 'arKey' => 'supplier', 'arPk' => 'idproveedor'])
