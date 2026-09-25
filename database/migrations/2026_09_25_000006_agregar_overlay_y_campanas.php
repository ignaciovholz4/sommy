<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sommy Creative Studio: capas de texto comercial editables (headline, CTA,
 * badge, legal, etc. — no generadas por IA, capas reales) y Campaigns
 * básico para agrupar publicaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('publicaciones', 'overlay_json')) {
                $table->text('overlay_json')->nullable()->after('prompt_escena');
            }
            if (!Schema::hasColumn('publicaciones', 'campana_id')) {
                $table->unsignedBigInteger('campana_id')->nullable()->index()->after('padre_id');
            }
            if (!Schema::hasColumn('publicaciones', 'orden_feed')) {
                $table->integer('orden_feed')->nullable()->after('campana_id');
            }
        });

        if (!Schema::hasTable('publicaciones_campanas')) {
            Schema::create('publicaciones_campanas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 120);
                $table->string('objetivo', 40)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            foreach (['overlay_json', 'campana_id', 'orden_feed'] as $col) {
                if (Schema::hasColumn('publicaciones', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('publicaciones_campanas');
    }
};
