<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El dueño no quiere precios de venta con centavos ni "sueltos" (ej.
 * $141.847,33): todo precio de venta al público tiene que quedar redondo en
 * miles, siempre redondeando PARA ARRIBA. Corrección única de lo que ya
 * había cargado — de acá en adelante App\Support\Precio::redondear() aplica
 * lo mismo en cada guardado desde el panel (ArticuloController) y en la
 * importación masiva (BulkProductImport).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE productos SET pventa_con_iva = CEIL(pventa_con_iva / 1000) * 1000 WHERE pventa_con_iva > 0');
        DB::statement('UPDATE producto_combinaciones SET pventa_variante = CEIL(pventa_variante / 1000) * 1000 WHERE pventa_variante > 0');
    }

    public function down(): void
    {
        // No hay forma de recuperar los precios exactos originales: el
        // redondeo hacia arriba no es reversible.
    }
};
