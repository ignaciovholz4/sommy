<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extracciones de comprobantes de compra hechas con IA (Gemini vision):
     * queda el resultado crudo auditado aunque el usuario no confirme la compra.
     */
    public function up(): void
    {
        Schema::create('compra_ocr_extracciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('archivo_path');
            $table->string('mime', 100)->nullable();
            $table->string('proveedor_extraido')->nullable();
            $table->unsignedBigInteger('proveedor_id_matched')->nullable();
            $table->date('fecha_extraida')->nullable();
            $table->string('num_folio_extraido')->nullable();
            $table->string('tipo_comprobante_sugerido')->nullable();
            $table->json('items_json')->nullable();
            $table->float('confianza')->nullable();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id_matched')->references('idproveedor')->on('proveedores')->nullOnDelete();
            $table->foreign('compra_id')->references('idcompra')->on('compras')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_ocr_extracciones');
    }
};
