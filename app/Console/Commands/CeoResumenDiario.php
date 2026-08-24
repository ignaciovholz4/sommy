<?php

namespace App\Console\Commands;

use App\Models\Notificacion;
use App\Models\ReporteChatSesion;
use App\Services\Ai\ReportesAgentService;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Agente CEO: resumen ejecutivo diario dentro del panel (no manda WhatsApp ni mail).
 * Reutiliza el mismo motor de tools del chat de analista IA (ReportesAgentService):
 * crea una sesion de chat nueva por dia para cada usuario con acceso total, le hace
 * una pregunta fija de "resumen ejecutivo" y la respuesta queda ahi como una consulta
 * mas en su historial, mas una notificacion en la campanita para que la vea.
 */
class CeoResumenDiario extends Command
{
    protected $signature = 'ceo:resumen-diario';

    protected $description = 'Genera el resumen ejecutivo diario del agente CEO para cada usuario con acceso total';

    public function handle(): int
    {
        $titulo = 'Resumen ejecutivo — ' . now()->format('d/m/Y');
        $usuarios = $this->usuariosDestino();

        if ($usuarios->isEmpty()) {
            $this->warn('No se encontró ningún usuario para generar el resumen.');
            return self::SUCCESS;
        }

        $pregunta = $this->preguntaFija();
        $generados = 0;

        foreach ($usuarios as $user) {
            $yaExiste = ReporteChatSesion::where('user_id', $user->id)->where('titulo', $titulo)->exists();
            if ($yaExiste) {
                continue;
            }

            try {
                $sesion = ReporteChatSesion::create(['user_id' => $user->id, 'titulo' => $titulo]);
                $respuesta = app(ReportesAgentService::class)->responder($sesion, $pregunta);

                Notificacion::avisar(
                    'ceo',
                    $titulo,
                    Str::limit(trim((string) $respuesta->content), 140),
                    '/reportes/chat?sesion=' . $sesion->id,
                    'info'
                );

                $generados++;
            } catch (\Throwable $e) {
                Log::warning("No se pudo generar el resumen CEO para el usuario {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Resumen ejecutivo generado para {$generados} usuario(s).");
        return self::SUCCESS;
    }

    /** Usuarios con rol de acceso total; si no hay roles cargados, el primer usuario. */
    private function usuariosDestino()
    {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->where('full-access', 'yes');
            })->get();

            if ($admins->isNotEmpty()) {
                return $admins;
            }
        } catch (\Throwable $e) {
            // estructura de roles distinta a la esperada: caemos al fallback de abajo
        }

        return User::orderBy('id')->limit(1)->get();
    }

    private function preguntaFija(): string
    {
        return 'Dame el resumen ejecutivo de hoy para el dueño del negocio. Cubrí, con los números reales de las '
            . 'herramientas: facturación y cantidad de ventas de hoy y en lo que va del mes (con la comparación contra '
            . 'el período anterior si la tenés), margen bruto del mes, gastos operativos del mes por categoría '
            . '(incluido publicidad/ads e IA si hay algo cargado), saldo actual de caja y bancos, deuda de clientes '
            . 'en cuenta corriente con los principales deudores, deuda con proveedores vencida y próxima a vencer, '
            . 'stock crítico si hay, y devoluciones del mes si hubo. Cerrá con hasta 3 alertas o recomendaciones '
            . 'concretas y accionables para hoy, priorizando lo más urgente primero. Si algún dato no está '
            . 'disponible porque todavía no se cargó en el sistema (por ejemplo el gasto real de ads o de IA), '
            . 'decilo explícitamente en vez de omitirlo.';
    }
}
