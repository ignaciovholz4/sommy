<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_imagenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->string('path', 255); // relativo a public/
            $table->unsignedTinyInteger('orden')->default(0);
            $table->string('alt', 255)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();

            $table->foreign('producto_id')->references('idarticulo')->on('productos')->onDelete('cascade');
            $table->index('producto_id');
        });

        // Backfill desde el esquema anterior: imagen principal + carpeta física de galería por artículo
        $productos = DB::table('productos')->select('idarticulo', 'imagen')->get();

        foreach ($productos as $producto) {
            $orden = 0;

            if (!empty($producto->imagen) && $producto->imagen !== 'productogeneral.png') {
                DB::table('producto_imagenes')->insert([
                    'producto_id' => $producto->idarticulo,
                    'path'        => 'imagenes/articulos/' . $producto->imagen,
                    'orden'       => $orden++,
                    'principal'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            $carpeta = public_path('imagenes/images_ecommerce/articulo-' . $producto->idarticulo);
            if (File::isDirectory($carpeta)) {
                foreach (File::files($carpeta) as $archivo) {
                    DB::table('producto_imagenes')->insert([
                        'producto_id' => $producto->idarticulo,
                        'path'        => 'imagenes/images_ecommerce/articulo-' . $producto->idarticulo . '/' . $archivo->getFilename(),
                        'orden'       => $orden++,
                        'principal'   => false,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('producto_imagenes');
    }
};
