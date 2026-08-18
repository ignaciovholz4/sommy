@extends('ecommerce.layouts.main-ecommerce')
@section('contentEcommerce')
<style>
    .section-content-search-category{
        background:#f8f9fa;
    }
    .color-option {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
        }

        .filter-group {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .product-name{
        height:50px; overflow: hidden; text-overflow: ellipsis;
        border: 1px solid red;
        }
</style>
<section class="py-5 section-content-search-category" style="">
    <div class="container-fluid">
        <div class="p-2" >
             <!-- Top Bar -->
            <div class="row mb-3">
                <div class="col-md-8">
                 <h4 class="mb-0 mt-1">Colección de productos</h4>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm search-bar">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar..." aria-label="Search" aria-describedby="search-addon">
                            <button class="btn btn-secondary" type="button" id="search-addon">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!--div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <div class="search-bar">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..." aria-label="Search" aria-describedby="search-addon">
                            <button class="btn btn-outline-secondary" type="button" id="search-addon">
                                    <i class="fas fa-search"></i>
                                </button>
                        </div>
                    </div>
                </div>
            </div>-->
            <div class="row g-4">
                <!-- Filters Sidebar -->
                <div class="col-lg-3">
                    <div class="card filter-sidebar p-4 border-0 shadow-sm">
                        <div class="filter-group">
                            <h6 class="mb-3">Categorias</h6>
                            @foreach ($getDataAllCategory as $cat)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="electronics">
                                <label class="form-check-label" for="electronics">
                                    {{$cat->nombre}}
                                </label>
                            </div>
                            @endforeach
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="clothing">
                                <label class="form-check-label" for="clothing">
                                    Clothing
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="accessories">
                                <label class="form-check-label" for="accessories">
                                    Accessories
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h6 class="mb-3">Gama de precios</h6>
                            <input type="range" class="form-range" min="0" max="1000" value="500">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">$0</span>
                                <span class="text-muted">$1000</span>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h6 class="mb-3">Colores</h6>
                            <div class="d-flex gap-2">
                                <div>
                                    <input class="form-check-input color-option" style="background: #000000;" type="radio" name="radioNoLabel" id="radioNoLabel1" value="" aria-label="...">
                                </div>
                                <div>
                                    <input class="form-check-input color-option" style="background: #dc2626;" type="radio" name="radioNoLabel" id="radioNoLabel1" value="" aria-label="...">
                                </div>
                                <div>
                                    <input class="form-check-input color-option" style="background: #2563eb;" type="radio" name="radioNoLabel" id="radioNoLabel1" value="" aria-label="...">
                                </div>
                                <div>
                                    <input class="form-check-input color-option" style="background: #16a34a;" type="radio" name="radioNoLabel" id="radioNoLabel1" value="" aria-label="...">
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h6 class="mb-3">Clasificacion</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="rating" id="rating4">
                                <label class="form-check-label" for="rating4">
                                    <i class="bi bi-star-fill text-warning"></i> 4 & above
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="rating" id="rating3">
                                <label class="form-check-label" for="rating3">
                                    <i class="bi bi-star-fill text-warning"></i> 3 & above
                                </label>
                            </div>
                        </div>

                        <button class="btn btn-outline-primary w-100">Apply Filters</button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="col-lg-9">
                    <!--div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">-->
                    <div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                        <!-- Product Card 1 -->
                        @foreach ($getDataProd as $product)
                        <div class="col">
                            <div class="product-item">
                                @if($product->has_price_list)
                                <span class="badge bg-success position-absolute m-3">
                                  <i class="fas fa-tags"></i> Oferta
                                </span>
                                @endif
                                <figure>
                                <a href="" title="Product Title">
                                    <img src="{{ $product->url }}"  class="tab-image">
                                </a>
                                </figure>
                                <div class="product-name">
                                <h3>{{$product->name_product}}</h3>
                                </div>
                                <span class="qty">{{$product->name_variante_variacion}} </span><span class="rating"></span>
                                @if($product->has_price_list)
                        <div class="price-container">
                          <span class="price original-price text-muted text-decoration-line-through">{{ format_money_global($product->original_price) }}</span>
                          <span class="price effective-price text-success fw-bold">{{ format_money_global($product->price) }}</span>
                        </div>
                        @else
                        <span class="price">{{ format_money_global($product->price) }}</span>
                        @endif
                                <div class="text-center">
                                <button class="btn btn-add-prod" data-value="">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <!--div class="col">
                            <div class="product-item">
                                <figure>
                                <a href="" title="Product Title">
                                    <img src="https://mdbootstrap.com/img/bootstrap-ecommerce/items/11.webp"  class="tab-image img-fluid">
                                </a>
                                </figure>
                                <div class="product-name">
                                <h3>$product->name_product</h3>
                                </div>
                                <span class="qty"> $product->name_variante_variacion </span><span class="rating"></span>
                                <span class="price"> precio</span>
                                <div class="text-center">
                                <button class="btn btn-add-prod" data-value="">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col">
                            <div class="product-item">
                                <figure>
                                <a href="" title="Product Title">
                                    <img src="https://mdbootstrap.com/img/bootstrap-ecommerce/items/10.webp"  class="tab-image img-fluid">
                                </a>
                                </figure>
                                <div class="product-name">
                                <h3>$product->name_product</h3>
                                </div>
                                <span class="qty"> $product->name_variante_variacion </span><span class="rating"></span>
                                <span class="price"> precio</span>
                                <div class="text-center">
                                <button class="btn btn-add-prod" data-value="">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="product-item">
                                <figure>
                                <a href="" title="Product Title">
                                    <img src="https://mdbootstrap.com/img/bootstrap-ecommerce/items/8.webp"  class="tab-image img-fluid">
                                </a>
                                </figure>
                                <div class="product-name">
                                <h3>$product->name_product</h3>
                                </div>
                                <span class="qty"> $product->name_variante_variacion </span><span class="rating"></span>
                                <span class="price"> precio</span>
                                <div class="text-center">
                                <button class="btn btn-add-prod" data-value="">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>
                     
                        <div class="col">
                            <div class="product-item">
                                <figure>
                                <a href="" title="Product Title">
                                    <img src="https://mdbootstrap.com/img/bootstrap-ecommerce/items/9.webp"  class="tab-image img-fluid">
                                </a>
                                </figure>
                                <div class="product-name">
                                <h3>$product->name_product</h3>
                                </div>
                                <span class="qty"> $product->name_variante_variacion </span><span class="rating"></span>
                                <span class="price"> precio</span>
                                <div class="text-center">
                                <button class="btn btn-add-prod" data-value="">Agregar al carrito</button>
                                </div>
                            </div>
                        </div>-->

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection