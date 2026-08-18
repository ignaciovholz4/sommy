<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('nombre');
            $table->string('meta_title', 70)->nullable()->after('tela');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('nombre');
        });

        // Backfill de slugs: Str::slug(nombre), con sufijo -{id} si colisiona
        foreach (DB::table('productos')->get(['idarticulo', 'nombre']) as $p) {
            $slug = Str::slug($p->nombre) ?: 'producto';
            if (DB::table('productos')->where('slug', $slug)->exists()) {
                $slug .= '-' . $p->idarticulo;
            }
            DB::table('productos')->where('idarticulo', $p->idarticulo)->update(['slug' => $slug]);
        }

        foreach (DB::table('categorias')->get(['idcategoria', 'nombre']) as $c) {
            $slug = Str::slug($c->nombre) ?: 'categoria';
            if (DB::table('categorias')->where('slug', $slug)->exists()) {
                $slug .= '-' . $c->idcategoria;
            }
            DB::table('categorias')->where('idcategoria', $c->idcategoria)->update(['slug' => $slug]);
        }
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'meta_title', 'meta_description']);
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
