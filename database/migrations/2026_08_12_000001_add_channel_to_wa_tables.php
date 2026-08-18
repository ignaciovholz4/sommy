<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bandeja multicanal: ademas de WhatsApp, entran Messenger (Facebook) e Instagram.
 * - wa_accounts: cada fila es un "endpoint" de mensajeria (numero de WhatsApp,
 *   pagina de Facebook o cuenta de Instagram) con su propio token.
 * - wa_conversations: el identificador del contacto pasa a external_id
 *   (telefono en WA, PSID en Messenger, IGSID en Instagram).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->enum('channel', ['whatsapp', 'messenger', 'instagram'])->default('whatsapp')->after('nombre');
            $table->string('page_id')->nullable()->after('waba_id');       // pagina de FB (messenger e instagram)
            $table->string('ig_account_id')->nullable()->after('page_id'); // cuenta profesional de IG
        });

        // phone_number_id era obligatorio; en messenger/instagram no existe (sin doctrine/dbal: ALTER directo)
        DB::statement('ALTER TABLE wa_accounts MODIFY phone_number_id VARCHAR(255) NULL');

        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->enum('channel', ['whatsapp', 'messenger', 'instagram'])->default('whatsapp')->after('wa_account_id');
            $table->string('external_id')->nullable()->after('channel'); // wa_id | PSID | IGSID
        });

        DB::statement('ALTER TABLE wa_conversations MODIFY phone_e164 VARCHAR(20) NULL');

        DB::table('wa_conversations')->whereNull('external_id')->update([
            'external_id' => DB::raw('phone_e164'),
        ]);

        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->unique(['wa_account_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->dropUnique(['wa_account_id', 'external_id']);
            $table->dropColumn(['channel', 'external_id']);
        });
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->dropColumn(['channel', 'page_id', 'ig_account_id']);
        });
    }
};
