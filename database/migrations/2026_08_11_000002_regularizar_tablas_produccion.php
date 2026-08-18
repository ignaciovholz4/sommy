<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Regulariza estructuras que hasta ahora solo existian en
 * actualizacion_produccion.sql (aplicadas a mano en produccion).
 * Todos los cambios llevan guardas para no chocar con bases que ya los tienen.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('categorias') && !Schema::hasColumn('categorias', 'orden')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->integer('orden')->default(99);
            });
        }

        if (Schema::hasTable('order_ecommerce')) {
            Schema::table('order_ecommerce', function (Blueprint $table) {
                if (!Schema::hasColumn('order_ecommerce', 'origen')) {
                    $table->string('origen', 20)->default('tienda');
                }
                if (!Schema::hasColumn('order_ecommerce', 'fecha_entrega')) {
                    $table->date('fecha_entrega')->nullable();
                }
            });
        }

        if (!Schema::hasTable('cliente_cc_movimientos')) {
            Schema::create('cliente_cc_movimientos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cliente_id')->index('idx_cliente');
                $table->enum('tipo', ['cargo', 'pago']);
                $table->string('concepto', 200);
                $table->decimal('monto', 14, 2);
                $table->string('medio_pago', 30)->nullable();
                $table->string('referencia', 60)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('arrepentimientos')) {
            Schema::create('arrepentimientos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 120);
                $table->string('email', 150);
                $table->string('telefono', 40)->nullable();
                $table->string('pedido', 40)->nullable();
                $table->text('motivo')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('publicaciones_registro')) {
            Schema::create('publicaciones_registro', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('producto_id')->index('idx_prod');
                $table->string('canal', 20);
                $table->string('formato', 20)->default('post');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // No se revierten columnas/tablas que produccion ya usa.
    }
};
