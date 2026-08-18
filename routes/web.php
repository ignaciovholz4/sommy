<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\Permission\Models\Role;
use App\Permission\Models\Permission;
//use Illuminate\Support\Facades\Gate;
/**use controller version 8 */
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
// use App\Http\Controllers\Caja\CajaAperturaController;
use App\Http\Controllers\Caja\CajaResumenController;
// use App\Http\Controllers\Caja\MovimientoController;

use App\Http\Controllers\Cuentas\CuentasController;
use App\Http\Controllers\Cuentas\CajaAperturaController;
    use App\Http\Controllers\Cuentas\MovimientoController;

use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\Venta\VentaController;
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
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\Tenant\TrainingVideoController;

/**IMPORT DATA FROM EMAILS */
use App\Http\Controllers\Email\TicketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth', 'Isadmin'])->get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Cache cleared successfully';
})->name('clear-cache');


Route::middleware(['web'])->group(base_path('routes/tenant-routes.php'));

// Auth::routes(['register' => false, 'reset' => true, 'verify' => true]);

//Route::get('/', function () {
//    return view('welcome');
//
//});
