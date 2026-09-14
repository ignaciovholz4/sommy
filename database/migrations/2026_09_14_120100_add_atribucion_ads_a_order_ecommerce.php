<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atribucion de origen (Meta Ads) para poder cruzar ventas reales contra
 * campanas: fbclid/UTM capturados en el checkout del ecommerce, y ctwa_clid
 * propagado desde la conversacion de WhatsApp cuando el pedido nace ahi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (!Schema::hasColumn('order_ecommerce', 'utm_source')) {
                $table->string('utm_source', 60)->nullable()->after('origen');
            }
            if (!Schema::hasColumn('order_ecommerce', 'utm_medium')) {
                $table->string('utm_medium', 60)->nullable()->after('utm_source');
            }
            if (!Schema::hasColumn('order_ecommerce', 'utm_campaign')) {
                $table->string('utm_campaign', 150)->nullable()->after('utm_medium');
            }
            if (!Schema::hasColumn('order_ecommerce', 'fbclid')) {
                $table->string('fbclid', 255)->nullable()->after('utm_campaign');
            }
            if (!Schema::hasColumn('order_ecommerce', 'meta_ad_id')) {
                $table->string('meta_ad_id', 60)->nullable()->after('fbclid');
            }
            if (!Schema::hasColumn('order_ecommerce', 'ctwa_clid')) {
                $table->string('ctwa_clid', 255)->nullable()->after('meta_ad_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            foreach (['utm_source', 'utm_medium', 'utm_campaign', 'fbclid', 'meta_ad_id', 'ctwa_clid'] as $col) {
                if (Schema::hasColumn('order_ecommerce', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
