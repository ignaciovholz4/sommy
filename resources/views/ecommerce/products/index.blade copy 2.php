@extends('ecommerce.layouts.main-ecommerce')
@section('contentEcommerce')
<style>
 
/******style for main image*********/
.content-product-item-main {
  display: flex;
  justify-content: center !important;
  align-items: center !important;
}
.content-product-item-main .product-main-image {
  max-height: 400px; 
  object-fit: cover;
}
/********************** */
.w-20{
width: 20%;
}
.gap-1{
  gap: .25rem !important;
}
.mb-5{
  margin-bottom: 1.25rem !important;
}
.ps-lg-10{
  padding-left: 3rem !important;
}
/*************COLOR CHECKBOX****************** */
.color-option {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}
.color-option.selected::after {
    content: '';
    position: absolute;
    top: -3px;
    left: -3px;
    right: -3px;
    bottom: -3px;
    border: 1px solid #696969;
    border-radius: 50%;
}
/******BUTTON VOLOR CSS CHECK V2************** */
.custom-radios div {
  display: inline-block;
}
.custom-radios input[type=radio] {
  display: none;
}
.custom-radios input[type=radio] + label {
  color: #333;
  font-family: Arial, sans-serif;
  font-size: 14px;
}
.custom-radios input[type=radio] + label span {
  display: inline-block;
  width: 40px;
  height: 40px;
  margin: -1px 4px 0 0;
  vertical-align: middle;
  cursor: pointer;
  border-radius: 50%;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.33);
  background-repeat: no-repeat;
  background-position: center;
  text-align: center;
  line-height: 44px;
}
.custom-radios input[type=radio] + label span i {
  opacity: 0;
  transition: all 0.3s ease;
}
.custom-radios input[type=radio]:checked + label span i {
  opacity: 1;
}
</style>
<section class="py-5">
  <div class="container-fluid">
    <div class="container">
      <div>
        <input type="hidden" id="dataProduct" value="{{$getProd}}">
        <input type="hidden" id="dataColor" value="{{$getColor}}">
        <input type="hidden" id="dataVariant" value="{{$getVariantesData}}">
        <input type="hidden" id="dataEachVariant" value="{{$getEachVarianteProd}}">
        <input type="hidden" id="dataCheckVariant" value="{{json_encode($firstMatchVariant, true)}}">

      </div>
     <div class="row">
        <div class="col-md-6">
          <div class="content-product-item-main">
              @if($getProd[0]->tipo_producto_id == 2)
              <a href="{{$firstMatchVariant->url}}" data-fancybox="gallery" title="{{$getProd[0]->nombre}}">
                <img src="{{$firstMatchVariant->url}}" style="" class="product-main-image" id="showImageVariant">
              </a>
              @elseif($getProd[0]->tipo_producto_id == 1)
              <a href="{{$getProd[0]->mainImage}}" data-fancybox="gallery" title="{{$getProd[0]->nombre}}">
                <img src="{{$getProd[0]->mainImage}}" style=""  class="product-main-image" id="showImageVariant">
              </a>
              @else
              <span>No se econtro ninguna imagen</span>
              @endif
          </div>
        </div>
        <div class="col-md-6">
            <div class="ps-lg-10 mt-6 mt-md-0">
              <!--a class="mb-1 d-block" href="">Fruits &amp; Vegetables</a>-->
              <h1 class="mb-1">{{$getProd[0]->nombre}}"</h1>
            
              <div class="fs-4">
                @if($getProd[0]->tipo_producto_id == 2)
                <input type="hidden" value="{{$firstMatchVariant->price}}" id="priceProduct">
                <span class="fw-bold text-dark" id="showPriceVariant">{{ format_money_global($firstMatchVariant->price) }} </span>
                @elseif($getProd[0]->tipo_producto_id == 1)
                <input type="hidden" value="{{$getProd[0]->pventa}}" id="priceProduct">
                <span class="fw-bold text-dark" id="showPriceVariant">{{ format_money_global($getProd[0]->pventa) }} </span>
                @else
                <span>No se econtro ninguno</span>
                @endif
                <!--span class="text-decoration-line-through text-muted">$24</span>
                <span><small class="fs-6 ms-2 text-danger">20% Off</small></span>-->
              </div>
              <hr class="my-6" />
              @if($getProd[0]->tipo_producto_id == 2)
              <label class="form-label fw-bold">Color</label>
              <div class="mb-5 d-flex gap-1">
                <div class="d-flex gap-3">
                  <div class="custom-radios">
                  @foreach ($getColor as $color)
                    @if($firstMatchVariant->color_id == $color->id)
                    <input type="radio" id="color-{{$color->id}}" name="colorProduct" value="{{$color->id}}" checked>
                    @else
                    <input type="radio" id="color-{{$color->id}}" name="colorProduct" value="{{$color->id}}" >
                    @endif
                    <label for="color-{{$color->id}}">
                    <span style="background-color: {{$color->hexadecimal}};">
                      <i class="fa fa-check" style="font-size:22px;color:#ffff !important;" aria-hidden="true"></i>
                    </span>
                    </label>
                  @endforeach
                  </div>
                </div>
              </div>
              <label class="form-label fw-bold">Talla</label>
              <div class="mb-5 d-flex gap-1">
                @foreach ($getVariantesData as $var)
                  @if($firstMatchVariant->product_integration_id == $var->id)
                  <input type="radio" class="btn-check" name="sizeProduct" id="size-{{$var->id}}" value="{{json_encode($var)}}" checked >
                  <label class="btn btn-outline-dark size-btn" for="size-{{$var->id}}">{{$var->name}}</label>
                  @else
                  <input type="radio" class="btn-check" name="sizeProduct" id="size-{{$var->id}}" value="{{json_encode($var)}}" >
                  <label class="btn btn-outline-dark size-btn" for="size-{{$var->id}}">{{$var->name}}</label>
                  @endif
                @endforeach
              </div>
              @elseif($getProd[0]->tipo_producto_id == 1)
              <span></span>
              @else
              <span>No se econtro ninguno</span>
              @endif
              <div class="w-20">
                <div class="input-spinner input-group">
                  <button
                    type="button" id="btnLessCant" class="button-minus text-white btn btn-danger btn-sm ">-</button>
                    <input
                    class="quantity-field form-input form-control form-control-sm"
                    type="number"
                    value="1"
                    name="quantity"
                    id="cantProduct"
                    readonly
                    />
                  <button type="button" id="btnAddCantMore" class="button-plus text-white btn btn-danger btn-sm">+</button>
                </div>
              </div>
              <div class="mt-3 justify-content-start g-2 align-items-center row">
                <div class="d-grid col-xxl-5 col-lg-5 col-md-5 col-5">
                  <button type="button" class="btn btn-add-prod" id="btn-add-product">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="me-2"
                    >
                      <path
                        d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"
                      ></path>
                      <line x1="3" y1="6" x2="21" y2="6"></line>
                      <path d="M16 10a4 4 0 0 1-8 0"></path>
                      </svg>Agregar al carrito
                  </button>
                </div>
                <!--div class="col-md-4 col-4">
                  <button type="button" class="me-1 btn btn-light">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 16 16"
                      width="1em"
                      height="1em"
                      fill="currentColor"
                      class="bi bi-arrow-left-right"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5m14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5"
                      ></path>
                    </svg></button
                  ><button type="button" class="btn btn-light">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="14"
                      height="14"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    >
                      <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                      ></path>
                    </svg>
                  </button>
                </div>-->
              </div>
              <hr class="my-6" />
              <label class="form-label fw-bold">Descripción</label>
              <div>
                <p class="">{{ $getProd[0]->descripcion }}</p>
              </div>
            </div>
        </div>
   
    </div>
     </div>
  </div>
</section>
<script>
</script>
@endsection
@section('scriptEcommerce')
<script src="{{asset('js/ecommerce/shopping-card.js')}}"></script>
@endsection