    <header>
      <div class="container-fluid">
        <div class="row py-3 border-bottom">
          
          <div class="col-sm-4 col-lg-3 text-center text-sm-start">
            <div class="main-logo">
              <a href="{{url('/')}}">
                <img src="{{asset('imagenes/ecommerce/images/logo.png')}}" alt="logo" class="img-fluid">
                <!--img src="{{asset('imagenes/empresa/logo.png')}}" alt="logo" class="img-fluid brand-image img-circle" width="50" height="50">-->
              </a>
            </div>
          </div>
          
          <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block">
            <div class="search-bar row bg-light p-2 my-2 rounded-4">
              <div class="col-11 col-md-11">
                <form id="search-form" class="text-center" action="index.html" method="post">
                  <input type="text" class="form-control border-0 bg-transparent" placeholder="Buscar para mas de 20,000 productos" />
                </form>
              </div>
              <div class="col-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/></svg>
              </div>
            </div>
          </div>
          
          <div class="col-sm-8 col-lg-4 d-flex justify-content-end gap-5 align-items-center mt-4 mt-sm-0 justify-content-center justify-content-sm-end">
            <div class="support-box text-end d-none d-xl-block">
              <span class="fs-6 text-muted"><i class="fa-solid fa-envelope"></i> Contacto</span>
              <!--h5 class="mb-0">+980-34984089</h5>-->
            </div>

            <ul class="d-flex justify-content-end list-unstyled m-0">
              <!--li>
                <a href="#" class="rounded-circle bg-light p-2 mx-1">
                  <i class="fa-regular fa-heart"></i>
                </a>
              </li>-->
              <li>
            <div class="cart d-none d-lg-block dropdown">
              <button class="border-0 bg-transparent d-flex flex-column gap-2 lh-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                <span class="fs-6 text-muted dropdown-toggle"><i class="fa-solid fa-cart-shopping"></i> <span class="badge text-bg-success show-total-header-products-added" style="font-size: 11px;">0</span></span>
                <!--span class="cart-total fs-5 fw-bold">$1290.00</span>-->
              </button>
            </div>
                <!--a href="#" class="rounded-circle bg-light p-2 mx-1">
                  <i class="fa-solid fa-user"></i>
                </a>-->
              </li>
              <li class="d-lg-none">
                <a href="#" class="rounded-circle bg-light p-2 mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                  <i class="fa-solid fa-cart-plus"></i>
                </a>
              </li>
              <li class="d-lg-none">
                <a href="#" class="rounded-circle bg-light p-2 mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" aria-controls="offcanvasSearch">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </a>
              </li>
            </ul>

            <div class="dropdown">
              <a href="{{ url('/login') }}" class="border-0 bg-transparent d-flex flex-column gap-2 lh-1">
                <i class="fa-solid fa-user"></i>
              </a>
            </div>
          </div>

        </div>
      </div>
    </header>