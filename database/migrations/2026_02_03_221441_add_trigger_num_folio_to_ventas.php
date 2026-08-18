<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER ventas_before_insert
            BEFORE INSERT ON ventas
            FOR EACH ROW
            BEGIN
                DECLARE lastNumber INT;

                SELECT IFNULL(MAX(CAST(SUBSTRING(num_folio, 5) AS UNSIGNED)), 0) + 1
                INTO lastNumber
                FROM ventas;

                SET NEW.num_folio = CONCAT('VTA-', LPAD(lastNumber, 6, '0'));
            END
        ");
    }

    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS ventas_before_insert");
    }
};
