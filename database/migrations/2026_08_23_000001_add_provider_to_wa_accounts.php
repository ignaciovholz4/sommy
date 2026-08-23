<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proveedor tecnico de una cuenta de WhatsApp: 'meta' (Cloud API oficial) o
 * 'baileys' (conexion no oficial via WhatsApp Web, sesion propia con QR).
 * Es independiente de `channel` (whatsapp/messenger/instagram): para Baileys
 * el channel sigue siendo 'whatsapp', solo cambia el mecanismo de envio/recepcion.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->enum('provider', ['meta', 'baileys'])->default('meta')->after('channel');
        });
    }

    public function down(): void
    {
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }
};
