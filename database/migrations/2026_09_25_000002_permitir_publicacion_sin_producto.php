<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El chat ahora puede generar contenido de marca sin estar atado a un
 * producto puntual (ej. una escena de estilo de vida, un tip de descanso).
 * producto_id pasa a ser opcional. Se usa SQL crudo (MODIFY) porque el
 * proyecto no tiene doctrine/dbal instalado para el ->change() de Schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE publicaciones MODIFY producto_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE publicaciones MODIFY producto_id BIGINT UNSIGNED NOT NULL');
    }
};
