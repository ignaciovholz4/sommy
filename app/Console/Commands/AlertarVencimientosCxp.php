<?php

namespace App\Console\Commands;

use App\Models\ProveedorCcMovimiento;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Alerta de vencimientos de cuentas por pagar: loguea (y si hay mail configurado,
 * envía al admin) un resumen de las deudas vencidas y de las que vencen en 7 días.
 *
 * NO registrado en el Kernel: ver docs/FINANZAS_NOTES.md para el snippet de scheduler.
 */
class AlertarVencimientosCxp extends Command
{
    protected $signature = 'cxp:alertar-vencimientos';

    protected $description = 'Loguea y avisa por mail las deudas a proveedores vencidas o que vencen en los próximos 7 días';

    public function handle(): int
    {
        $hoy = now()->startOfDay();

        $vencidas = ProveedorCcMovimiento::with('proveedor')
            ->where('tipo', 'debe')
            ->where('estado', '!=', 'pagado')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento')
            ->get();

        $porVencer = ProveedorCcMovimiento::with('proveedor')
            ->where('tipo', 'debe')
            ->where('estado', '!=', 'pagado')
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy->toDateString(), $hoy->copy()->addDays(7)->toDateString()])
            ->orderBy('fecha_vencimiento')
            ->get();

        if ($vencidas->isEmpty() && $porVencer->isEmpty()) {
            $this->info('Sin deudas vencidas ni por vencer en los próximos 7 días. Todo al día.');

            return self::SUCCESS;
        }

        $lineas = [];
        $fmt = fn ($m) => sprintf(
            '- %s | %s | $%s | vence %s',
            $m->proveedor->nombre ?? ('Proveedor #' . $m->proveedor_id),
            $m->descripcion ?: ($m->compra_id ? 'Compra #' . $m->compra_id : 'Deuda'),
            number_format($m->monto, 2, ',', '.'),
            $m->fecha_vencimiento->format('d/m/Y')
        );

        if ($vencidas->isNotEmpty()) {
            $lineas[] = 'DEUDAS VENCIDAS (' . $vencidas->count() . ') — total $' . number_format($vencidas->sum('monto'), 2, ',', '.');
            foreach ($vencidas as $m) {
                $lineas[] = $fmt($m);
            }
            $lineas[] = '';
        }

        if ($porVencer->isNotEmpty()) {
            $lineas[] = 'VENCEN EN LOS PRÓXIMOS 7 DÍAS (' . $porVencer->count() . ') — total $' . number_format($porVencer->sum('monto'), 2, ',', '.');
            foreach ($porVencer as $m) {
                $lineas[] = $fmt($m);
            }
        }

        $resumen = implode(PHP_EOL, $lineas);

        Log::info('Resumen de vencimientos CxP:' . PHP_EOL . $resumen);
        $this->line($resumen);

        // Envío por mail al admin: si el mail no está configurado o falla, no explota
        try {
            $destinatarios = $this->emailsAdmin();

            if ($destinatarios->isEmpty()) {
                $this->warn('No se encontró un mail de admin para enviar el resumen (solo quedó logueado).');

                return self::SUCCESS;
            }

            Mail::raw(
                "Resumen de cuentas por pagar:\n\n" . $resumen . "\n\nEntrá al módulo Finanzas > Cuentas por Pagar para registrar los pagos.",
                function ($mail) use ($destinatarios) {
                    $mail->to($destinatarios->all())
                        ->subject('CxP: deudas vencidas o por vencer en 7 días');
                }
            );

            $this->info('Resumen enviado por mail a: ' . $destinatarios->implode(', '));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el mail de vencimientos CxP: ' . $e->getMessage());
            $this->warn('El mail no salió (' . $e->getMessage() . '), pero el resumen quedó logueado.');
        }

        return self::SUCCESS;
    }

    /**
     * Mails de los usuarios con rol de acceso total; si no hay, el primer usuario.
     */
    private function emailsAdmin()
    {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->where('full-access', 'yes');
            })->pluck('email')->filter();

            if ($admins->isNotEmpty()) {
                return $admins->unique()->values();
            }
        } catch (\Throwable $e) {
            // Si la estructura de roles cambia, caemos al primer usuario
        }

        return User::orderBy('id')->limit(1)->pluck('email')->filter()->values();
    }
}
