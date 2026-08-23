<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Caja\CajainicioController;
use App\Http\Controllers\Caja\CortecajaController;
use App\Http\Controllers\Caja\CorteparcialController;
use App\Http\Controllers\Caja\HistoricocajaController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserdashboardController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Reportes\GraphicsController;
use App\Http\Controllers\Configuracion\ConfiguracionController;
use App\Http\Controllers\Articulo\InventarioController;
use App\Http\Controllers\Articulo\ArticuloController;
use App\Http\Controllers\Articulo\EditpriceController;
use App\Http\Controllers\Quotes\QuotesController;
use App\Http\Controllers\Configuracion\BannerController;
use App\Http\Controllers\Articulo\ColorController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\SucursalArticuloController;
use App\Http\Controllers\SucursalCombinacionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Caja\CajaController;
use App\Http\Controllers\Caja\CajaResumenController;
use App\Http\Controllers\Cuentas\CuentasController;
use App\Http\Controllers\Cuentas\CajaAperturaController;
use App\Http\Controllers\Cuentas\MovimientoController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\Venta\VentaController;
use App\Http\Controllers\Venta\VentaController2;
use App\Http\Controllers\Compra\CompraController;
use App\Http\Controllers\Devolucion\DevolucionController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Variaciones\VariacionesController;
use App\Http\Controllers\Variaciones\ProductovarianteController;
use App\Http\Controllers\Variaciones\ProductointegracionvarianteController;
use App\Http\Controllers\Venta\VentaorderecommerceController;
use App\Http\Controllers\Ecommerce\EcommerceController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Ecommerce\EcommercecategoryController;
use App\Http\Controllers\Ecommerce\EcommerceproductController;
use App\Http\Controllers\Ecommerce\EcommerceorderController;
use App\Http\Controllers\Ecommerce\EcommerceaboutController;
use App\Http\Controllers\Ecommerce\EcommercecontactoController;
use App\Http\Controllers\Ecommerce\EcommercesearchcategoryController;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\Tenant\TrainingVideoController;
use App\Http\Controllers\Email\TicketController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are registered either under a subdomain (production) or
| a path prefix (local development). See web.php for the registration.
|
*/

Route::get('/', [EcommerceController::class, 'index']);

// Rutas SEO con slug
Route::get('/producto/{slug}', [EcommerceproductController::class, 'showBySlug'])->name('ecommerce.producto');
Route::get('/categoria/{slug}', [EcommercecategoryController::class, 'showBySlug'])->name('ecommerce.categoria');
Route::get('/productos', [EcommercecategoryController::class, 'todos'])->name('ecommerce.catalogo');
Route::get('/buscar', [EcommercesearchcategoryController::class, 'index'])->name('ecommerce.buscar');

// Rutas viejas por ID → redirect 301 a la URL con slug
Route::get('/Ecommercecategory/{id}', function ($id) {
    $categoria = \App\Models\Categoria::find($id);
    abort_unless($categoria && $categoria->slug, 404);
    return redirect('/categoria/' . $categoria->slug, 301);
});
Route::get('/Ecommerceproduct/{id}', function ($id) {
    $producto = \App\Models\Articulo::find($id);
    abort_unless($producto && $producto->slug, 404);
    return redirect('/producto/' . $producto->slug, 301);
});

// El checkout exige cuenta de comprador (registro en la primera compra)
Route::get('/Ecommerceorder', [EcommerceorderController::class, 'show'])->middleware('auth.cliente');

/** CUENTAS DE COMPRADORES (guard cliente) */
Route::get('/cuenta/login', [\App\Http\Controllers\Ecommerce\ClienteAuthController::class, 'showLogin'])->name('cliente.login');
Route::post('/cuenta/login', [\App\Http\Controllers\Ecommerce\ClienteAuthController::class, 'login'])->name('cliente.login.post');
Route::get('/cuenta/registro', [\App\Http\Controllers\Ecommerce\ClienteAuthController::class, 'showRegister'])->name('cliente.registro');
Route::post('/cuenta/registro', [\App\Http\Controllers\Ecommerce\ClienteAuthController::class, 'register'])->name('cliente.registro.post');
Route::get('/cuenta/salir', [\App\Http\Controllers\Ecommerce\ClienteAuthController::class, 'logout'])->name('cliente.logout');
Route::get('/cuenta/pedidos', [\App\Http\Controllers\Ecommerce\ClientePedidosController::class, 'index'])->name('cliente.pedidos')->middleware('auth.cliente');

/** PROGRAMA DE REVENDEDORES (público: se registran, reciben link y QR, nada más) */
Route::get('/revendedores', [\App\Http\Controllers\Ecommerce\RevendedorPublicController::class, 'landing'])->name('revendedores.landing');
Route::post('/revendedores', [\App\Http\Controllers\Ecommerce\RevendedorPublicController::class, 'store'])->name('revendedores.store');
Route::post('/revendedores/recuperar', [\App\Http\Controllers\Ecommerce\RevendedorPublicController::class, 'recuperar'])->name('revendedores.recuperar');
Route::get('/revendedores/link/{codigo}', [\App\Http\Controllers\Ecommerce\RevendedorPublicController::class, 'link'])->name('revendedores.link');
Route::get('/revendedores/qr/{codigo}', [\App\Http\Controllers\Ecommerce\RevendedorPublicController::class, 'qr'])->name('revendedores.qr');
Route::get('/r/{codigo}', [\App\Http\Controllers\Ecommerce\RevendedorPublicController::class, 'ref'])->name('revendedores.ref');

/** PÁGINAS LEGALES + FEED DE CATÁLOGO */
Route::get('/terminos', [\App\Http\Controllers\Ecommerce\LegalController::class, 'terminos'])->name('legal.terminos');
Route::get('/cambios-y-devoluciones', [\App\Http\Controllers\Ecommerce\LegalController::class, 'devoluciones'])->name('legal.devoluciones');
Route::get('/arrepentimiento', [\App\Http\Controllers\Ecommerce\LegalController::class, 'arrepentimiento'])->name('legal.arrepentimiento');
Route::post('/arrepentimiento', [\App\Http\Controllers\Ecommerce\LegalController::class, 'arrepentimientoStore'])->name('legal.arrepentimiento.post');
Route::get('/feed/productos.xml', [\App\Http\Controllers\Ecommerce\FeedController::class, 'productos'])->name('feed.productos');
Route::get('/cuenta/pedidos/{id}', [\App\Http\Controllers\Ecommerce\ClientePedidosController::class, 'show'])->name('cliente.pedido')->middleware('auth.cliente');
Route::post('/Ecommercesaveorder', [EcommerceorderController::class, 'store']);
Route::post('/EcommerceFindEmailCustomer', [EcommerceorderController::class, 'validateEmaiLIfExist']);

// Pagos online (MercadoPago) + página de agradecimiento
Route::post('/mercadopago/webhook', [\App\Http\Controllers\Ecommerce\EcommercepagoController::class, 'webhook']);
Route::get('/pedido/gracias/{orderId}', [\App\Http\Controllers\Ecommerce\EcommercepagoController::class, 'gracias'])->name('ecommerce.gracias');
Route::get('/pedido/verificar-pago/{orderId}', [\App\Http\Controllers\Ecommerce\EcommercepagoController::class, 'verificarPago']);
Route::get('/nosotros', [EcommerceaboutController::class, 'index']);
Route::get('/contacto', [EcommercecontactoController::class, 'index']);

Route::get('/ticket', function(){
 return view('ventas/venta/impresion');
});
/**Ruta principal */
/**ROUTE FOR ADMIM */
Route::get('dashboard', [AdminController::class, 'index'])->name('admin')->middleware(['auth','verified','Isadmin']);
Route::get('/get-data-sales', [AdminController::class, 'get_sales'])->name('get-data-sales')->middleware(['auth','verified','Isadmin']);
/**ROUTE FOR USER */
Route::get('userdashboard', [UserdashboardController::class, 'index'])->name('userdashboard')->middleware(['auth','verified']);
//Auth::routes();

/**RUTAS DE AUTENTICACION DEL LOGIN*/
Route::get('/login', 'ConnectController@index')->name('tenant.login');
Route::post('/login','ConnectController@postLogin')->name('tenant.login.post');
/**RUTA DE CERRAR CESION */
Route::get('/logout', 'ConnectController@getLogout')->name('tenant.logout')->middleware('auth');

// GET routes referenced from Blade/Fortify (config/fortify.php has views=false, so framework does not register these).
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function () {
        return view('auth.passwords.email');
    })->name('password.request');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => request()->query('email'),
        ]);
    })->name('password.reset');

    // Envío del enlace de restablecimiento (Fortify fue removido: se usa el broker nativo)
    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Ingresá tu correo electrónico.',
            'email.email'    => 'El correo no tiene un formato válido.',
        ]);

        try {
            $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            \Log::error('Fallo el envío del mail de restablecimiento', ['error' => $e->getMessage()]);
            return back()->withErrors(['email' => 'No pudimos enviar el correo. Verificá la configuración de mail o intentá más tarde.']);
        }

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with('status', 'Te enviamos un correo con el enlace para restablecer tu contraseña. Revisá tu bandeja de entrada (y spam).')
            : back()->withErrors(['email' => match ($status) {
                \Illuminate\Support\Facades\Password::RESET_THROTTLED => 'Ya pediste un enlace hace poco. Esperá unos minutos y volvé a intentar.',
                \Illuminate\Support\Facades\Password::INVALID_USER    => 'No encontramos una cuenta con ese correo.',
                default => 'No pudimos enviar el enlace. Intentá de nuevo.',
            }]);
    })->name('password.email');

    // Restablecimiento efectivo de la contraseña
    Route::post('/reset-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Ingresá la nueva contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password),
                ])->save();
                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect('/login')->with('message', 'Tu contraseña fue restablecida correctamente. Ingresá con tu nueva clave.')->with('typealert', 'success')
            : back()->withErrors(['email' => match ($status) {
                \Illuminate\Support\Facades\Password::INVALID_TOKEN => 'El enlace no es válido o ya expiró. Pedí uno nuevo desde "¿Olvidaste la clave?".',
                \Illuminate\Support\Facades\Password::INVALID_USER  => 'No encontramos una cuenta con ese correo.',
                default => 'No pudimos restablecer la contraseña. Intentá de nuevo.',
            }]);
    })->name('password.update');
});

Route::get('/email/verify', function (\Illuminate\Http\Request $request) {
    return view('auth.verify', ['email' => $request->query('email', '')]);
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Http\Request $request) {
    $user = \App\User::find($request->route('id'));
    if (!$user) {
        abort(404);
    }
    if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
        abort(403);
    }
    if ($user->hasVerifiedEmail()) {
        return redirect('/login')->with('message', 'Su correo ya estÃ¡ verificado.')->with('typealert', 'success');
    }
    $user->markEmailAsVerified();
    return redirect('/login')->with('message', 'Email verificado correctamente')->with('typealert', 'success');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
    $email = Auth::check() ? Auth::user()->email : $request->input('email');
    if (!$email) {
        return back()->withErrors(['email' => 'Email is required']);
    }
    $user = \App\User::where('email', $email)->first();
    if ($user) {
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
    return redirect('/email/verify?email=' . urlencode($email))->with('resent', true);
})->middleware(['throttle:6,1'])->name('verification.send');


//Route::get('/home', 'HomeController@index')->name('home')->middleware('auth');

/**RUTAS DEL USUARIO PRINCIPAL*/
Route::resource('admin/role', 'RoleController')->names('role')->middleware(['auth','verified']);
Route::resource('admin/user', 'UserController')->names('user')->middleware(['auth','verified']);
Route::post('newuser', 'UserController@postRegister')->name('register')->middleware(['auth','verified']);
Route::post('updatepasssword', 'UserController@updatepassword')->name('updatepasssword')->middleware(['auth','verified']);
Route::get('delete-users/{id}', 'UserController@delete_user')->name('delete-users')->middleware(['auth','verified']);
Route::post('/downrol', 'RoleController@downRol')->name('downrol')->middleware(['auth','verified']);
//Route::post('savedevolucionproduct', 'Devolucion\DevolucionventaController@store')->name('savedevolucionproduct');

/**RUTAS DE CATEGORIA */
Route::get('almacen/categoria', 'Categoria\CategoriaController@index')->name('categoria')->middleware(['auth','verified']);
Route::post('/savecategoria', 'Categoria\CategoriaController@store')->name('savecategoria')->middleware(['auth','verified']);
Route::get('showcategoria', 'Categoria\CategoriaController@show')->name('showcategoria')->middleware(['auth','verified']);
Route::post('/deletecategoria', 'Categoria\CategoriaController@destroy')->name('deletecategoria')->middleware(['auth','verified']);
Route::get('categoria-list/{id}', 'Categoria\CategoriaController@edit')->name('categoria-list')->middleware(['auth','verified']);
Route::post('categoriaupdate', 'Categoria\CategoriaController@update')->name('categoriaupdate')->middleware(['auth','verified']);
//Route::get('product-list', 'Categoria\CategoriaController@show');

/** RUTAS DE MARCA */
Route::get('almacen/marca', 'Marca\MarcaController@index')->name('marca')->middleware(['auth','verified']);
Route::post('/savemarca', 'Marca\MarcaController@store')->name('savemarca')->middleware(['auth','verified']);
Route::get('showmarca', 'Marca\MarcaController@show')->name('showmarca')->middleware(['auth','verified']);
Route::post('/deletemarca', 'Marca\MarcaController@destroy')->name('deletemarca')->middleware(['auth','verified']);
Route::get('marca-list/{id}', 'Marca\MarcaController@edit')->name('marca-list')->middleware(['auth','verified']);
Route::post('marcaupdate', 'Marca\MarcaController@update')->name('marcaupdate')->middleware(['auth','verified']);

/** RUTAS DE UNIDAD */
Route::get('almacen/unidad', 'Unidad\UnidadController@index')->name('unidad')->middleware(['auth','verified']);
Route::post('/saveunidad', 'Unidad\UnidadController@store')->name('saveunidad')->middleware(['auth','verified']);
Route::get('showunidad', 'Unidad\UnidadController@show')->name('showunidad')->middleware(['auth','verified']);
Route::post('/deleteunidad', 'Unidad\UnidadController@destroy')->name('deleteunidad')->middleware(['auth','verified']);
Route::get('unidad-list/{id}', 'Unidad\UnidadController@edit')->name('unidad-list')->middleware(['auth','verified']);
Route::post('unidadupdate', 'Unidad\UnidadController@update')->name('unidadupdate')->middleware(['auth','verified']);

/**RUTAS PARA EL ARTICULO*/
Route::get('almacen/articulo', 'Articulo\ArticuloController@index')->name('articulo')->middleware(['auth','verified']);
Route::post('savearticulo', 'Articulo\ArticuloController@store')->name('savearticulo')->middleware(['auth','verified']);
Route::get('showproducto', 'Articulo\ArticuloController@show')->name('showproducto')->middleware(['auth','verified']);
Route::get('product-list/{id}', 'Articulo\ArticuloController@edit')->name('product-list')->middleware(['auth','verified']);
Route::post('updateproduct', 'Articulo\ArticuloController@update')->name('updateproduct')->middleware(['auth','verified']);
Route::post('/delete-product', 'Articulo\ArticuloController@destroy')->name('delete-product')->middleware(['auth','verified']);
Route::post('/send-articulo-excel', 'Articulo\ArticuloController@get_data_excel')->name('send-articulo-excel')->middleware(['auth','verified']);
Route::post('/save_upload_products', 'Articulo\ArticuloController@save_products')->name('save_upload_products')->middleware(['auth','verified']);
Route::post('/updateStock', 'Articulo\ArticuloController@update_stock')->name('updateStock')->middleware(['auth','verified']);
Route::get('/excelarticulo', [ArticuloController::class, 'exportArticulo'])->name('excelexportarticulo')->middleware(['auth','verified']);
Route::get('variantes-list/{id}/{idproducto}', [ArticuloController::class, 'get_variantes'])->name('variantes_list')->middleware(['auth','verified']);
Route::get('almacen/formproduct', [ArticuloController::class, 'create'])->name('create_product')->middleware(['auth','verified']);
Route::get('articulo/{id}/edit', [ArticuloController::class, 'edit_product'])->name('articulo.edit')->middleware(['auth','verified']);
Route::get('articulos/load-options/categoria', [ArticuloController::class, 'loadOptionsCategoria']);
Route::get('articulos/load-options/marca', [ArticuloController::class, 'loadOptionsMarca']);


/** RUTAS DE SUCURSAL */
Route::get('sucursal', [SucursalController::class, 'index'])
    ->name('sucursal')
    ->middleware(['auth','verified']);

Route::get('sucursal/list', [SucursalController::class, 'list'])
    ->name('sucursal.list')
    ->middleware(['auth','verified']);

Route::post('sucursal/store', [SucursalController::class, 'store'])
    ->name('sucursal.store')
    ->middleware(['auth','verified']);

Route::get('sucursal/show/{id}', [SucursalController::class, 'show'])
    ->name('sucursal.show')
    ->middleware(['auth','verified']);

Route::post('sucursal/update', [SucursalController::class, 'update'])
    ->name('sucursal.update')
    ->middleware(['auth','verified']);

Route::post('sucursal/delete', [SucursalController::class, 'destroy'])
    ->name('sucursal.delete')
    ->middleware(['auth','verified']);

Route::post('sucursal/activar', [SucursalController::class, 'activar'])
    ->name('sucursal.activar')
    ->middleware(['auth','verified']);

/** STOCK POR SUCURSAL */
Route::get('sucursal/{id}/stock', [SucursalArticuloController::class, 'vistaStock'])
    ->name('sucursal.stock')
    ->middleware(['auth','verified']);

Route::get('sucursal/{id}/articulos', [SucursalArticuloController::class, 'index'])
    ->name('sucursal.articulos')
    ->middleware(['auth','verified']);

/** ArtÃ­culos simples */
Route::post('sucursal-articulo/store', [SucursalArticuloController::class, 'store'])
    ->name('sucursal.articulo.store')
    ->middleware(['auth','verified']);

Route::post('sucursal-articulo/update-stock', [SucursalArticuloController::class, 'updateStock'])
    ->name('sucursal.articulo.updateStock')
    ->middleware(['auth','verified']);

Route::post('sucursal-articulo/update-ubicacion', [SucursalArticuloController::class, 'updateUbicacion'])
    ->name('sucursal.articulo.updateUbicacion')
    ->middleware(['auth','verified']);

Route::post('sucursal-articulo/update-stock-minimo', [SucursalArticuloController::class, 'updateStockMinimo'])
    ->name('sucursal.articulo.updateStockMinimo')
    ->middleware(['auth','verified']);

Route::post('sucursal-articulo/delete', [SucursalArticuloController::class, 'destroy'])
    ->name('sucursal.articulo.delete')
    ->middleware(['auth','verified']);

Route::post('sucursal-articulo/activar', [SucursalArticuloController::class, 'activar'])
    ->name('sucursal.articulo.activar')
    ->middleware(['auth','verified']);

/** Combinaciones personalizadas */
Route::post('sucursal-combinacion/store', [SucursalCombinacionController::class, 'store'])
    ->name('sucursal.combinacion.store')
    ->middleware(['auth','verified']);

Route::post('sucursal-combinacion/update-stock', [SucursalCombinacionController::class, 'updateStock'])
    ->name('sucursal.combinacion.updateStock')
    ->middleware(['auth','verified']);

Route::post('sucursal-combinacion/update-ubicacion', [SucursalCombinacionController::class, 'updateUbicacion'])
    ->name('sucursal.combinacion.updateUbicacion')
    ->middleware(['auth','verified']);

Route::post('sucursal-combinacion/delete', [SucursalCombinacionController::class, 'destroy'])
    ->name('sucursal.combinacion.delete')
    ->middleware(['auth','verified']);

Route::post('sucursal-combinacion/activar', [SucursalCombinacionController::class, 'activar'])
    ->name('sucursal.combinacion.activar')
    ->middleware(['auth','verified']);

/** Listado de artÃ­culos y combinaciones */
Route::get('articulos/listar', [ArticuloController::class, 'listar'])
    ->name('articulos.listar')
    ->middleware(['auth','verified']);

// Productos con stock general en todas las sucursales
Route::get('productos-con-stock', [StockController::class, 'productosConStock'])
    ->name('productos.con.stock')
    ->middleware(['auth','verified']);

Route::middleware(['auth','verified'])->group(function () {
    Route::post('stock/transferir', [StockController::class, 'transferirStock'])
        ->name('stock.transferir');
});

// Stock por producto en todas las sucursales
Route::get('stock/producto/{productoId}/sucursales', [StockController::class, 'getSucursalesConStockProducto'])
    ->name('stock.producto.sucursales')
    ->middleware(['auth','verified']);

Route::get('stock/orden/{orderId}/productos-sucursales', [StockController::class, 'getProductosOrdenConSucursales'])
    ->name('stock.orden.productos.sucursales')
    ->middleware(['auth','verified']);

// Endpoints genÃ©ricos para sucursal
Route::get('sucursal/{id}/cuentas-abiertas', [SucursalController::class, 'cuentasDisponibles'])
    ->name('sucursal.cuentas.abiertas')
    ->middleware(['auth','verified']);

Route::get('sucursal/{id}/articulos-disponibles', [SucursalController::class, 'articulosDisponibles'])
    ->name('sucursal.articulos.disponibles')
    ->middleware(['auth','verified']);

Route::get('cuentas-abiertas', [SucursalController::class, 'cuentasDisponiblesGlobal'])
    ->name('cuentas.abiertas.global')
    ->middleware(['auth','verified']);


/**BULK UPLOAD ROUTES FOR PRODUCTS*/
Route::get('almacen/bulk-upload', [ArticuloController::class, 'showBulkUpload'])->name('bulk_upload')->middleware(['auth','verified']);
Route::get('almacen/download-template', [ArticuloController::class, 'downloadTemplate'])->name('download_template')->middleware(['auth','verified']);
Route::post('almacen/process-bulk-upload', [ArticuloController::class, 'processBulkUpload'])->name('process_bulk_upload')->middleware(['auth','verified']);
Route::get('almacen/get-categories', [ArticuloController::class, 'getCategories'])->name('get_categories')->middleware(['auth','verified']);
Route::get('almacen/get-product-types', [ArticuloController::class, 'getProductTypes'])->name('get_product_types')->middleware(['auth','verified']);
Route::get('almacen/get-brands', [ArticuloController::class, 'getBrands'])->name('get_brands')->middleware(['auth','verified']);
Route::get('almacen/get-locations', [ArticuloController::class, 'getLocations'])->name('get_locations')->middleware(['auth','verified']);
/** RUTAS QUICK CREATE */
Route::post('almacen/quick-create-category', [ArticuloController::class, 'quickCreateCategory'])->name('quick_create_category')->middleware(['auth','verified']);
Route::post('almacen/quick-create-marca', [ArticuloController::class, 'quickCreateMarca'])->name('quick_create_marca')->middleware(['auth','verified']);
Route::post('almacen/quick-create-unidad', [ArticuloController::class, 'quickCreateUnidad'])->name('quick_create_unidad')->middleware(['auth','verified']);
/****EDICION DE PRECIOS MASIVO*****/
Route::get('articulo/editprice', [EditpriceController::class, 'index'])->name('editarticulo.index')->middleware(['auth','verified']);
Route::get('get_product_category/{id}', [EditpriceController::class, 'show'])->name('editarticulo.category')->middleware(['auth','verified']);

/**RUTAS PARA EL PROVEEDOR*/
Route::get('compras/proveedor', 'Proveedor\ProveedorController@index')->name('proveedor')->middleware(['auth','verified']);
Route::post('saveproveedor', 'Proveedor\ProveedorController@store')->name('saveproveedor')->middleware(['auth','verified']);
Route::get('showproveedor', 'Proveedor\ProveedorController@show')->name('showproveedor')->middleware(['auth','verified']);
Route::get('provider-list/{id}', 'Proveedor\ProveedorController@edit')->name('provider-list')->middleware(['auth','verified']);
Route::post('updateprovider', 'Proveedor\ProveedorController@update')->name('updateprovider')->middleware(['auth','verified']);
Route::get('delete-provider/{id}', 'Proveedor\ProveedorController@destroy')->name('delete-provider')->middleware(['auth','verified']);
Route::post('quick-create-supplier', 'Proveedor\ProveedorController@quickCreateSupplier')->name('quick_create_supplier')->middleware(['auth','verified']);
/**RUTAS DE LOS INGRESOS COMPRAS DE LOS PRODUCTOS */
Route::resource('compras/entradas', 'Ingreso\IngresoController')->names('entradas')->middleware(['auth','verified']);
Route::post('nombrearticuloentrada', 'Ingreso\IngresoController@find_nombre')->name('nombrearticuloentrada')->middleware(['auth','verified']);
Route::post('temp_datos', 'Ingreso\IngresoController@save_temp')->name('temp_datos')->middleware(['auth','verified']);
Route::post('showproveedores', 'Ingreso\IngresoController@searh_proveedores')->name('showproveedores')->middleware(['auth','verified']);
// Route::post('', 'Ingreso\IngresoController@')->name('deleteproduct');
Route::post('deleteproduct', 'Ingreso\IngresoController@delete_prod')->name('deleteproduct')->middleware(['auth','verified']);
Route::post('showproductostemp', 'Ingreso\IngresoController@show_prod')->name('showproductostemp')->middleware(['auth','verified']);
Route::post('saveproductoentrada', 'Ingreso\IngresoController@store')->name('saveproductoentrada')->middleware(['auth','verified']);
Route::get('showlistentradas', 'Ingreso\IngresoController@show')->name('showlistentradas')->middleware(['auth','verified']);

Route::get('get-entrada/{id}', 'Ingreso\IngresoController@get_products')->name('get-entrada')->middleware(['auth','verified']);

// Route::get('registro', 'Ingreso\IngresoController@create')->name('entradasproductos');

/** RUTAS DE LOS CLIENTES */
Route::middleware(['auth','verified'])->group(function () {
    // Vista principal de clientes
    Route::get('clientes', [ClienteController::class, 'index'])
        ->name('cliente');
    // Guardar nuevo cliente
    Route::post('savecliente', [ClienteController::class, 'store'])
        ->name('savecliente');
    // Listado de clientes
    Route::get('showlistcustomers', [ClienteController::class, 'show'])
        ->name('showlistcustomers');
    // Obtener datos de un cliente especÃ­fico
    Route::get('get-data-cliente/{id}', [ClienteController::class, 'get_cliente'])
        ->name('get-data-cliente');
    // Actualizar cliente
    Route::post('updatecliente', [ClienteController::class, 'update'])
        ->name('updatecliente');
    // Dar de baja cliente
    Route::get('down-cliente/{id}', [ClienteController::class, 'down_cliente'])
        ->name('down-cliente');
    // Buscar cliente
    Route::post('findcustomer', [ClienteController::class, 'findCustomer'])
        ->name('findcustomer');
    // CreaciÃ³n rÃ¡pida de cliente
    Route::post('quick-create-customer', [ClienteController::class, 'quickCreateCustomer'])
        ->name('quick_create_customer');
});


/** RUTAS DEL MODULO DE VENTAS */
Route::prefix('ventas')->middleware(['auth','verified'])->group(function () {
    Route::get('/', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/list', [VentaController::class, 'list'])->name('ventas.list');
    Route::get('/kpis', [VentaController::class, 'kpis'])->name('ventas.kpis');
    Route::get('/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/store', [VentaController::class, 'store'])->name('ventas.store');
    Route::post('/{idventa}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::get('/{idventa}/detail', [VentaController::class, 'detail'])->name('ventas.detail');
    Route::get('/{idventa}/pendiente', [VentaController::class, 'pendiente'])->name('ventas.pendiente');
    Route::post('/{idventa}/registrar-pago', [VentaController::class, 'registrarPago'])->name('ventas.registrarPago');
});

/** DEVOLUCIONES — rutas web con sesión (reemplaza las de api.php que usaban auth:api) */
Route::prefix('devoluciones')->middleware(['auth','verified'])->group(function () {
    Route::post('/anular-venta/{idventa}',   [DevolucionController::class, 'anularVenta'])->name('devoluciones.anularVenta');
    Route::post('/anular-compra/{idcompra}', [DevolucionController::class, 'anularCompra'])->name('devoluciones.anularCompra');
    Route::post('/anular-pedido/{orderId}',  [DevolucionController::class, 'anularPedido'])->name('devoluciones.anularPedido');
});

/** GENERADOR DE PEDIDOS DE COMPRA (borradores que luego se convierten en compras).
 *  Se define ANTES del grupo compras para que 'compras/pedidos' no matchee {idcompra}. */
Route::prefix('compras/pedidos')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'index'])->name('pedidos-compra.index');
    Route::get('/list', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'list'])->name('pedidos-compra.list');
    Route::get('/create', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'create'])->name('pedidos-compra.create');
    Route::post('/store', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'store'])->name('pedidos-compra.store');
    Route::get('/{id}/edit', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'edit'])->name('pedidos-compra.edit');
    Route::post('/{id}/update', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'update'])->name('pedidos-compra.update');
    Route::get('/{id}/detail', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'detail'])->name('pedidos-compra.detail');
    Route::get('/{id}/pdf', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'pdf'])->name('pedidos-compra.pdf');
    Route::post('/{id}/anular', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'anular'])->name('pedidos-compra.anular');
    Route::post('/{id}/convertir', [\App\Http\Controllers\Compra\PedidoCompraController::class, 'convertir'])->name('pedidos-compra.convertir');
});

/** RUTAS DEL MODULO DE COMPRAS */
Route::prefix('compras')->group(function () {
    Route::get('/', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/list', [CompraController::class, 'list'])->name('compras.list');
    Route::get('/create', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/store', [CompraController::class, 'store'])->name('compras.store');
    Route::post('/ocr-upload', [CompraController::class, 'ocrUpload'])->name('compras.ocr-upload');
    Route::post('/{idcompra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    Route::get('/{idcompra}/detail', [CompraController::class, 'detail'])->name('compras.detail');
    Route::get('/{idcompra}/pendiente', [CompraController::class, 'pendiente'])->name('compras.pendiente');
    Route::post('/{idcompra}/registrar-pago', [CompraController::class, 'registrarPago'])->name('compras.registrarPago');
});

Route::prefix('devoluciones')->group(function () {
    // Vista principal del mÃ³dulo de devoluciones
    Route::get('/', [DevolucionController::class, 'index'])->name('devoluciones.index');
    // Endpoint para DataTables (listado de devoluciones)
    Route::get('/list', [DevolucionController::class, 'list'])->name('devoluciones.list');
    // Buscar compra o venta por folio
    Route::get('/buscar-folio', [DevolucionController::class, 'buscarFolio'])->name('devoluciones.buscarFolio');
    // Detalles de las devoluciones
    Route::get('/detalle/{iddevolucion}', [DevolucionController::class, 'detalle'])
    ->name('devoluciones.detalle');
});

/**DEVOLUCIONES DE UNA VENTA*/
Route::resource('devoluciones/venta', 'Devolucion\DevolucionventaController')->names('devolucion')->middleware(['auth','verified']);
Route::get('/products_devolucion/{folio}', 'Devolucion\DevolucionventaController@show_devolucion_venta')->name('products_devolucion')->middleware(['auth','verified']);
Route::post('/savedevolucionproduct', 'Devolucion\DevolucionventaController@store')->name('savedevolucionproduct')->middleware(['auth','verified']);

/* Rutas Gestor de Caja */
Route::get('caja/gestor', [CajaController::class, 'index'])
    ->name('caja.listado')
    ->middleware(['auth','verified']);

Route::get('caja/{caja}/historial/data', [CajaAperturaController::class, 'historialData'])
    ->name('caja.historial.data');

Route::post('caja/crear', [CajaController::class, 'store'])
    ->name('caja.store')
    ->middleware(['auth','verified']);

Route::post('caja/{caja}/actualizar', [CajaController::class, 'update'])
    ->name('caja.actualizar')
    ->middleware(['auth','verified']);

Route::post('caja/{caja}/desactivar', [CajaController::class, 'desactivar'])
    ->name('caja.desactivar')
    ->middleware(['auth','verified']);

Route::post('caja/{caja}/activar', [CajaController::class, 'activar'])
    ->name('caja.activar')
    ->middleware(['auth','verified']);

Route::get('caja/list', [CajaController::class, 'list'])->name('caja.list');

Route::prefix('cuentas')->middleware(['auth','verified'])->group(function () {
    // Gestor de cuentas
    Route::get('gestor', [CuentasController::class, 'index'])
        ->name('cuentas.index');
    // Historial de aperturas (solo aplica si la cuenta es tipo caja)
    Route::get('{cuenta}/historial/data', [CajaAperturaController::class, 'historialData'])
        ->name('cuentas.historial.data');
    // Crear nueva cuenta
    Route::post('crear', [CuentasController::class, 'store'])
        ->name('cuentas.store');
    // Actualizar cuenta
    Route::post('{cuenta}/actualizar', [CuentasController::class, 'update'])
        ->name('cuentas.actualizar');
    // Desactivar cuenta
    Route::post('{cuenta}/desactivar', [CuentasController::class, 'desactivar'])
        ->name('cuentas.desactivar');
    // Activar cuenta
    Route::post('{cuenta}/activar', [CuentasController::class, 'activar'])
        ->name('cuentas.activar');
    // Listado para datatables
    Route::get('list', [CuentasController::class, 'list'])
        ->name('cuentas.list');
});

Route::prefix('cuentas')->middleware(['auth','verified'])->group(function () {
    // Historial de aperturas (solo aplica si la cuenta es tipo caja)
    Route::get('{cuenta}/historial', [CajaAperturaController::class, 'historial'])
        ->name('cuentas.historial');

    // Apertura de cuenta tipo caja
    Route::post('{cuenta}/abrir', [CajaAperturaController::class, 'abrir'])
        ->name('cuentas.abrir');

    // Cierre de cuenta tipo caja
    Route::post('{cuenta}/cerrar', [CajaAperturaController::class, 'cerrar'])
        ->name('cuentas.cerrar');

    // Listado de cuentas abiertas (solo cajas abiertas)
    Route::get('abiertas', [CajaAperturaController::class, 'abiertas'])
        ->name('cuentas.abiertas');

    // Resumen de apertura de caja
    Route::get('apertura/{apertura}/resumen', [CajaAperturaController::class, 'resumen'])
        ->name('cuentas.apertura.resumen');
});


Route::prefix('cuentas')->middleware(['auth','verified'])->group(function () {
    // Data para DataTables
    Route::get('{cuenta}/movimientos/data', [MovimientoController::class, 'data'])
        ->name('cuentas.movimientos.data');

    // Vista de movimientos
    Route::get('{cuenta}/movimientos', [MovimientoController::class, 'index'])
        ->name('cuentas.movimientos.index');

    // Guardar movimiento
    Route::post('{cuenta}/movimientos', [MovimientoController::class, 'store'])
        ->name('cuentas.movimientos.store');

    // Nueva ruta para transferencias
    Route::post('transferencias', [MovimientoController::class, 'transferir'])
        ->name('cuentas.movimientos.transferir');

    // Detalle de un movimiento (resuelve tipo de comprobante)
    Route::get('{cuenta}/movimientos/{movimiento}/detalle', [MovimientoController::class, 'detalle'])
        ->name('cuentas.movimientos.detalle');
});

/* PRESUPUESTO */
// Endpoint para DataTable
Route::get('/presupuestos-list', [PresupuestoController::class, 'list'])->name('presupuestos.list');

// Endpoint para detalle vÃ­a AJAX
Route::get('/presupuestos/{idpresupuesto}/detail', [PresupuestoController::class, 'detail'])->name('presupuestos.detail');

// CRUD completo
Route::resource('presupuestos', PresupuestoController::class);

Route::get('/presupuestos/{idpresupuesto}/pdf', [PresupuestoController::class, 'generatePdf']) ->name('presupuestos.pdf');

Route::post('/presupuestos/{idpresupuesto}/estado', [PresupuestoController::class, 'changeState'])
->name('presupuestos.changeState');

/**RUTAS DE CAJA INICIO*/
Route::get('caja/cajainicio', [CajainicioController::class, 'index'])->name('cajainicio')->middleware(['auth','verified']);
Route::post('/saveapertura', [CajainicioController::class, 'store'])->name('saveapertura')->middleware(['auth','verified']);

/**HISTORY CAJA */
Route::get('caja/historial', [HistoricocajaController::class, 'index'])->name('cajahistorico')->middleware(['auth','verified']);
Route::post('caja/showlista', [HistoricocajaController::class, 'store'])->name('cajashowlista')->middleware(['auth','verified']);
Route::get('caja/showHistoryDetalle/{id}', [HistoricocajaController::class, 'show'])->name('cajashowDetalle')->middleware(['auth','verified']);

/**RUTAS DE CORTE DE CAJA*/
Route::get('caja/corte', [CortecajaController::class, 'index'])->name('corte')->middleware(['auth','verified']);
Route::post('/savecortecaja', [CortecajaController::class, 'store'])->name('savecortecaja')->middleware(['auth','verified']);
Route::get('showlistcortes', [CortecajaController::class, 'show'])->name('showlistcortes')->middleware(['auth','verified']);

Route::get('caja/corteparcial', [CorteparcialController::class, 'index'])->name('corteparcial')->middleware(['auth','verified']);
Route::get('showlistcajeros', [CorteparcialController::class, 'show'])->name('showlistcajeros')->middleware(['auth','verified']);
Route::post('/saveformparcial', [CorteparcialController::class, 'store'])->name('saveformparcial')->middleware(['auth','verified']);

/**TICKET FOR THE CUTTING OF THE CASHIER'S DAY*/
Route::get('/ticketcorte', [CortecajaController::class, 'ticket'])->name('ticketcorte')->middleware(['auth','verified']);

/**REPORTS*/
Route::get('/graph', [GraphicsController::class, 'index'])->name('graph')->middleware(['auth','verified']);
Route::get('/graph/movimientos', [GraphicsController::class, 'movimientos'])->name('graph.movimientos')->middleware(['auth','verified']);
Route::post('/getdatagraph', [GraphicsController::class, 'get_data'])->name('getdatagraph')->middleware(['auth','verified']);
Route::post('/getmesgraph', [GraphicsController::class, 'get_data_mes'])->name('getmesgraph')->middleware(['auth','verified']);

/** Chat interno de Reportes (analista IA) */
Route::prefix('reportes/chat')->middleware(['auth', 'verified'])->name('reportes.chat.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Reportes\ReportesChatController::class, 'index'])->name('index');
    Route::post('/sesion', [\App\Http\Controllers\Reportes\ReportesChatController::class, 'crearSesion'])->name('crear-sesion');
    Route::get('/sesion/{sesionId}', [\App\Http\Controllers\Reportes\ReportesChatController::class, 'historial'])->name('historial');
    Route::post('/sesion/{sesionId}/enviar', [\App\Http\Controllers\Reportes\ReportesChatController::class, 'enviar'])->name('enviar');
});


/******QUOTES****** */
Route::get('/quote', [QuotesController::class, 'index'])->name('quote.index')->middleware(['auth','verified']);
Route::get('quote/create', [QuotesController::class, 'create'])->name('quote')->middleware(['auth','verified']);
Route::post('nombrearticuloquote', [QuotesController::class, 'find_nombre'])->name('quote.nombrearticuloquote')->middleware(['auth','verified']);
Route::post('quote/saveProdTemp', [QuotesController::class, 'saveProdTemp'])->name('quote.saveProdTemp')->middleware(['auth','verified']);
Route::post('quote/updateProdTemp', [QuotesController::class, 'updateProdTemp'])->name('quote.updateProdTemp')->middleware(['auth','verified']);
Route::post('quote/downProdTemp', [QuotesController::class, 'downProdTemp'])->name('quote.downProdTemp')->middleware(['auth','verified']);
Route::post('quote/store', [QuotesController::class, 'store'])->name('quote.store')->middleware(['auth','verified']);
Route::get('/quote/print/{id}', [QuotesController::class, 'generatePdf'])->name('printpdf')->middleware(['auth','verified']);
Route::post('/quote/cancel', [QuotesController::class, 'cancelQuote'])->name('quote.cancel')->middleware(['auth','verified']);
Route::get('showlistquote', [QuotesController::class, 'show'])->name('quote.showlist')->middleware(['auth','verified']);
Route::get('/quote/detail/{id}', [QuotesController::class, 'getDetail'])->name('quote.detail')->middleware(['auth','verified']);

/**PRICE LISTS MODULE*/
Route::get('almacen/pricelists', [PriceListController::class, 'index'])->name('pricelists.index')->middleware(['auth','verified']);
Route::get('almacen/pricelists/create', [PriceListController::class, 'create'])->name('pricelists.create')->middleware(['auth','verified']);
Route::post('almacen/pricelists', [PriceListController::class, 'store'])->name('pricelists.store')->middleware(['auth','verified']);
Route::get('almacen/pricelists/{id}/edit', [PriceListController::class, 'edit'])->name('pricelists.edit')->middleware(['auth','verified']);
Route::put('almacen/pricelists/{id}', [PriceListController::class, 'update'])->name('pricelists.update')->middleware(['auth','verified']);
Route::delete('almacen/pricelists/{id}', [PriceListController::class, 'destroy'])->name('pricelists.destroy')->middleware(['auth','verified']);
// API helpers
Route::get('price-lists/options', [PriceListController::class, 'getLists'])->middleware(['auth','verified']);
Route::get('price-lists/sales', [PriceListController::class, 'getSalesLists'])->middleware(['auth','verified']);
Route::get('price-lists/purchase', [PriceListController::class, 'getPurchaseLists'])->middleware(['auth','verified']);
Route::post('price-lists/bulk-attach', [PriceListController::class, 'bulkAttachProducts'])->middleware(['auth','verified']);
Route::delete('price-lists/items/{id}', [PriceListController::class, 'removeItem'])->middleware(['auth','verified']);

Route::get('price-lists/load-options/{type}', [PriceListController::class, 'loadOptions'])->middleware(['auth','verified']);

/**INVENTORY */
Route::get('/inventory', [InventarioController::class, 'index'])->name('inventory');//->middleware('auth');
Route::get('/pdfinventario', [InventarioController::class, 'store'])->name('inventariopdf')->middleware(['auth','verified']);
Route::get('/pdfbarcodeproduc', [InventarioController::class, 'generateBarcodeProducts'])->name('barcodeProductpdf')->middleware(['auth','verified']);

/*ROUTE FOR SEND EMAIL */
Route::post('/contact', [TicketController::class, 'sendEmail'])->name('contact')->middleware(['auth','verified']);

/**ROUTE FOR CONFIGURATION*/
Route::get('/config', [ConfiguracionController::class, 'index'])->name('config')->middleware(['auth','verified']);
Route::post('/saveconf', [ConfiguracionController::class, 'update'])->name('saveconf')->middleware(['auth','verified']);

/**RUTAS DE VARIACIONES*/
Route::get('/almacen/variacion', [VariacionesController ::class, 'index'])->name('variacion.index')->middleware(['auth','verified']);
Route::post('/savevariacion', [VariacionesController::class, 'store'])->name('variacion.store')->middleware(['auth','verified']);
Route::get('/showvariacion', [VariacionesController::class, 'show'])->name('variacion.show')->middleware(['auth','verified']);
Route::get('/variacion-list/{id}', [VariacionesController::class, 'edit'])->name('variacion.edit')->middleware(['auth','verified']);
Route::post('/deletevariacion', [VariacionesController::class, 'destroy'])->name('variacion.destroy')->middleware(['auth','verified']);

/******RUTAS DE PRODUCTO INTEGRACION VARIANTE******* */
Route::post('/saveproductvariante', [ProductointegracionvarianteController::class, 'store'])->name('productsavevariante.store')->middleware(['auth','verified']);

/******RUTAS DE PRODUCTO VARIANTE******* */
Route::post('/productvariantesave', [ProductovarianteController::class, 'store'])->name('productvariante.store')->middleware(['auth','verified']);
Route::post('/productvarianteEdit', [ProductovarianteController::class, 'updateRow'])->name('productvariante.edit')->middleware(['auth','verified']);
Route::post('/productvarianteDown', [ProductovarianteController::class, 'destroy'])->name('productvariante.destroy')->middleware(['auth','verified']);
Route::post('/productvarianteUpdate', [ProductovarianteController::class, 'update'])->name('productvariante.update')->middleware(['auth','verified']);
Route::post('/productvarianteAddMoreStock', [ProductovarianteController::class, 'addMoreStockVariant'])->name('productvariante.addMoreStockVariant')->middleware(['auth','verified']);

/***************ORDER***************** */
Route::get('/orders/order', [OrderController::class, 'index'])->name('order.index')->middleware(['auth','verified']);
Route::get('/orders/new-count', [OrderController::class, 'newCount'])->name('order.newcount')->middleware(['auth','verified']);
Route::get('/orders/manual', [OrderController::class, 'createManual'])->name('order.manual')->middleware(['auth','verified']);

/** CUENTA CORRIENTE DE CLIENTES */
Route::get('/cc', [\App\Http\Controllers\Cuentas\CuentaCorrienteController::class, 'index'])->name('cc.index')->middleware(['auth','verified']);
Route::get('/cc/cliente/{id}', [\App\Http\Controllers\Cuentas\CuentaCorrienteController::class, 'cliente'])->name('cc.cliente')->middleware(['auth','verified']);
Route::post('/cc/cliente/{id}/movimiento', [\App\Http\Controllers\Cuentas\CuentaCorrienteController::class, 'storeMovimiento'])->name('cc.movimiento')->middleware(['auth','verified']);

/** REVENDEDORES (panel interno: acá se gestiona todo y se liquidan comisiones) */
Route::get('/revendedores-panel', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'index'])->name('rev.index')->middleware(['auth','verified']);
Route::get('/revendedores-panel/{id}', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'show'])->name('rev.show')->middleware(['auth','verified']);
Route::post('/revendedores-panel/{id}/actualizar', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'update'])->name('rev.update')->middleware(['auth','verified']);
Route::post('/revendedores-panel/{id}/liquidar', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'liquidar'])->name('rev.liquidar')->middleware(['auth','verified']);
Route::post('/revendedores-panel/comision/{id}/estado', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'estadoComision'])->name('rev.comision.estado')->middleware(['auth','verified']);
Route::get('/revendedores-panel/{id}/qr', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'qr'])->name('rev.qr')->middleware(['auth','verified']);
Route::get('/revendedores-export/comisiones', [\App\Http\Controllers\Revendedores\RevendedorController::class, 'exportComisiones'])->name('rev.export')->middleware(['auth','verified']);

/** PROCESOS DE TRABAJO (documentación visual de flujos) */
Route::get('/procesos', function () {
    return view('documentation.procesos');
})->name('procesos')->middleware(['auth','verified']);

/** BASE DE CONOCIMIENTO DE PRODUCTOS (interna, para el bot del CRM y el Estudio) */
Route::get('/articulo/{id}/conocimiento', [\App\Http\Controllers\Articulo\ConocimientoController::class, 'index'])->name('articulo.conocimiento')->middleware(['auth','verified']);
Route::post('/articulo/{id}/conocimiento', [\App\Http\Controllers\Articulo\ConocimientoController::class, 'store'])->name('articulo.conocimiento.store')->middleware(['auth','verified']);
Route::delete('/articulo/conocimiento/{itemId}', [\App\Http\Controllers\Articulo\ConocimientoController::class, 'destroy'])->name('articulo.conocimiento.destroy')->middleware(['auth','verified']);

/** COMPROBANTES DE PAGO DE VENTAS */
Route::post('/ventas/{idventa}/comprobante', [\App\Http\Controllers\Venta\VentaController::class, 'subirComprobante'])->name('ventas.comprobante')->middleware(['auth','verified']);
Route::delete('/ventas/comprobante/{compId}', [\App\Http\Controllers\Venta\VentaController::class, 'eliminarComprobante'])->name('ventas.comprobante.eliminar')->middleware(['auth','verified']);

/** CENTRO DE NOTIFICACIONES */
Route::get('/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])->name('notificaciones.index')->middleware(['auth','verified']);
Route::get('/notificaciones/feed', [\App\Http\Controllers\NotificacionController::class, 'feed'])->name('notificaciones.feed')->middleware(['auth','verified']);
Route::get('/notificaciones/{id}/ir', [\App\Http\Controllers\NotificacionController::class, 'ir'])->name('notificaciones.ir')->middleware(['auth','verified']);
Route::post('/notificaciones/leidas', [\App\Http\Controllers\NotificacionController::class, 'marcarLeidas'])->name('notificaciones.leidas')->middleware(['auth','verified']);

/** BUSCADOR GLOBAL por DNI/CUIT (clientes, proveedores, revendedores) */
Route::get('/buscar-persona', [\App\Http\Controllers\BuscadorGlobalController::class, 'buscar'])->name('buscar.persona')->middleware(['auth','verified']);

/** FICHAS FINANCIERAS (cliente y proveedor: historial + cuenta corriente) */
Route::get('/clientes/{id}/ficha', [\App\Http\Controllers\Cliente\ClienteController::class, 'ficha'])->name('clientes.ficha')->middleware(['auth','verified']);
Route::get('/proveedores/{id}/ficha', [\App\Http\Controllers\Proveedor\ProveedorController::class, 'ficha'])->name('proveedores.ficha')->middleware(['auth','verified']);

/** LEGALES (públicas — las exige Meta para publicar la app) */
Route::view('/politica-de-privacidad', 'legal.privacidad')->name('legal.privacidad');

/** INTEGRACIONES (claves de API del sistema) */
Route::get('/admin/integraciones', [\App\Http\Controllers\Configuracion\IntegracionController::class, 'index'])->name('integraciones.index')->middleware(['auth','verified']);
Route::post('/admin/integraciones', [\App\Http\Controllers\Configuracion\IntegracionController::class, 'guardar'])->name('integraciones.guardar')->middleware(['auth','verified']);

/** MÓDULO ENVÍOS (tablero por etapas) */
Route::get('/envios', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'index'])->name('envios.board')->middleware(['auth','verified']);
Route::post('/envios/asignar', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'asignar'])->name('envios.asignar')->middleware(['auth','verified']);
Route::post('/envios/{id}/etapa', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'etapa'])->name('envios.etapa')->middleware(['auth','verified']);
Route::get('/envios/pending-count', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'pendingCount'])->name('envios.pending-count')->middleware(['auth','verified']);
Route::get('/envios/ruta', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'hojaRuta'])->name('envios.ruta')->middleware(['auth','verified']);

Route::get('/envios/mapa', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'mapa'])->name('envios.mapa')->middleware(['auth','verified']);
Route::get('/envios/mapa-data', [\App\Http\Controllers\Envios\EnvioBoardController::class, 'mapaData'])->name('envios.mapa-data')->middleware(['auth','verified']);
Route::get('/envios/reporte-pdf', [\App\Http\Controllers\Envios\EnvioReporteController::class, 'pdf'])->name('envios.reporte-pdf')->middleware(['auth','verified']);

/** PORTAL DEL FLETERO (acceso por token, mobile) */
Route::get('/fletero/{token}', [\App\Http\Controllers\Envios\FleteroPortalController::class, 'portal'])->name('fletero.portal')->middleware('throttle:60,1');
Route::post('/fletero/{token}/entrega/{envioId}', [\App\Http\Controllers\Envios\FleteroPortalController::class, 'confirmarEntrega'])->name('fletero.entrega')->middleware('throttle:30,1');
Route::get('/etiqueta/pedido/{id}', [\App\Http\Controllers\Envios\EtiquetaController::class, 'pedido'])->name('etiqueta.pedido')->middleware(['auth','verified']);
Route::get('/etiqueta/venta/{id}', [\App\Http\Controllers\Envios\EtiquetaController::class, 'venta'])->name('etiqueta.venta')->middleware(['auth','verified']);

/** ESTUDIO DE PUBLICACIONES */
Route::get('/publicaciones', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'index'])->name('publicaciones.index')->middleware(['auth','verified']);
Route::post('/publicaciones/registrar', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'registrar'])->name('publicaciones.registrar')->middleware(['auth','verified']);
Route::post('/publicaciones/generar-copy', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'generarCopy'])->name('publicaciones.generar-copy')->middleware(['auth','verified']);
Route::post('/publicaciones/generar-imagen', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'generarImagen'])->name('publicaciones.generar-imagen')->middleware(['auth','verified']);
Route::post('/publicaciones/guardar', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'guardar'])->name('publicaciones.guardar')->middleware(['auth','verified']);
Route::post('/publicaciones/publicar', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'publicar'])->name('publicaciones.publicar')->middleware(['auth','verified']);
Route::get('/publicaciones/catalogo', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'catalogoPdf'])->name('publicaciones.catalogo')->middleware(['auth','verified']);
Route::post('/publicaciones/generar-video', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'generarVideo'])->name('publicaciones.generar-video')->middleware(['auth','verified']);
Route::post('/publicaciones/ajustes', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'guardarAjustes'])->name('publicaciones.ajustes')->middleware(['auth','verified']);
Route::post('/publicaciones/recursos', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'guardarRecurso'])->name('publicaciones.recursos')->middleware(['auth','verified']);
Route::delete('/publicaciones/recursos/{id}', [\App\Http\Controllers\Publicaciones\PublicacionController::class, 'eliminarRecurso'])->name('publicaciones.recursos.eliminar')->middleware(['auth','verified']);
Route::post('/orders/manual', [OrderController::class, 'storeManual'])->name('order.manual.post')->middleware(['auth','verified']);
Route::post('/orders/order/{id}/entrega', [OrderController::class, 'setEntrega'])->name('order.entrega')->middleware(['auth','verified']);
Route::post('/orders/order/{id}/comprobante', [OrderController::class, 'subirComprobante'])->name('order.comprobante')->middleware(['auth','verified']);
Route::delete('/orders/comprobante/{compId}', [OrderController::class, 'eliminarComprobante'])->name('order.comprobante.eliminar')->middleware(['auth','verified']);
Route::get('/orders/order/{id}/pagos', [OrderController::class, 'pagos'])->name('order.pagos')->middleware(['auth','verified']);
Route::post('/orders/order/{id}/registrar-pago', [OrderController::class, 'registrarPago'])->name('order.registrarpago')->middleware(['auth','verified']);
Route::get('/showorders', [OrderController::class, 'show'])->name('order.show')->middleware(['auth','verified']);
Route::get('orders/order/{id}', [OrderController::class, 'show_order'])->name('order.edit')->middleware(['auth','verified']);
Route::post('/updateStatusOrder', [OrderController::class, 'update_status'])->name('order.status')->middleware(['auth','verified']);
Route::post('/updatePaidStatus', [OrderController::class, 'update_paid'])->name('order.updatePaidStatus')->middleware(['auth','verified']);

/****************************************/

/**RUTAS DE COLOR*/
Route::get('/almacen/color', [ColorController::class, 'index'])->name('color.index')->middleware('auth');
Route::post('/savecolor', [ColorController::class, 'store'])->name('color.store')->middleware('auth');
Route::get('/showcolor', [ColorController::class, 'show'])->name('color.show')->middleware('auth');
Route::get('/color-list/{id}', [ColorController::class, 'edit'])->name('color.edit')->middleware('auth');
Route::post('/deletecolor', [ColorController::class, 'destroy'])->name('color.destroy')->middleware('auth');

// Price list API for items
Route::get('price-lists/{id}/items', [PriceListController::class, 'getListItems'])->middleware(['auth','verified']);
Route::get('/price-lists/applicable/{idarticulo}', [PriceListController::class, 'applicableToArticulo']);

/*****RUTAS PARA EL BANNER PRINCIPAL DEL ECOMMERCE*******/
Route::get('/banner', [BannerController::class, 'index'])->name('banner.index')->middleware(['auth','verified']);
Route::post('/savebanner', [BannerController::class, 'store'])->name('banner.store')->middleware(['auth','verified']);
Route::get('/showbanner', [BannerController::class, 'show'])->name('banner.show')->middleware(['auth','verified']);
Route::post('/getByIdbanner', [BannerController::class, 'edit'])->name('banner.edit')->middleware(['auth','verified']);
Route::post('/deleteByIdbanner', [BannerController::class, 'destroy'])->name('banner.destroy')->middleware(['auth','verified']);

/**ZONAS DE ENVIO (checkout ecommerce) */
Route::get('/zonas-envio', [\App\Http\Controllers\Configuracion\ZonaEnvioController::class, 'index'])->name('zonas_envio.index')->middleware(['auth','verified']);
Route::get('/showzonasenvio', [\App\Http\Controllers\Configuracion\ZonaEnvioController::class, 'show'])->name('zonas_envio.show')->middleware(['auth','verified']);
Route::post('/savezonaenvio', [\App\Http\Controllers\Configuracion\ZonaEnvioController::class, 'store'])->name('zonas_envio.store')->middleware(['auth','verified']);
Route::post('/deletezonaenvio', [\App\Http\Controllers\Configuracion\ZonaEnvioController::class, 'destroy'])->name('zonas_envio.destroy')->middleware(['auth','verified']);

/******* RUTAS PARA LAS VENTAS DE ORDENES QUE VIENEN DEL PAGOS POR ECOMMERCE********************* */
Route::get('/ventas/ecommerce', [VentaorderecommerceController::class, 'index'])->name('ventasorderecomm.index')->middleware(['auth','verified']);
Route::get('showventasecommerce', [VentaorderecommerceController::class, 'show'])->name('ventasorderecomm.show')->middleware(['auth','verified']);
Route::get('saleEcommerce/{id}', [VentaorderecommerceController::class, 'edit'])->name('ventasorderecomm.edit')->middleware(['auth','verified']);

        /**CHATBOT AND DOCUMENTATION ROUTES*/
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index')->middleware(['auth','verified']);
    Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])->name('chatbot.send')->middleware(['auth','verified']);
    Route::post('/chatbot/rate', [ChatbotController::class, 'rateResponse'])->name('chatbot.rate')->middleware(['auth','verified']);
    Route::get('/chatbot/history', [ChatbotController::class, 'getChatHistory'])->name('chatbot.history')->middleware(['auth','verified']);
    Route::post('/chatbot/clear', [ChatbotController::class, 'clearChat'])->name('chatbot.clear')->middleware(['auth','verified']);
    Route::get('/chatbot/welcome', [ChatbotController::class, 'getWelcomeMessage'])->name('chatbot.welcome')->middleware(['auth','verified']);
    Route::get('/chatbot/categories', [ChatbotController::class, 'getCategories'])->name('chatbot.categories')->middleware(['auth','verified']);

/**DOCUMENTATION ROUTES*/
Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index')->middleware(['auth','verified']);
Route::get('/documentation/{slug}', [DocumentationController::class, 'show'])->name('documentation.show')->middleware(['auth','verified']);
Route::get('/documentation/category/{category}', [DocumentationController::class, 'category'])->name('documentation.category')->middleware(['auth','verified']);
Route::get('/documentation/search', [DocumentationController::class, 'search'])->name('documentation.search')->middleware(['auth','verified']);
Route::get('/documentation/categories', [DocumentationController::class, 'categories'])->name('documentation.categories')->middleware(['auth','verified']);

/**TENANT TRAINING VIDEO ROUTES*/
Route::get('/training-videos', [TrainingVideoController::class, 'index'])->name('tenant.training-videos.index')->middleware(['auth','verified']);
Route::get('/training-videos/{id}', [TrainingVideoController::class, 'show'])->name('tenant.training-videos.show')->middleware(['auth','verified']);
Route::get('/training-videos/module/{module}', [TrainingVideoController::class, 'getVideosByModule'])->name('tenant.training-videos.by-module')->middleware(['auth','verified']);

/**ADMIN DOCUMENTATION ROUTES*/
Route::get('/admin/documentation', [DocumentationController::class, 'adminIndex'])->name('documentation.admin.index')->middleware(['auth','verified','Isadmin']);
Route::get('/admin/documentation/create', [DocumentationController::class, 'create'])->name('documentation.admin.create')->middleware(['auth','verified','Isadmin']);
Route::post('/admin/documentation', [DocumentationController::class, 'store'])->name('documentation.admin.store')->middleware(['auth','verified','Isadmin']);
Route::get('/admin/documentation/{id}/edit', [DocumentationController::class, 'edit'])->name('documentation.admin.edit')->middleware(['auth','verified','Isadmin']);
Route::put('/admin/documentation/{id}', [DocumentationController::class, 'update'])->name('documentation.admin.update')->middleware(['auth','verified','Isadmin']);
Route::delete('/admin/documentation/{id}', [DocumentationController::class, 'destroy'])->name('documentation.admin.destroy')->middleware(['auth','verified','Isadmin']);

/** WHATSAPP / CRM / AGENTES IA */
require __DIR__ . '/whatsapp.php';

/** FINANZAS (gastos, fletes, CxP, tablero) */
if (file_exists(__DIR__ . '/finanzas.php')) {
    require __DIR__ . '/finanzas.php';
}
