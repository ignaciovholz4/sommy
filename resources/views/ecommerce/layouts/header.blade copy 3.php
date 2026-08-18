<style>
.header {
    background-color: rgba(51, 51, 51, 1);
    border-color: rgba(129, 129, 129, 0.2);
    border-bottom-width: 1px;
    border-bottom-style: solid;
}

.navbar {
    /*box-shadow: 0 2px 4px rgba(0,0,0,0.1);*/
    padding: 12px 0;
}
.navbar-brand img:hover {
    opacity: 0.8;
}
.nav-link {
    color: #333 !important;
    font-weight: 500;
    padding: 8px 15px !important;
}
.nav-link:hover {
    /*color: #0d6efd !important;*/
    color: red !important;
}
.cart-badge {
    position: relative;
    top: -8px;
    right: 3px;
    font-size: 0.7rem;
}
/************************* */
.main-content-header{
    margin-right:10px !important;
    margin-left:10px !important;
    width: 100% !important;
    display: flex;
    flex-wrap: inherit;
    align-items: center;
    justify-content: space-between;
    }
    .logo img {
      height: 100px;
      width: 100px;
      border-radius: 50%; 
      object-fit: cover; 
      margin-right: 0.5rem;
    }
    /*******color nav************ */
    .nav-color{
      color: #fff !important;
    }

    .nav-content-main{
        display: flex !important;

        flex-basis: 100% !important;
        flex-grow:1 !important;
        align-items: center !important;

    }
    .btn-show-close{
        border: 1px solid #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='%23ffffff' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E"); /* White icon */
    }
    /****header navbar movible****/
    @media (max-width: 768px) {
        .logo img {
            height: 100px;
            width: 100px;
            border-radius: 50%; 
            object-fit: cover; 
            margin-right: 0.5rem;
        }
        .navbar {
            padding: 0.3rem 1rem !important; /* Reduce padding for mobile */
        }
        .navbar-brand {
            font-size: 1rem !important; /* Smaller brand font size */
        }
        .nav-link {
            font-size: 0.875rem !important; /* Smaller font size for links */
            padding: 0.25rem 0.5rem !important; /* Reduce padding for links */
        }
        .navbar-toggler {
            padding: 0.25rem 0.5rem; /* Smaller padding */
            font-size: 0.75rem; /* Smaller icon size */
            width: 32px; /* Smaller width */
            height: 32px; /* Smaller height */
            border: 1px solid #ffffff;
        }
        .btn-show-close {
            padding: 0.25rem 0.5rem; /* Smaller padding */
            font-size: 0.75rem; /* Smaller icon size */
            width: 30px; /* Smaller width */
            height: 30px; /* Smaller height */
            border: 1px solid #ffffff;
            border-radius: 5px;
            margin-left:10px;
        }
        .navbar-toggler-icon {
            width: 16px; /* Smaller icon width */
            height: 16px; /* Smaller icon height */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='%23ffffff' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E"); /* White icon */
        }
        .navbar-menu-main{
             /*display: none; 
            flex-direction: column;
            gap: 10px;*/
            position: fixed; /* Stays in front of content */
            top: 0;
            left: 0;
            width: 50%;
            height: auto;
            background-color: rgba(43, 42, 42, 0.95); /* Semi-transparent background */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: translateY(-100%); /* Hidden by default */
            transition: transform 0.4s ease; /* Smooth slide-in/out */
            z-index: 1050; /* Ensure it stays above content */
        }
        .navbar-menu-main.show {
            display: flex; /* Show the menu when toggled */
            transform: translateY(0); 
            border:1px solid #555;
        }
        .navbar-menu-main li{
            margin: 10px 0;
            transition: color 0.3s;
            /*border:1px solid #ffffff;
            width: 100% !important;*/
        }
        /*.navbar-menu-main li:hover{
            background-color: #555;
            color: white !important;
        }*/
        .navbar-toggler {
            margin-right:5px;
            position: absolute;
            top: 10px;
            right: 20px;
            z-index: 1100;
            border: none;
        }
        .nav-content-secondary{
            display: flex;
            flex-direction: row;
            position: absolute;
            top: 10px;
            right: 20px;
            /*z-index: 1100;*/
            
        }
        .li-show-close{
            display: block !important;
        }
        .span-text-user{
            display: none !important;
        }

    }
</style>
<header class="header header-ecommerce">
<nav class="navbar navbar-expand-lg header">
    <div class="main-content-header">
        <!-- Logo -->
        <a class="navbar-brand logo" href="{{url('/')}}">
            <img src="{{asset($arrayEmpresa['image'])}}" width="50" height="50" alt="Logo empresa">
        </a>
        <!-- Mobile Toggle -->
        <!--button class="navbar-toggler" type="button" id="">
            <span class="navbar-toggler-icon "></span>
        </button>-->

        <!-- Main Nav Content -->
        <!--div class="collapse navbar-collapse" id="navbarNav">-->
        <div class="nav-content-main" id="navbarNav">
            <!-- Categories -->
            <ul class="navbar-nav mx-auto navbar-menu-main">
                @foreach ($getCategoryLimit as $catLimit)
                <li class="nav-item " style="">
                    <a class="nav-link nav-color nav-line" href="{{url('Ecommercecategory', ['id'=> $catLimit->idcategoria])}}">{{$catLimit->nombre}}</a>
                </li>
                @endforeach
            </ul>

            <!-- Login & Cart -->
            <ul class="navbar-nav nav-content-secondary">
                @auth
                <li class="nav-item me-2">
                    <a class="nav-link nav-color" href="{{ url('/logout') }}">
                        <i class="fa-solid fa-user me-1"></i>
                        Cerrar sesion
                    </a>
                </li>
                @else
                <li class="nav-item me-2">
                    <a class="nav-link nav-color" href="{{ url('/login') }}">
                        <i class="fa-solid fa-user me-1"></i>
                        <span class="span-text-user">Iniciar sesion</span>
                    </a>
                </li>
                @endauth
                <li class="nav-item me-2">
                    <a class="nav-link nav-color" href="#">
                        <i class="fa fa-search me-1"></i>
                    </a>
                </li>
                <li class="nav-item">
                    @if (strpos(url()->current(), '/Ecommerceorder') !== false)
                    <a class="nav-link nav-color" href="#">
                    @else 
                    <a class="nav-link nav-color" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                    @endif 
                    <i class="fa-solid fa-cart-plus"></i>
                        <span class="badge bg-danger cart-badge show-total-header-products-added">0</span>
                    </a>
                </li>
                <li class="nav-item li-show-close" style="display:none;">
                    <button class="nav-link nav-color btn-show-close" id="navbarToggle">
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
</header>