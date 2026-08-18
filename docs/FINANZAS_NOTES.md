# Módulo Finanzas — notas de integración

Módulo de gastos, envíos/fletes, cuentas por pagar (CxP) y tablero financiero.
Este archivo documenta lo que el proceso principal tiene que enganchar (rutas, menú, scheduler)
porque esos archivos no se tocaron desde esta rama de trabajo.

## 1. Include de rutas (routes/tenant-routes.php)

Todas las rutas del módulo viven en `routes/finanzas.php` y ya traen su propio grupo
`Route::middleware(['auth','verified'])->prefix('finanzas')->name('finanzas.')`.
Solo hay que incluir el archivo al final de `routes/tenant-routes.php`:

```php
// Módulo Finanzas (gastos, envíos, CxP, tablero)
require __DIR__ . '/finanzas.php';
```

URLs resultantes: `/finanzas` (tablero), `/finanzas/gastos`, `/finanzas/envios`,
`/finanzas/transportistas`, `/finanzas/cxp`, `/finanzas/cxp/{proveedor}`.

## 2. Menú lateral (resources/views/layouts/aside.blade.php)

Entradas para pegar dentro del `<div class="dg-nav-dropdown">` del ítem **Finanzas**
existente (el que hoy solo tiene "Gestor de Cuentas"), debajo del label "Cuentas"
o con un label propio:

```blade
<div class="dg-drop-label">Finanzas</div>
@can('haveaccess','finanzas.dashboard')
<a href="{{ url('finanzas') }}" class="dg-drop-item {{ $resp === 'finanzas' ? 'dg-drop-active' : '' }}">
    <i class="fas fa-chart-line"></i> Tablero
</a>
@endcan
@can('haveaccess','finanzas.gastos.index')
<a href="{{ url('finanzas/gastos') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/gastos') ? 'dg-drop-active' : '' }}">
    <i class="fas fa-receipt"></i> Gastos
</a>
@endcan
@can('haveaccess','finanzas.envios.index')
<a href="{{ url('finanzas/envios') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/envios') ? 'dg-drop-active' : '' }}">
    <i class="fas fa-shipping-fast"></i> Envíos y Fletes
</a>
@endcan
@can('haveaccess','finanzas.transportistas.manage')
<a href="{{ url('finanzas/transportistas') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/transportistas') ? 'dg-drop-active' : '' }}">
    <i class="fas fa-truck"></i> Transportistas
</a>
@endcan
@can('haveaccess','finanzas.cxp.index')
<a href="{{ url('finanzas/cxp') }}" class="dg-drop-item {{ Str::startsWith($resp,'finanzas/cxp') ? 'dg-drop-active' : '' }}">
    <i class="fas fa-file-invoice"></i> Cuentas por Pagar
</a>
@endcan
```

Nota: si el `$isCaja` del ítem Finanzas se calcula por prefijo de ruta, conviene sumarle
`Str::startsWith($resp, 'finanzas')` para que el dropdown quede marcado activo.

## 3. Scheduler (app/Console/Kernel.php)

Los comandos ya existen en `app/Console/Commands` (se autodescubren con `$this->load(__DIR__.'/Commands')`).
Solo falta agendarlos en el método `schedule()`:

```php
// Finanzas: gastos recurrentes y alertas de vencimientos de proveedores
$schedule->command('gastos:generar-recurrentes')->dailyAt('06:00');
$schedule->command('cxp:alertar-vencimientos')->dailyAt('08:00');
```

- `gastos:generar-recurrentes`: por cada gasto marcado recurrente con `proximo_vencimiento <= hoy`,
  clona un gasto pendiente con esa fecha y avanza el próximo vencimiento según la frecuencia
  (genera todos los períodos atrasados si hubo días sin correr).
- `cxp:alertar-vencimientos`: loguea un resumen de deudas vencidas / que vencen en 7 días y,
  si hay mail configurado, se lo manda a los usuarios con rol de acceso total (`full-access = yes`);
  si el mail falla, solo loguea un warning (no explota).

## 4. Decisiones de diseño y deuda técnica

- **Imputación FIFO sin tabla de imputaciones**: `ProveedorCcMovimiento::reimputarFifo($proveedorId)`
  recalcula el estado (pendiente/parcial/pagado) de todas las filas `debe` recorriéndolas por
  `fecha_vencimiento` asc (las sin vencimiento al final) y consumiendo el total del `haber`.
  Es idempotente y autocorrectiva; si más adelante se necesita saber exactamente qué pago canceló
  qué factura, habrá que agregar una tabla de imputaciones.
- **Compras a crédito**: `CompraController@store` acepta un parámetro opcional `a_credito` (checkbox).
  Si viene, genera la fila `debe` en CxP con vencimiento = fecha + `condicion_pago_dias` del proveedor.
  **El formulario de compras (`resources/views/compras/create.blade.php`) todavía no tiene el checkbox**:
  falta agregar `<input type="checkbox" name="a_credito" value="1">` al form de alta de compra.
- **Pago de compras**: `CompraController@registrarPago` ahora, además del flujo original, crea las
  filas `haber` en CxP (una por movimiento, capadas a la deuda restante de la compra) y reimputa FIFO.
  Todo dentro de un try/catch que loguea sin romper el pago si CxP falla.
- **Movimientos de tesorería**: los pagos de gastos y de CxP crean `Movimiento` egreso con
  `referencia_type`/`referencia_id` (polimórfico) apuntando a `App\Models\Gasto` o
  `App\Models\ProveedorCcMovimiento`. Las cuentas de pago usan el formato `caja-{aperturaId}` /
  `banco-{cuentaId}` y el endpoint global existente `GET /cuentas-abiertas`.
- **Fletes pagados por la empresa**: al crear un envío con `pagado_por=empresa` y costo > 0 se
  genera automáticamente un `Gasto` pendiente en la categoría "Fletes" (firstOrCreate). Al eliminar
  el envío, si ese gasto sigue pendiente se elimina también; si ya se pagó, el envío no se puede borrar.
- **Alta de envíos desde pedidos/compras**: el endpoint `POST /finanzas/envios` recibe
  `tipo` + `order_ecommerce_id`|`compra_id`, así que se puede llamar desde el detalle de un pedido
  o de una compra. Por ahora la UI de alta está en el index de envíos (modal con nro de pedido/compra);
  queda pendiente agregar un botón "Generar envío" en el show del pedido ecommerce.
- **Comprobantes de gastos**: se guardan en `storage/app/public/gastos` (requiere `php artisan storage:link`).
- **Permisos usados** (ya sembrados): `finanzas.dashboard`, `finanzas.gastos.index`,
  `finanzas.gastos.manage`, `finanzas.envios.index`, `finanzas.envios.manage`,
  `finanzas.transportistas.manage`, `finanzas.cxp.index`, `finanzas.cxp.manage`.
