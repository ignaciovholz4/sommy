<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_ecommerce', function (Blueprint $table) {
            $table->string('mp_preference_id', 64)->nullable()->after('status_payment');
            $table->string('mp_payment_id', 32)->nullable()->after('mp_preference_id');
            $table->string('mp_status', 30)->nullable()->after('mp_payment_id');
            $table->dateTime('paid_at')->nullable()->after('mp_status');
        });

        foreach (['transferencia', 'mercadopago'] as $metodo) {
            if (!DB::table('payment_methods')->where('method_name', $metodo)->exists()) {
                DB::table('payment_methods')->insert(['method_name' => $metodo, 'status' => 1]);
            }
        }
    }

    public function down()
    {
        Schema::table('payment_ecommerce', function (Blueprint $table) {
            $table->dropColumn(['mp_preference_id', 'mp_payment_id', 'mp_status', 'paid_at']);
        });

        DB::table('payment_methods')->whereIn('method_name', ['transferencia', 'mercadopago'])->delete();
    }
};
