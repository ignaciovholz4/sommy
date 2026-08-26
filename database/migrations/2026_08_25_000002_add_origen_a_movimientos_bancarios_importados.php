<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos_bancarios_importados', function (Blueprint $table) {
            $table->enum('origen', ['archivo', 'chytapay'])->default('archivo')->after('cuenta_id');
            $table->string('chytapay_payment_request_id')->nullable()->after('origen');
            $table->unique('chytapay_payment_request_id', 'mov_banc_imp_chytapay_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_bancarios_importados', function (Blueprint $table) {
            $table->dropUnique('mov_banc_imp_chytapay_id_unique');
            $table->dropColumn(['origen', 'chytapay_payment_request_id']);
        });
    }
};
