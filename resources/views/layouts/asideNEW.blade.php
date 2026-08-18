@php
  $prefix = Request::route()->getPrefix();
  $route = Route::current()->getName();
  $resp = request()->path();
  $exp = explode("/", $resp); 
@endphp
<aside class="main-sidebar style-modal-form elevation-2" >
    <!-- Brand Logo -->
    <br>
    <a href="#" class="brand-link" style="color: #060606;">
      <img src="{{asset("Logo_facturarg.jpeg")}}" style="height: 60px !important; width:205px; margin-left:13px; margin-top:6px; margin-bottom:14px">
      <!--<span class=""><strong>{{strtoupper(substr($name,0,16))}}</strong></span>-->
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <!--<div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{asset('dist/img/avatar04.png')}}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block" style="color: #8d8989;">{{ Auth::user()->name }}</a>
        </div>
      </div>-->
      <!-- Sidebar Menu -->
      <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

      {{-- 1. TIENDA --}}
      @can('haveaccess', 'tienda.index')
      <li class="nav-item has-treeview {{($exp[0] == 'tienda')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-store"></i>
          <p>
            Tienda
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          @can('haveaccess', 'tienda_config.index')
          <li class="nav-item">
            <a href="{{url('tienda/config')}}" class="nav-link {{'tienda/config' == request()->path() ?'active':''}}">
              <i class="far fa-circle nav-icon"></i>
              <p>Configuración</p>
            </a>
          </li>
          @endcan
        </ul>
      </li>
      @endcan

      {{-- 2. INGRESOS --}}
      @can('haveaccess', 'ingresos.index')
      <li class="nav-item has-treeview {{($exp[0] == 'ingresos')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-hand-holding-usd"></i>
          <p>
            Ingresos
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item"><a href="{{url('ingresos/ventas')}}" class="nav-link {{'ingresos/ventas' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Ventas a clientes</p></a></li>
          <li class="nav-item"><a href="{{url('ingresos/presupuestos')}}" class="nav-link {{'ingresos/presupuestos' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Presupuestos</p></a></li>
          <li class="nav-item"><a href="{{url('ingresos/pedidos')}}" class="nav-link {{'ingresos/pedidos' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Pedidos ecommerce</p></a></li>
          <li class="nav-item"><a href="{{url('ingresos/otros')}}" class="nav-link {{'ingresos/otros' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Otros ingresos</p></a></li>
          <li class="nav-item"><a href="{{url('ingresos/pendientes')}}" class="nav-link {{'ingresos/pendientes' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Entregas pendientes</p></a></li>
        </ul>
      </li>
      @endcan

      {{-- 3. EGRESOS --}}
      @can('haveaccess', 'egresos.index')
      <li class="nav-item has-treeview {{($exp[0] == 'egresos')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-money-bill-wave"></i>
          <p>
            Egresos
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item"><a href="{{url('egresos/compras')}}" class="nav-link {{'egresos/compras' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Compras a proveedores</p></a></li>
          <li class="nav-item"><a href="{{url('egresos/otros')}}" class="nav-link {{'egresos/otros' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Otros egresos</p></a></li>
        </ul>
      </li>
      @endcan

      {{-- 4. CONTACTOS --}}
      @can('haveaccess', 'contactos.index')
      <li class="nav-item has-treeview {{($exp[0] == 'contactos')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-address-book"></i>
          <p>
            Contactos
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item"><a href="{{url('contactos/clientes')}}" class="nav-link {{'contactos/clientes' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Clientes</p></a></li>
          <li class="nav-item"><a href="{{url('contactos/proveedores')}}" class="nav-link {{'contactos/proveedores' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Proveedores</p></a></li>
        </ul>
      </li>
      @endcan

      {{-- 5. PRODUCTOS --}}
      @can('haveaccess', 'productos.index')
      <li class="nav-item has-treeview {{($exp[0] == 'productos')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-boxes"></i>
          <p>
            Productos
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item"><a href="{{url('productos')}}" class="nav-link {{'productos' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Productos</p></a></li>
          <li class="nav-item"><a href="{{url('productos/listas')}}" class="nav-link {{'productos/listas' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Listas de precio de venta</p></a></li>
          <li class="nav-item"><a href="{{url('productos/gestion-listas')}}" class="nav-link {{'productos/gestion-listas' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Gestión de productos en listas</p></a></li>
        </ul>
      </li>
      @endcan

      {{-- 6. CUENTAS --}}
      @can('haveaccess', 'cuentas.index')
      <li class="nav-item has-treeview {{($exp[0] == 'cuentas')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-university"></i>
          <p>
            Cuentas
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item"><a href="{{url('cuentas')}}" class="nav-link {{'cuentas' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Cuentas</p></a></li>
          <li class="nav-item"><a href="{{url('cuentas/operaciones')}}" class="nav-link {{'cuentas/operaciones' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Operaciones en cuentas</p></a></li>
        </ul>
      </li>
      @endcan

      {{-- 7. ARCA --}}
      @can('haveaccess', 'arca.index')
      <li class="nav-item has-treeview {{($exp[0] == 'arca')?'menu-open':''}}">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-archway"></i>
          <p>
            ARCA
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item"><a href="{{url('arca/config')}}" class="nav-link {{'arca/config' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Configuración</p></a></li>
          <li class="nav-item"><a href="{{url('arca/puntos')}}" class="nav-link {{'arca/puntos' == request()->path() ?'active':''}}"><i class="far fa-circle nav-icon"></i><p>Puntos de Venta</p></a></li>
        </ul>
      </li>
      @endcan
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>