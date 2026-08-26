<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // WhatsApp: cotizaciones abandonadas
        $schedule->command('whatsapp:expirar-drafts')->dailyAt('05:00');

        // WhatsApp: lo resuelto migra a "cerradas" y vuelve al bot (higiene del tablero)
        $schedule->command('whatsapp:cerrar-inactivas')->hourly();

        // Finanzas: gastos recurrentes y alertas de vencimientos de proveedores
        $schedule->command('gastos:generar-recurrentes')->dailyAt('06:00');
        $schedule->command('cxp:alertar-vencimientos')->dailyAt('08:00');

        // Reposicion inteligente de stock: genera pedidos de compra borrador
        $schedule->command('reposicion:generar-sugerencias')->weeklyOn(1, '06:30');

        // Cobranzas: genera borradores de recordatorio para deuda vencida (quedan a aprobar)
        $schedule->command('cobranzas:generar-recordatorios')->dailyAt('07:00');

        // Notificaciones: alerta diaria de stock crítico / stock bajo
        $schedule->command('stock:alertar-critico')->dailyAt('08:30');

        // Agente CEO: resumen ejecutivo diario dentro del panel (despues de las alertas de arriba)
        $schedule->command('ceo:resumen-diario')->dailyAt('09:00');

        // Chytapay: trae cobros pagados hacia la conciliacion bancaria (solo si hay cuentas conectadas)
        $schedule->command('chytapay:sincronizar-cobros')->everyFifteenMinutes();

        // Cheques: avisa de cheques (propios y de terceros) vencidos o por vencer en 3 dias
        $schedule->command('cheques:alertar-vencimientos')->dailyAt('08:15');

        // Ads: gasto diario de Meta Ads y Google Ads (solo si hay claves cargadas)
        $schedule->command('ads:sincronizar-gasto')->dailyAt('07:00');

        // Auditoria: purga logs viejos para que la tabla no crezca sin limite
        $schedule->command('auditoria:limpiar')->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
