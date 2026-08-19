<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite taguear que plantilla aprobada de Meta corresponde a cada nivel
 * de cobranza (cobranza_suave, cobranza_firme, cobranza_urgente), sin
 * necesidad de un deploy para cambiar el mapeo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_templates', function (Blueprint $table) {
            $table->string('uso')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('wa_templates', function (Blueprint $table) {
            $table->dropColumn('uso');
        });
    }
};
