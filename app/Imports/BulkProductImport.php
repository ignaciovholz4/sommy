<?php

namespace App\Imports;

use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use App\Models\Unidad;
use App\Models\Iva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BulkProductImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
{
    private $errors = [];
    private $successCount = 0;
    private $errorCount = 0;
    private $slugsUsados = []; // los batch inserts no disparan eventos de modelo → slug se genera acá

    public function model(array $row)
    {
        // Validar campos requeridos
        if (empty($row['nombre']) || empty($row['categoria']) || empty($row['codigo']) ||
            empty($row['precio_compra_sin_iva']) || empty($row['precio_venta_sin_iva']) ||
            empty($row['iva_compra']) || empty($row['iva_venta']) || empty($row['tipo_producto'])) {
            
            $this->errorCount++;
            $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": faltan campos requeridos.";
            return null;
        }

        try {
            // Buscar o crear categoría
            $categoria = Categoria::firstOrCreate(
                ['nombre' => trim($row['categoria'])],
                ['descripcion' => 'Auto-creada desde importación', 'estatus' => 1]
            );

            // Validar tipo de producto (solo 1 o 2)
            $tipoProductoId = null;
            if (in_array($row['tipo_producto'], ['1','2'])) {
                $tipoProductoId = $row['tipo_producto'];
            } else {
                $this->errorCount++;
                $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": tipo_producto inválido (solo 1 = simple, 2 = personalizado).";
                return null;
            }

            // Buscar o crear marca
            $marcaId = null;
            if (!empty($row['marca'])) {
                $marca = Marca::firstOrCreate(
                    ['nombre' => trim($row['marca'])],
                    ['descripcion' => 'Auto-creada desde importación']
                );
                $marcaId = $marca->idmarca;
            }

            // Unidad por defecto (ejemplo: id = 1)
            $unidadId = 1;

            // IVA compra
            $ivaCompra = Iva::where('tipo_iva', trim($row['iva_compra']))->first(); 
            if (!$ivaCompra) {
                $this->errorCount++;
                $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": IVA de compra inválido.";
                return null;
            }

            // IVA venta
            $ivaVenta = Iva::where('tipo_iva', trim($row['iva_venta']))->first();
            if (!$ivaVenta) {
                $this->errorCount++;
                $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": IVA de venta inválido.";
                return null;
            }

            // Validar código único
            if (Articulo::where('codigo', trim($row['codigo']))->exists()) {
                $this->errorCount++;
                $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": código '{$row['codigo']}' ya existe.";
                return null;
            }

            // Validar descuento
            $descuento = 0;
            if (!empty($row['descuento'])) {
                $descuento = floatval($row['descuento']);
                if ($descuento < 0 || $descuento > 100) {
                    $this->errorCount++;
                    $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": descuento debe estar entre 0 y 100.";
                    return null;
                }
            }

            // Precios
            $pcompra_sin_iva = floatval($row['precio_compra_sin_iva']);
            $pventa_sin_iva = floatval($row['precio_venta_sin_iva']);
            $pcompra_con_iva = $pcompra_sin_iva * (1 + $ivaCompra->value_iva/100);
            $pventa_con_iva  = $pventa_sin_iva * (1 + $ivaVenta->value_iva/100);

            // Pesable
            $pesable = (!empty($row['pesable']) && strtolower(trim($row['pesable'])) === 'sí');

            // Proveedor (opcional, se crea si no existe — mismo patrón que marca)
            $proveedorId = null;
            if (!empty($row['proveedor'])) {
                $proveedor = Proveedor::firstOrCreate(
                    ['nombre' => trim($row['proveedor'])],
                    ['direccion' => '', 'telefono' => '', 'email' => '', 'estado' => 'Activo']
                );
                $proveedorId = $proveedor->idproveedor;
            }

            // Ficha técnica colchón (todos opcionales, con validación de opciones fijas)
            $fichaTecnica = $this->fichaTecnicaFromRow($row);
            if ($fichaTecnica === false) {
                return null; // el error ya quedó registrado
            }

            // Slug SEO (batch inserts no disparan el evento created del modelo)
            $slug = Str::slug(trim($row['nombre'])) ?: 'producto';
            if (in_array($slug, $this->slugsUsados) || Articulo::where('slug', $slug)->exists()) {
                $slug .= '-' . substr(uniqid(), -5);
            }
            $this->slugsUsados[] = $slug;

            $this->successCount++;

            return new Articulo(array_merge([
                'categoria_id' => $categoria->idcategoria,
                'codigo' => trim($row['codigo']),
                'nombre' => trim($row['nombre']),
                'slug' => $slug,
                'descripcion' => trim($row['descripcion'] ?? ''),
                'imagen' => '',
                'estado' => 'Activo',
                'tipo_producto_id' => $tipoProductoId,
                'marca_id' => $marcaId,
                'proveedor_id' => $proveedorId,
                'codigo_proveedor' => !empty($row['codigo_proveedor']) ? trim($row['codigo_proveedor']) : null,
                'unidad_id' => $unidadId,
                'iva_compra_id' => $ivaCompra->idiva,
                'pcompra_sin_iva' => $pcompra_sin_iva,
                'pcompra_con_iva' => $pcompra_con_iva,
                'iva_venta_id' => $ivaVenta->idiva,
                'pventa_sin_iva' => $pventa_sin_iva,
                'pventa_con_iva' => $pventa_con_iva,
                'descuento' => $descuento,
                'articulo_pesable_balanza' => $pesable,
            ], $fichaTecnica));

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": " . $e->getMessage();
            return null;
        }
    }

    /**
     * Ficha técnica de colchón desde la fila del Excel.
     * Devuelve array de campos (vacío si no hay datos) o false si hay un valor inválido.
     */
    private function fichaTecnicaFromRow(array $row)
    {
        $ficha = [];

        $opciones = [
            'tipo_colchon' => array_keys(Articulo::TIPOS_COLCHON),
            'firmeza'      => array_keys(Articulo::FIRMEZAS),
            'plazas'       => array_keys(Articulo::PLAZAS),
        ];

        foreach ($opciones as $campo => $validos) {
            if (!empty($row[$campo])) {
                $valor = strtolower(trim((string) $row[$campo]));
                if (!in_array($valor, $validos)) {
                    $this->errorCount++;
                    $this->errors[] = "Fila " . ($this->errorCount + $this->successCount) . ": {$campo} inválido '{$row[$campo]}' (opciones: " . implode(', ', $validos) . ").";
                    return false;
                }
                $ficha[$campo] = $valor;
            }
        }

        $numericos = ['altura_cm', 'densidad', 'peso_max_kg', 'garantia_anios', 'noches_prueba'];
        foreach ($numericos as $campo) {
            if (isset($row[$campo]) && $row[$campo] !== '' && $row[$campo] !== null) {
                $columna = $campo === 'densidad' ? 'densidad_kg_m3' : $campo;
                $ficha[$columna] = floatval($row[$campo]);
            }
        }

        if (!empty($row['certificaciones'])) {
            $ficha['certificaciones'] = trim((string) $row['certificaciones']);
        }
        if (!empty($row['tela'])) {
            $ficha['tela'] = trim((string) $row['tela']);
        }
        if (isset($row['pillow_top']) && $row['pillow_top'] !== '' && $row['pillow_top'] !== null) {
            $ficha['pillow_top'] = strtolower(trim((string) $row['pillow_top'])) === 'sí' ? 1 : 0;
        }

        return $ficha;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'categoria' => 'required|string|max:100',
            'codigo' => 'required|string|max:50',
            'precio_compra_sin_iva' => 'required|numeric|min:0',
            'precio_venta_sin_iva' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'iva_compra' => 'required|string',
            'iva_venta' => 'required|string',
            'tipo_producto' => 'required|in:1,2',
            'marca' => 'nullable|string|max:100',
            'pesable' => 'nullable|string|in:Sí,No',
            'proveedor' => 'nullable|string|max:200',
            'codigo_proveedor' => 'nullable|string|max:50',
            'altura_cm' => 'nullable|numeric|min:0',
            'densidad' => 'nullable|numeric|min:0',
            'peso_max_kg' => 'nullable|numeric|min:0',
            'garantia_anios' => 'nullable|numeric|min:0|max:50',
            'noches_prueba' => 'nullable|numeric|min:0|max:365',
            'certificaciones' => 'nullable|string|max:500',
            'tela' => 'nullable|string|max:100',
            'pillow_top' => 'nullable|string|in:Sí,No,sí,no',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'codigo.required' => 'El código es obligatorio.',
            'precio_compra_sin_iva.required' => 'El precio de compra sin IVA es obligatorio.',
            'precio_venta_sin_iva.required' => 'El precio de venta sin IVA es obligatorio.',
            'tipo_producto.in' => 'El tipo de producto debe ser 1 (simple) o 2 (personalizado).',
            'pesable.in' => 'El campo pesable debe ser Sí o No.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function onError(\Throwable $e)
    {
        $this->errorCount++;
        $this->errors[] = "Error: " . $e->getMessage();
    }

    public function getStatistics()
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors
        ];
    }
}