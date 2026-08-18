<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionTableSeeder extends Seeder
{
    public function run()
    {
        // Check if configuracion record exists before inserting
        if (!DB::table('configuracion')->where('id', 1)->exists()) {
            DB::table('configuracion')->insert([
                'id' => 1,
                'name' => 'Chemin',
                'image' => 'Diseño sin título (3).png',
                'adress' => 'Argentina',
                'email' => 'argentina@gmail.com',
                'phone' => '7937937957',
                'created_at' => '2023-02-18 18:19:32',
                'updated_at' => '2025-06-13 00:41:59',
            ]);
        }

        // Check if cliente record exists before inserting
        if (!DB::table('clientes')->where('idcliente', 1)->exists()) {
            DB::table('clientes')->insert([
                'idcliente' => 1,
                'nombre' => 'Publico general',
                'direccion' => 'Argentina',
                'telefono' => '2222332231',
                'email' => 'argentina@gmail.com',
                'estatus' => 'Activo',
                'number_exterior' => 103,
                'number_interior' => '122',
                'materno' => '',
                'paterno' => '',
            ]);
        }

        // Check if tipo_producto records exist before inserting
        if (!DB::table('tipo_producto')->where('id', 1)->exists()) {
            DB::table('tipo_producto')->insert([
                'id' => 1,
                'name' => 'Producto simple',
                'descripcion' => '',
                'status' => 1,
                'registration_date' => '2024-12-23 12:34:31',
            ]);
        }

        if (!DB::table('tipo_producto')->where('id', 2)->exists()) {
            DB::table('tipo_producto')->insert([
                'id' => 2,
                'name' => 'Producto personalizado',
                'descripcion' => '',
                'status' => 1,
                'registration_date' => '2024-12-23 12:34:45',
            ]);
        }

        // Check if categoria record exists before inserting
        if (!DB::table('categorias')->where('idcategoria', 1)->exists()) {
            DB::table('categorias')->insert([
                'idcategoria' => 1,
                'nombre' => 'Sabanas',
                'descripcion' => 'Sabanas',
                'status' => 1,
                'name_imagen' => 'SABANA1.jpg',
            ]);
        }

        // Check if unidad record exists before inserting
        if (!DB::table('unidades')->where('idunidad', 1)->exists()) {
            DB::table('unidades')->insert([
                'idunidad'     => 1,
                'nombre'       => 'Unidades',
                'nombre_corto' => 'unid',
                'decimal'      => false,
                'status'      => 1,
            ]);
        }

        // Seeder de IVA
        $ivas = [
            [
                'idiva'      => 1,
                'tipo_iva'   => 'No gravado',
                'value_iva'  => 0.0000,
                'value_arca' => '8',
            ],
            [
                'idiva'      => 2,
                'tipo_iva'   => 'Exento',
                'value_iva'  => 0.0000,
                'value_arca' => '7',
            ],
            [
                'idiva'      => 3,
                'tipo_iva'   => 'IVA 0%',
                'value_iva'  => 0.0000,
                'value_arca' => '6',
            ],
            [
                'idiva'      => 4,
                'tipo_iva'   => 'IVA 2,5%',
                'value_iva'  => 2.5000,
                'value_arca' => '5',
            ],
            [
                'idiva'      => 5,
                'tipo_iva'   => 'IVA 5%',
                'value_iva'  => 5.0000,
                'value_arca' => '4',
            ],
            [
                'idiva'      => 6,
                'tipo_iva'   => 'IVA 10,5%',
                'value_iva'  => 10.5000,
                'value_arca' => '3',
            ],
            [
                'idiva'      => 7,
                'tipo_iva'   => 'IVA 21%',
                'value_iva'  => 21.0000,
                'value_arca' => '2',
            ],
            [
                'idiva'      => 8,
                'tipo_iva'   => 'IVA 27%',
                'value_iva'  => 27.0000,
                'value_arca' => '1',
            ],
        ];

        foreach ($ivas as $iva) {
            if (!DB::table('iva')->where('idiva', $iva['idiva'])->exists()) {
                DB::table('iva')->insert($iva);
            }
        }

        // Check if marca record exists before inserting
        if (!DB::table('marcas')->where('idmarca', 1)->exists()) {
            DB::table('marcas')->insert([
                'idmarca'     => 1,
                'nombre'      => 'Essencial',
                'descripcion' => 'Marca Essencial de productos textiles',
                'status'      => 1,
            ]);
        }

        // Check if productos record exists before inserting
        if (!DB::table('productos')->where('idarticulo', 1)->exists()) {
            DB::table('productos')->insert([
                'idarticulo'          => 1,
                'categoria_id'        => 1,
                'codigo'              => '1234567891234',
                'nombre'              => 'Sabana Essencial Alcoyana',
                'descripcion'         => 'La sábana Essencial de Alcoyana combina suavidad, frescura y durabilidad en una pieza ideal para el descanso diario. Confeccionada con materiales de alta calidad, ofrece una textura suave al tacto y una excelente resistencia al uso y los lavados. Su diseño simple y elegante se adapta a cualquier estilo de habitación, brindando confort y funcionalidad para un sueño placentero. Perfecta para quienes buscan calidad y confort en cada detalle.',
                'imagen'              => 'SABANA1.jpg',
                'estado'              => 'Activo',
                'ubicacion'           => 'Depósito A1', // ejemplo de ubicación
                'tipo_producto_id'    => 1,
                'marca_id'            => 1, // referencia a tabla marcas
                'articulo_pesable_balanza' => false,
                'unidad_id'           => 1, // referencia a tabla unidades

                // Nuevos campos de precios e impuestos
                'iva_compra_id'       => 2, // referencia a IVA 21% en tabla iva
                'pcompra_sin_iva'     => 35000.00,
                'pcompra_con_iva'     => 42350.00, // ejemplo con IVA 21%
                'iva_venta_id'        => 2, // referencia a IVA 21% en tabla iva
                'pventa_sin_iva'      => 45000.00,
                'pventa_con_iva'      => 54450.00, // ejemplo con IVA 21%
                'descuento'           => 0.00,
            ]);
        }

        // Seeder de estados de orden ecommerce
        $statuses = [
            ['status_id' => 1, 'status_name' => 'Pendiente',  'active' => 1],
            ['status_id' => 2, 'status_name' => 'Pagado',     'active' => 1],
            ['status_id' => 3, 'status_name' => 'Enviado',    'active' => 1],
            ['status_id' => 4, 'status_name' => 'Entregado',  'active' => 1],
            ['status_id' => 5, 'status_name' => 'Cancelado',  'active' => 1],
        ];

        foreach ($statuses as $status) {
            if (!DB::table('status_orders_ecommerce')->where('status_id', $status['status_id'])->exists()) {
                DB::table('status_orders_ecommerce')->insert($status);
            }
        }

        // Inserta ARS y USD si no existen
        $monedas = [
            ['codigo' => 'ARS', 'nombre' => 'Pesos Argentinos', 'simbolo' => '$',   'decimales' => 2],
            ['codigo' => 'USD', 'nombre' => 'Dólares',          'simbolo' => 'US$', 'decimales' => 2],
        ];

        foreach ($monedas as $m) {
            if (!DB::table('monedas')->where('codigo', $m['codigo'])->exists()) {
                DB::table('monedas')->insert(array_merge($m, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

    }
}