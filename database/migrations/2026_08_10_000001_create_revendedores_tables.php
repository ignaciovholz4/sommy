<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revendedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();          // el que viaja en el link: ?ref=CODIGO
            $table->string('nombre', 150);
            $table->string('email', 150)->unique();
            $table->string('telefono', 40)->nullable();
            $table->string('dni_cuit', 30)->nullable();
            $table->string('localidad', 120)->nullable();
            $table->string('provincia', 120)->nullable();
            $table->string('instagram', 120)->nullable();
            $table->text('como_vende')->nullable();          // dónde/cómo piensa vender
            $table->decimal('comision_porcentaje', 5, 2)->default(10.00);
            $table->string('cbu', 40)->nullable();
            $table->string('alias_cbu', 60)->nullable();
            $table->string('titular_cuenta', 150)->nullable();
            $table->enum('estado', ['pendiente', 'activo', 'suspendido'])->default('pendiente');
            $table->text('notas')->nullable();               // notas internas del administrador
            $table->unsignedInteger('visitas')->default(0);  // clicks sobre su link
            $table->timestamp('ultima_visita')->nullable();
            $table->timestamps();
        });

        Schema::create('revendedor_comisiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('revendedor_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('monto_venta', 12, 2)->default(0);   // base sobre la que se calcula
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->decimal('comision', 12, 2)->default(0);
            $table->enum('estado', ['pendiente', 'aprobada', 'pagada', 'anulada'])->default('pendiente');
            $table->unsignedBigInteger('pago_id')->nullable();   // lote de pago con el que se liquidó
            $table->timestamp('pagada_at')->nullable();
            $table->timestamps();

            $table->index('revendedor_id');
            $table->index('order_id');
            $table->index('estado');
        });

        Schema::create('revendedor_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('revendedor_id');
            $table->decimal('monto', 12, 2);
            $table->date('fecha');
            $table->string('medio', 40)->default('transferencia');
            $table->string('referencia', 120)->nullable();       // nro de comprobante
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable(); // quién liquidó
            $table->timestamps();

            $table->index('revendedor_id');
        });

        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (!Schema::hasColumn('order_ecommerce', 'revendedor_id')) {
                $table->unsignedBigInteger('revendedor_id')->nullable()->after('cliente_id');
                $table->index('revendedor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (Schema::hasColumn('order_ecommerce', 'revendedor_id')) {
                $table->dropColumn('revendedor_id');
            }
        });
        Schema::dropIfExists('revendedor_pagos');
        Schema::dropIfExists('revendedor_comisiones');
        Schema::dropIfExists('revendedores');
    }
};
