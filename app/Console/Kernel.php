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

        // Finanzas: gastos recurrentes y alertas de vencimientos de proveedores
        $schedule->command('gastos:generar-recurrentes')->dailyAt('06:00');
        $schedule->command('cxp:alertar-vencimientos')->dailyAt('08:00');

        // Reposicion inteligente de stock: genera pedidos de compra borrador
        $schedule->command('reposicion:generar-sugerencias')->weeklyOn(1, '06:30');
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
