<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithLimit;

/**
 * Lee un Excel/CSV tal cual, sin asumir encabezados ni columnas fijas.
 * Se usa para poder mapear las columnas del archivo del banco a mano,
 * ya que cada banco/billetera exporta con un formato distinto.
 */
class RawSheetImport implements ToArray, WithLimit
{
    public function array(array $array)
    {
        return $array;
    }

    public function limit(): int
    {
        return 5000;
    }
}
