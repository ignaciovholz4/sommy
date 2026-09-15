<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El referral de click-to-WhatsApp/Messenger/Instagram (Meta Ads) trae ademas
 * el texto real del anuncio (headline/body de la creatividad) cuando el
 * cliente entra haciendo clic en un anuncio puntual: se guarda para que el
 * bot sepa de que anuncio vino y pueda ofrecer proactivamente lo que ese
 * anuncio promociona (ej. un combo), en vez de esperar a que el cliente lo
 * mencione. Antes solo se guardaba el id/url del anuncio para atribucion,
 * nunca el contenido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('wa_conversations', 'referral_headline')) {
                $table->string('referral_headline', 255)->nullable()->after('referral_source_url');
            }
            if (!Schema::hasColumn('wa_conversations', 'referral_body')) {
                $table->text('referral_body')->nullable()->after('referral_headline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            foreach (['referral_headline', 'referral_body'] as $col) {
                if (Schema::hasColumn('wa_conversations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
