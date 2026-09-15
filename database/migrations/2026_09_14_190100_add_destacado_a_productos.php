<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Control manual de que productos se muestran en "Productos destacados"
 * de la home (se tilda por producto desde el panel).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'destacado')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->boolean('destacado')->default(false)->after('estado')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('destacado');
        });
    }
};
