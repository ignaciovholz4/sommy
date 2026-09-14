<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Idempotencia del evento Purchase de la Conversions API: no reenviarlo si ya se mando. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (!Schema::hasColumn('order_ecommerce', 'purchase_capi_sent_at')) {
                $table->timestamp('purchase_capi_sent_at')->nullable()->after('ctwa_clid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (Schema::hasColumn('order_ecommerce', 'purchase_capi_sent_at')) {
                $table->dropColumn('purchase_capi_sent_at');
            }
        });
    }
};
