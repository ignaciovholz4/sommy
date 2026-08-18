<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->bigIncrements('idpresupuesto');

            $table->foreignId('idcliente')
                ->constrained('clientes', 'idcliente')
                ->onDelete('cascade');

            $table->date('fecha');

            $table->string('num_folio', 20)->nullable()->unique();

            $table->enum('estado', ['borrador', 'confirmado', 'venta'])
                ->default('borrador');

            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('total_neto', 12, 2)->default(0);
            $table->decimal('total_con_iva', 12, 2)->default(0);

            $table->timestamps();
        });

        // Trigger para generar num_folio automáticamente
        DB::unprepared('
            CREATE TRIGGER trg_presupuestos_num_folio
            BEFORE INSERT ON presupuestos
            FOR EACH ROW
            BEGIN
                DECLARE next_id INT;

                SET next_id = (SELECT IFNULL(MAX(idpresupuesto), 0) + 1 FROM presupuestos);

                SET NEW.num_folio = CONCAT("PRES-", LPAD(next_id, 6, "0"));
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_presupuestos_num_folio');
        Schema::dropIfExists('presupuestos');
    }
};
