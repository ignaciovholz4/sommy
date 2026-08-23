<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Control fino de que productos ofrece el bot de ventas del CRM: el bot solo
 * habla de los articulos con bot_ofrecer=1 (se tilda por producto).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'bot_ofrecer')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->boolean('bot_ofrecer')->default(false)->after('estado')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('bot_ofrecer');
        });
    }
};
