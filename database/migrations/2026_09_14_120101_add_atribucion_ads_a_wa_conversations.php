<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda el referral de click-to-WhatsApp/Messenger/Instagram (Meta Ads) en el
 * momento en que nace la conversacion, para poder atribuir despues el pedido
 * que salga de ahi a la campana/anuncio de origen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('wa_conversations', 'ctwa_clid')) {
                $table->string('ctwa_clid', 255)->nullable();
            }
            if (!Schema::hasColumn('wa_conversations', 'meta_ad_id')) {
                $table->string('meta_ad_id', 60)->nullable();
            }
            if (!Schema::hasColumn('wa_conversations', 'referral_source_url')) {
                $table->string('referral_source_url', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            foreach (['ctwa_clid', 'meta_ad_id', 'referral_source_url'] as $col) {
                if (Schema::hasColumn('wa_conversations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
