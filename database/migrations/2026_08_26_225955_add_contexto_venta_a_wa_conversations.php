<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Memoria" de la conversación: medida, tipo de colchón, producto que le
     * interesó y etapa comercial, para que el bot no vuelva a preguntar algo
     * que el cliente ya contó.
     */
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->json('contexto_venta')->nullable()->after('mode');
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->dropColumn('contexto_venta');
        });
    }
};
