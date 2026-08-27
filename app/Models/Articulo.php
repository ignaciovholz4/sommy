<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Articulo extends Model
{
    protected $table = 'productos';

    protected $primaryKey = "idarticulo";

    public $timestamps = false;

    protected static function booted()
    {
        // Slug SEO: solo se genera si está vacío (no se regenera al renombrar, para no romper URLs indexadas)
        static::created(function ($articulo) {
            if (empty($articulo->slug)) {
                $slug = Str::slug($articulo->nombre) ?: 'producto';
                if (static::where('slug', $slug)->where('idarticulo', '!=', $articulo->idarticulo)->exists()) {
                    $slug .= '-' . $articulo->idarticulo;
                }
                $articulo->slug = $slug;
                $articulo->saveQuietly();
            }
        });
    }

    // Opciones fijas de ficha técnica de colchón (valor => etiqueta)
    public const TIPOS_COLCHON = [
        'bonell'        => 'Resortes bonell',
        'pocket'        => 'Resortes pocket',
        'espuma'        => 'Espuma alta densidad',
        'viscoelastico' => 'Viscoelástico',
        'hibrido'       => 'Híbrido',
    ];

    public const FIRMEZAS = [
        'suave' => 'Suave',
        'media' => 'Media',
        'firme' => 'Firme',
    ];

    public const PLAZAS = [
        '1'    => '1 plaza',
        '1.5'  => '1 plaza y media',
        '2'    => '2 plazas',
        '2.5'  => '2 plazas y media (Queen)',
        'king' => 'King',
    ];

    protected $fillable = [
        'categoria_id',
        'codigo',
        'nombre',
        'nombre_compra',
        'slug',
        'meta_title',
        'meta_description',
        'descripcion',
        'imagen',
        'estado',
        'bot_ofrecer',
        'ubicacion',
        'tipo_producto_id',
        'marca_id',
        'proveedor_id',
        'codigo_proveedor',
        'articulo_pesable_balanza',
        'unidad_id',

        // Referencias a la tabla iva
        'iva_compra_id',
        'pcompra_sin_iva',
        'pcompra_con_iva',
        'iva_venta_id',
        'pventa_sin_iva',
        'pventa_con_iva',
        'pventa_mayorista',
        'descuento',

        // Ficha técnica colchón
        'tipo_colchon',
        'firmeza',
        'altura_cm',
        'densidad_kg_m3',
        'plazas',
        'peso_max_kg',
        'garantia_anios',
        'noches_prueba',
        'certificaciones',
        'pillow_top',
        'tela',
    ];

    // 🔗 Relaciones

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    // Relación con IVA de compra
    public function ivaCompra()
    {
        return $this->belongsTo(Iva::class, 'iva_compra_id');
    }

    // Relación con IVA de venta
    public function ivaVenta()
    {
        return $this->belongsTo(Iva::class, 'iva_venta_id');
    }
    
    public function sucursales()
    {
        return $this->hasMany(SucursalArticulo::class, 'articulo_id');
    }

    public function combinaciones() {
        return $this->hasMany(ProductoCombinacion::class, 'producto_id', 'idarticulo');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'idproveedor');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class, 'producto_id', 'idarticulo')->orderBy('orden');
    }

    // Productos que se recomiendan junto a este (carrito, ficha de producto)
    public function relacionados()
    {
        return $this->belongsToMany(Articulo::class, 'producto_relacionados', 'idarticulo', 'relacionado_id');
    }
}