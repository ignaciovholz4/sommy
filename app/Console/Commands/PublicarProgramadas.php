<?php

namespace App\Console\Commands;

use App\Http\Controllers\Publicaciones\PublicacionController;
use App\Services\Publicaciones\MetaPublisherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Calendario de contenido del Estudio de Publicaciones: publica en Meta las
 * piezas que el usuario dejó programadas (publicaciones.estado = programada)
 * apenas se cumple su programado_para. Corre cada 5 minutos.
 */
class PublicarProgramadas extends Command
{
    protected $signature = 'publicaciones:auto-publicar';

    protected $description = 'Publica en Facebook/Instagram las piezas programadas cuya hora ya llegó';

    public function handle(MetaPublisherService $meta): int
    {
        $pendientes = DB::table('publicaciones')
            ->where('estado', 'programada')
            ->whereNotNull('programado_para')
            ->where('programado_para', '<=', now())
            ->get();

        foreach ($pendientes as $pub) {
            $canales = array_filter(explode(',', (string) $pub->canales_programados));
            if (!$canales || !$pub->imagen_final) {
                DB::table('publicaciones')->where('id', $pub->id)->update(['estado' => 'borrador', 'updated_at' => now()]);
                continue;
            }

            [$ok, $errores] = PublicacionController::publicarEnCanales($pub, $canales, $meta);

            if ($ok) {
                $this->info("Publicacion #{$pub->id}: publicada en " . implode(',', $ok));
            }
            if ($errores) {
                $this->error("Publicacion #{$pub->id}: error en " . implode(',', array_keys($errores)) . ' — ' . implode(' | ', $errores));
            }
        }

        return self::SUCCESS;
    }
}
