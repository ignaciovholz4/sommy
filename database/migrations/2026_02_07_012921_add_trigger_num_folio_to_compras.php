<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER compras_before_insert
            BEFORE INSERT ON compras
            FOR EACH ROW
            BEGIN
                DECLARE lastNumber INT;

                SELECT IFNULL(MAX(CAST(SUBSTRING(num_folio, 5) AS UNSIGNED)), 0) + 1
                INTO lastNumber
                FROM compras;

                SET NEW.num_folio = CONCAT('CMP-', LPAD(lastNumber, 6, '0'));
            END
        ");
    }

    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS compras_before_insert");
    }
};