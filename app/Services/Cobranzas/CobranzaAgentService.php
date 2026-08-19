<?php

namespace App\Services\Cobranzas;

use App\Models\Cliente;
use App\Models\CobranzaRecordatorio;
use App\Models\WaTemplate;
use App\Services\Ai\AnthropicClient;
use App\Services\Ai\OpenAiClient;
use App\Support\PhoneAr;
use Illuminate\Support\Facades\Log;

/**
 * Arma los borradores de recordatorio de cobranza (nunca envia): elige el
 * "tier" segun cuanto vencio la deuda, busca la plantilla de WhatsApp
 * aprobada para ese tier, y opcionalmente le pide a un LLM una nota interna
 * de apoyo para quien va a revisar y aprobar el envio.
 *
 * El texto que se manda por WhatsApp SIEMPRE es la plantilla aprobada por
 * Meta (obligatorio fuera de la ventana de 24h) — el LLM nunca redacta el
 * mensaje que se envia, solo la nota interna.
 */
class CobranzaAgentService
{
    public function __construct(protected DeudaVencidaService $deudaService)
    {
    }

    /** @return array{generados: int, omitidos_sin_plantilla: array, omitidos_sin_telefono: array} */
    public function generarBorradores(): array
    {
        $deudas = $this->deudaService->calcular();

        $generados = 0;
        $omitidosSinPlantilla = [];
        $omitidosSinTelefono = [];

        foreach ($deudas as $d) {
            $cliente = Cliente::find($d['cliente_id']);
            if (!$cliente) {
                continue;
            }

            // Ya hay un recordatorio en curso para este cliente: no duplicar
            $yaExiste = CobranzaRecordatorio::where('cliente_id', $cliente->idcliente)
                ->whereIn('estado', ['pendiente_revision', 'aprobado'])
                ->exists();
            if ($yaExiste) {
                continue;
            }

            if (!PhoneAr::toE164($cliente->telefono)) {
                $omitidosSinTelefono[] = $cliente->nombre;
                continue;
            }

            $tier = $this->tierPara((int) $d['dias_vencido_max']);
            $template = WaTemplate::approved()->where('uso', "cobranza_{$tier}")->first();
            if (!$template) {
                $omitidosSinPlantilla[] = $cliente->nombre . " (tier {$tier})";
                continue;
            }

            CobranzaRecordatorio::create([
                'cliente_id' => $cliente->idcliente,
                'monto_vencido' => $d['monto_vencido'],
                'dias_vencido' => $d['dias_vencido_max'],
                'tier' => $tier,
                'wa_template_id' => $template->id,
                'template_params' => $this->templateParams($cliente, $d),
                'nota_interna' => $this->notaInterna($cliente, $d, $tier),
                'estado' => 'pendiente_revision',
            ]);
            $generados++;
        }

        return [
            'generados' => $generados,
            'omitidos_sin_plantilla' => $omitidosSinPlantilla,
            'omitidos_sin_telefono' => $omitidosSinTelefono,
        ];
    }

    protected function tierPara(int $diasVencido): string
    {
        return match (true) {
            $diasVencido <= 15 => 'suave',
            $diasVencido <= 45 => 'firme',
            default => 'urgente',
        };
    }

    /** Parametros genericos {{1}}=nombre, {{2}}=monto: el usuario redacta la plantilla acorde en Meta. */
    protected function templateParams(Cliente $cliente, array $d): array
    {
        return [
            trim($cliente->nombre . ' ' . ($cliente->paterno ?? '')),
            number_format((float) $d['monto_vencido'], 2, ',', '.'),
        ];
    }

    protected function notaInterna(Cliente $cliente, array $d, string $tier): ?string
    {
        if (!config('services.openai.api_key') && !config('services.anthropic.api_key')) {
            return null;
        }

        try {
            $client = config('services.openai.api_key') ? new OpenAiClient() : new AnthropicClient();
            $model = config('services.cobranzas.nota_model', 'gpt-4o-mini');

            $system = 'Sos un asistente interno del equipo de cobranzas de Sommy (distribuidora de colchones). '
                . 'Tu tarea es escribir una nota BREVE (2-3 lineas) para quien va a revisar y aprobar el envio de un '
                . 'recordatorio de pago, con sugerencias de abordaje segun la antiguedad de la deuda. No inventes '
                . 'datos que no esten en el mensaje del usuario. No redactes el mensaje que se le manda al cliente '
                . '(eso ya sale de una plantilla aprobada), solo la nota interna para el equipo.';

            $user = "Cliente: {$cliente->nombre} {$cliente->paterno}\n"
                . 'Monto vencido: $' . number_format((float) $d['monto_vencido'], 2, ',', '.') . "\n"
                . "Dias de atraso: {$d['dias_vencido_max']}\n"
                . "Nivel de cobranza: {$tier} (suave/firme/urgente)";

            $response = $client->chat($system, [['role' => 'user', 'content' => $user]], [], $model, 0.5);

            return trim((string) $response->text) ?: null;
        } catch (\Throwable $e) {
            Log::warning('No se pudo generar la nota interna de cobranza: ' . $e->getMessage());
            return null;
        }
    }
}
