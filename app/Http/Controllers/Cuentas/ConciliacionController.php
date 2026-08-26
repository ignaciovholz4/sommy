<?php

namespace App\Http\Controllers\Cuentas;

use App\Http\Controllers\Controller;
use App\Imports\RawSheetImport;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\MovimientoImportado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Conciliación bancaria manual: se sube un extracto (Excel/CSV) de un CBU/CVU,
 * se mapean sus columnas a mano (cada banco/billetera exporta distinto) y
 * después cada movimiento importado se vincula a un movimiento interno ya
 * cargado en el sistema, sugerido por monto/fecha y confirmado por el usuario.
 */
class ConciliacionController extends Controller
{
    private const DISCO_TEMP = 'local';
    private const CARPETA_TEMP = 'conciliacion_tmp';

    public function index(Cuenta $cuenta)
    {
        return view('cuentas.conciliacion.index', compact('cuenta'));
    }

    /**
     * Sube el archivo, lo guarda temporalmente y devuelve un preview
     * (encabezados detectados + primeras filas) para armar el mapeo de columnas.
     */
    public function previsualizar(Request $request, Cuenta $cuenta)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $archivo = $request->file('archivo');
        $token = (string) Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        $ruta = self::CARPETA_TEMP . '/' . $cuenta->id . '/' . $token;

        Storage::disk(self::DISCO_TEMP)->putFileAs(
            self::CARPETA_TEMP . '/' . $cuenta->id,
            $archivo,
            $token
        );

        $filas = $this->leerArchivo($cuenta, $token);

        if (empty($filas)) {
            return response()->json(['estado' => 0, 'mensaje' => 'El archivo está vacío o no se pudo leer.']);
        }

        return response()->json([
            'estado'          => 1,
            'file_token'      => $token,
            'archivo_nombre'  => $archivo->getClientOriginalName(),
            'total_filas'     => count($filas),
            'preview'         => array_slice($filas, 0, 8),
            'columnas_count'  => max(array_map('count', array_slice($filas, 0, 20))),
        ]);
    }

    /**
     * Confirma el mapeo de columnas y crea los movimientos_bancarios_importados
     * en estado "pendiente". Salta filas duplicadas (mismo archivo importado
     * dos veces, o fila ya existente para esa cuenta).
     */
    public function importar(Request $request, Cuenta $cuenta)
    {
        $validated = $request->validate([
            'file_token'         => 'required|string',
            'archivo_nombre'     => 'nullable|string',
            'con_encabezado'     => 'required|boolean',
            'col_fecha'          => 'required|integer|min:0',
            'col_descripcion'    => 'nullable|integer|min:0',
            'col_referencia'     => 'nullable|integer|min:0',
            'modo_importe'       => 'required|in:signo_unico,dos_columnas',
            'col_importe'        => 'required_if:modo_importe,signo_unico|nullable|integer|min:0',
            'col_ingreso'        => 'required_if:modo_importe,dos_columnas|nullable|integer|min:0',
            'col_egreso'         => 'required_if:modo_importe,dos_columnas|nullable|integer|min:0',
        ]);

        $filas = $this->leerArchivo($cuenta, $validated['file_token']);

        if ($validated['con_encabezado']) {
            array_shift($filas);
        }

        $creados = 0;
        $duplicados = 0;
        $invalidos = 0;

        foreach ($filas as $fila) {
            if ($this->filaVacia($fila)) {
                continue;
            }

            $fecha = $this->parsearFecha($fila[$validated['col_fecha']] ?? null);
            if (!$fecha) {
                $invalidos++;
                continue;
            }

            if ($validated['modo_importe'] === 'signo_unico') {
                $monto = $this->parsearMonto($fila[$validated['col_importe']] ?? null);
                if ($monto === null || $monto == 0) {
                    $invalidos++;
                    continue;
                }
                $tipo = $monto >= 0 ? 'ingreso' : 'egreso';
                $monto = abs($monto);
            } else {
                $ingreso = $this->parsearMonto($fila[$validated['col_ingreso']] ?? null) ?? 0;
                $egreso = $this->parsearMonto($fila[$validated['col_egreso']] ?? null) ?? 0;

                if ($ingreso == 0 && $egreso == 0) {
                    $invalidos++;
                    continue;
                }
                $tipo = abs($ingreso) >= abs($egreso) ? 'ingreso' : 'egreso';
                $monto = abs($ingreso) >= abs($egreso) ? abs($ingreso) : abs($egreso);
            }

            $descripcion = isset($validated['col_descripcion'])
                ? trim((string) ($fila[$validated['col_descripcion']] ?? ''))
                : null;
            $referencia = isset($validated['col_referencia'])
                ? trim((string) ($fila[$validated['col_referencia']] ?? ''))
                : null;

            $existe = MovimientoImportado::where('cuenta_id', $cuenta->id)
                ->whereDate('fecha', $fecha)
                ->where('tipo', $tipo)
                ->where('monto', $monto)
                ->where('descripcion', $descripcion ?: null)
                ->where('referencia', $referencia ?: null)
                ->exists();

            if ($existe) {
                $duplicados++;
                continue;
            }

            MovimientoImportado::create([
                'cuenta_id'       => $cuenta->id,
                'archivo_nombre'  => $validated['archivo_nombre'] ?? null,
                'fecha'           => $fecha,
                'tipo'            => $tipo,
                'monto'           => $monto,
                'descripcion'     => $descripcion ?: null,
                'referencia'      => $referencia ?: null,
                'fila_original'   => array_values($fila),
                'estado'          => 'pendiente',
            ]);
            $creados++;
        }

        Storage::disk(self::DISCO_TEMP)->delete(
            self::CARPETA_TEMP . '/' . $cuenta->id . '/' . $validated['file_token']
        );

        return response()->json([
            'estado'     => 1,
            'mensaje'    => "Se importaron {$creados} movimiento(s). {$duplicados} ya existían, {$invalidos} fila(s) no se pudieron leer.",
            'creados'    => $creados,
            'duplicados' => $duplicados,
            'invalidos'  => $invalidos,
        ]);
    }

    /**
     * Listado de movimientos importados con la mejor sugerencia de match
     * (mismo tipo y monto exacto, movimiento interno más cercano en fecha,
     * todavía no vinculado a otro importado).
     */
    public function data(Cuenta $cuenta, Request $request)
    {
        $estado = $request->query('estado', 'pendiente');

        $importados = MovimientoImportado::with(['movimiento', 'conciliadoPor'])
            ->where('cuenta_id', $cuenta->id)
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('fecha')
            ->get();

        $yaVinculados = MovimientoImportado::where('cuenta_id', $cuenta->id)
            ->whereNotNull('movimiento_id')
            ->pluck('movimiento_id');

        $resultado = $importados->map(function (MovimientoImportado $imp) use ($yaVinculados) {
            $sugerido = null;

            if ($imp->estado === 'pendiente') {
                $sugerido = Movimiento::where('cuenta_id', $imp->cuenta_id)
                    ->where('tipo', $imp->tipo)
                    ->where('total', $imp->monto)
                    ->whereNotIn('id', $yaVinculados)
                    ->orderByRaw('ABS(DATEDIFF(fecha, ?))', [$imp->fecha->toDateString()])
                    ->first();
            }

            return [
                'id'          => $imp->id,
                'origen'      => $imp->origen,
                'fecha'       => $imp->fecha->format('d/m/Y'),
                'tipo'        => $imp->tipo,
                'monto'       => (float) $imp->monto,
                'descripcion' => $imp->descripcion,
                'referencia'  => $imp->referencia,
                'estado'      => $imp->estado,
                'movimiento'  => $imp->movimiento ? [
                    'id'     => $imp->movimiento->id,
                    'fecha'  => Carbon::parse($imp->movimiento->fecha)->format('d/m/Y H:i'),
                    'total'  => (float) $imp->movimiento->total,
                    'comprobante' => $imp->movimiento->comprobante,
                ] : null,
                'conciliado_por' => optional($imp->conciliadoPor)->name,
                'sugerido'    => $sugerido ? [
                    'id'          => $sugerido->id,
                    'fecha'       => Carbon::parse($sugerido->fecha)->format('d/m/Y H:i'),
                    'total'       => (float) $sugerido->total,
                    'comprobante' => $sugerido->comprobante,
                    'cliente_proveedor' => $sugerido->cliente_proveedor,
                ] : null,
            ];
        });

        return response()->json([
            'estado' => 1,
            'data'   => $resultado,
        ]);
    }

    /** Búsqueda manual de movimientos internos de la cuenta, para cuando la sugerencia no sirve. */
    public function buscarMovimientos(Cuenta $cuenta, Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $vinculados = MovimientoImportado::where('cuenta_id', $cuenta->id)
            ->whereNotNull('movimiento_id')
            ->pluck('movimiento_id');

        $movimientos = Movimiento::where('cuenta_id', $cuenta->id)
            ->whereNotIn('id', $vinculados)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('comprobante', 'like', "%{$q}%")
                        ->orWhere('cliente_proveedor', 'like', "%{$q}%")
                        ->orWhere('total', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('fecha')
            ->limit(30)
            ->get(['id', 'fecha', 'tipo', 'total', 'comprobante', 'cliente_proveedor']);

        return response()->json(['estado' => 1, 'movimientos' => $movimientos]);
    }

    /** Confirma el vínculo entre un movimiento importado y uno interno. */
    public function conciliar(Request $request, Cuenta $cuenta, MovimientoImportado $importado)
    {
        abort_if($importado->cuenta_id !== $cuenta->id, 404);

        $validated = $request->validate([
            'movimiento_id' => 'required|exists:movimientos,id',
        ]);

        $movimiento = Movimiento::where('id', $validated['movimiento_id'])
            ->where('cuenta_id', $cuenta->id)
            ->firstOrFail();

        $yaUsado = MovimientoImportado::where('movimiento_id', $movimiento->id)
            ->where('id', '!=', $importado->id)
            ->exists();

        if ($yaUsado) {
            return response()->json(['estado' => 0, 'mensaje' => 'Ese movimiento interno ya está conciliado con otra fila importada.'], 422);
        }

        $importado->update([
            'movimiento_id'  => $movimiento->id,
            'estado'         => 'conciliado',
            'conciliado_por' => Auth::id(),
            'conciliado_at'  => now(),
        ]);

        return response()->json(['estado' => 1, 'mensaje' => 'Movimiento conciliado.']);
    }

    /** Deshace una conciliación, vuelve la fila importada a pendiente. */
    public function deshacer(Cuenta $cuenta, MovimientoImportado $importado)
    {
        abort_if($importado->cuenta_id !== $cuenta->id, 404);

        $importado->update([
            'movimiento_id'  => null,
            'estado'         => 'pendiente',
            'conciliado_por' => null,
            'conciliado_at'  => null,
        ]);

        return response()->json(['estado' => 1, 'mensaje' => 'Se deshizo la conciliación.']);
    }

    /** Descarta una fila importada que no corresponde conciliar (ej. saldo de apertura del extracto). */
    public function descartar(Cuenta $cuenta, MovimientoImportado $importado)
    {
        abort_if($importado->cuenta_id !== $cuenta->id, 404);

        $importado->update(['estado' => 'descartado']);

        return response()->json(['estado' => 1, 'mensaje' => 'Fila descartada.']);
    }

    private function leerArchivo(Cuenta $cuenta, string $token): array
    {
        $ruta = self::CARPETA_TEMP . '/' . $cuenta->id . '/' . $token;
        $rutaCompleta = Storage::disk(self::DISCO_TEMP)->path($ruta);

        $hojas = Excel::toArray(new RawSheetImport, $rutaCompleta);

        return $hojas[0] ?? [];
    }

    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (trim((string) $valor) !== '') {
                return false;
            }
        }
        return true;
    }

    private function parsearFecha($valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        // Serial de fecha de Excel (número de días desde 1899-12-30)
        if (is_numeric($valor) && $valor > 20000 && $valor < 60000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor)->format('Y-m-d');
            } catch (\Throwable $e) {
                // sigue con los otros formatos
            }
        }

        $valor = trim((string) $valor);
        $formatos = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d', 'd/m/y'];

        foreach ($formatos as $formato) {
            try {
                return Carbon::createFromFormat($formato, $valor)->format('Y-m-d');
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parsearMonto($valor): ?float
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $texto = trim((string) $valor);
        $negativo = false;

        if (str_starts_with($texto, '(') && str_ends_with($texto, ')')) {
            $negativo = true;
            $texto = substr($texto, 1, -1);
        }

        // Deja solo dígitos, separadores y signo
        $texto = preg_replace('/[^0-9,.\-]/', '', $texto);
        if ($texto === '' || $texto === '-') {
            return null;
        }

        if (str_starts_with($texto, '-')) {
            $negativo = true;
            $texto = substr($texto, 1);
        }

        $ultimaComa = strrpos($texto, ',');
        $ultimoPunto = strrpos($texto, '.');

        if ($ultimaComa !== false && $ultimoPunto !== false) {
            // El separador decimal es el que aparece más a la derecha
            if ($ultimaComa > $ultimoPunto) {
                $texto = str_replace('.', '', $texto);
                $texto = str_replace(',', '.', $texto);
            } else {
                $texto = str_replace(',', '', $texto);
            }
        } elseif ($ultimaComa !== false) {
            // Solo coma: se asume separador decimal (formato AR)
            $texto = str_replace(',', '.', $texto);
        }
        // Solo punto o ninguno: se asume que ya es formato decimal válido

        if (!is_numeric($texto)) {
            return null;
        }

        $numero = (float) $texto;
        return $negativo ? -$numero : $numero;
    }
}
