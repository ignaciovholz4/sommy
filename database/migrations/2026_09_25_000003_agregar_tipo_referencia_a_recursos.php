<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nuevo tipo de recurso "referencia": imágenes que el usuario sube para que
 * la IA imite su estilo visual (colores, luz, composición) al generar
 * escenas, sin copiar su contenido. Se envían a Gemini junto a la foto real
 * del producto en cada generación.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE publicaciones_recursos MODIFY tipo ENUM('imagen','logo','prompt','contexto','referencia') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE publicaciones_recursos MODIFY tipo ENUM('imagen','logo','prompt','contexto') NOT NULL");
    }
};
