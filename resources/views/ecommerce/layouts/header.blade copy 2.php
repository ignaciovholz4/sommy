
    <style>
          .header {
      /*display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #f8f9fa;
      border-bottom: 1px solid #ddd;*/
      height: 60px; 
      background-color: rgba(51, 51, 51, 1);
      border-color: rgba(129, 129, 129, 0.2);
      border-bottom-width: 1px;
      border-bottom-style: solid;
    }
      .main-logo{
        /*border: 4px solid;*/
        width: 20%;
      }

      .logo{
         display: flex;
      align-items: center;
      text-decoration: none;
      }
      .logo img {
      height: 50px;
      width: 50px;
      border-radius: 50%; 
      object-fit: cover; 
      margin-right: 0.5rem;
    }
    .ul-test li {
      background-color: #007bff;
      color: white;
      text-align: center;
      font-size: 1rem;
    }
    .v-line{
      border-left: thick solid red;
      height:100%;
    }
    .nav-color{
      color: #fff !important;
    }
    .nav-color2{
      color: red !important;
    }
    .nav-line::after {
      content: ''; 
      width: 100%;
      height: 2px;
      background-color: red;
      transition: width 0.3s ease;
      display: block;
         left: 0;
         bottom: -8px; 
         opacity: 0;
    }
    .nav-line:hover::after {
      width: 100%;
      opacity: 1;
    }
    
    </style>
    <header class="header">
      <div class="container-fluid">
        <div class="row">
          
          <div class="col-sm-4 col-lg-3 text-center text-sm-start">
            <div class="main-logo">
              <a class="logo" href="#">
                <img src="{{asset('imagenes/empresa/ICON_YOUTUBE.png')}}" alt="" width="40" height="40" class="d-inline-block align-text-top">
              </a>
            </div>
          </div>
          
          <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block" style="">
            <div class="" style="">
              <div class="col-11 col-md-11">
              <nav class="navbar navbar-expand-lg">
                  <div class="container-fluid">
                    <div class="collapse navbar-collapse " id="navbarNavDropdown">
                      <ul class="navbar-nav mx-auto" style="gap:15px;" >
                        @foreach ($getCategoryLimit as $catLimit)
                        <li class="nav-item " style="border:1px solid #fff;display: inline-block !important;">
                          <span >{{$catLimit->nombre}}</span>
                        </li>
                        @endforeach
                        <!--li class="nav-item">
                          <a class="nav-link nav-color nav-line" href="#">iPad</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-color nav-line" href="#">iPhone</a>
                        </li>
                        <li class="nav-item dropdown">
                          <a class="nav-link dropdown-toggle nav-color nav-line" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Whatch
                          </a>
                          <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                          </ul>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-color nav-line" href="#">AppleTV</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link nav-color nav-line" href="#">AirPods</a>
                        </li>
                         <li class="nav-item">
                          <a class="nav-link nav-color nav-line" href="#">Audio</a>
                        </li>-->
                      </ul>
                    </div>
                  </div>
                </nav>
              </div>
            </div>
          </div>
          
          <div class="col-sm-8 col-lg-4 d-flex justify-content-end gap-4 align-items-center mt-4 mt-sm-0 justify-content-center justify-content-sm-end">
            <div class="support-box text-end d-none d-xl-block">
              <span class="fs-6 text-muted nav-color"><i class="fa fa-search" aria-hidden="true"></i></span>
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
                    <!--span class="fs-6 text-muted dropdown-toggle"><i class="fa-solid fa-cart-shopping"></i> <span class="badge text-bg-success show-total-header-products-added" style="font-size: 11px;">0</span></span>-->
                    <span class="fs-6 text-muted dropdown-toggle nav-color"><i class="fa-solid fa-cart-shopping"></i></span>
                  </button>
                </div>
              </li>
              <li class="d-lg-none">
                <a href="#" class="rounded-circle p-2 mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                  <i class="nav-color fa-solid fa-cart-plus"></i>
                </a>
              </li>
              <li class="d-lg-none">
                <a href="#" class="rounded-circle p-2 mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" aria-controls="offcanvasSearch">
                  <i class=" nav-color fa-solid fa-magnifying-glass"></i>
                </a>
              </li>
            </ul>

            <div class="dropdown">
              <!--a href="{{ route('login') }}" class="border-0 bg-transparent d-flex flex-column gap-2 lh-1">
                <i class="fa-solid fa-user"></i>
              </a>-->
              <a class="nav-link dropdown-toggle nav-color" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
              </a>
              <ul class="dropdown-menu " aria-labelledby="navbarDropdownMenuLink">
                <li><a class="dropdown-item" href="{{ url('/login') }}">Iniciar sesion</a></li>
              </ul>
            </div>
            <div class="v-line"></div>
            <div >
                <span class="nav-color2">$0</span><br>
                <span class="nav-color show-total-header-products-added">0 items</span>
            </div>
          </div>

        </div>
      </div>
    </header>