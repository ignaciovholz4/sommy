<?php

namespace App\Services\Ai;

use App\Models\ReporteChatMensaje;
use App\Models\ReporteChatSesion;
use App\Services\Ai\Contracts\LlmClient;
use App\Services\Ai\ReportesTools\ComprasQueryTool;
use App\Services\Ai\ReportesTools\CuentasPorPagarQueryTool;
use App\Services\Ai\ReportesTools\DeudoresQueryTool;
use App\Services\Ai\ReportesTools\DevolucionesQueryTool;
use App\Services\Ai\ReportesTools\GastosQueryTool;
use App\Services\Ai\ReportesTools\MargenQueryTool;
use App\Services\Ai\ReportesTools\NotasQueryTool;
use App\Services\Ai\ReportesTools\StockQueryTool;
use App\Services\Ai\ReportesTools\TesoreriaQueryTool;
use App\Services\Ai\ReportesTools\VentasQueryTool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Chat interno "preguntale a tus datos": mismo patron de loop de tool-calling
 * que AiAgentService, pero desacoplado de WhatsApp/WaConversation. Las tools
 * solo consultan (nunca escriben) y acotan sus propios limites de fecha/filas.
 */
class ReportesAgentService
{
    protected const MAX_ITERATIONS = 6;
    protected const HISTORIAL_MENSAJES = 20;

    public function responder(ReporteChatSesion $sesion, string $pregunta): ReporteChatMensaje
    {
        $sesion->mensajes()->create(['role' => 'user', 'content' => $pregunta, 'created_at' => now()]);

        $client = $this->client();
        $tools = $this->toolDefinitions();
        $messages = $this->buildHistory($sesion);
        $system = $this->buildSystem();
        $model = config('services.reportes.model', 'gpt-4o-mini');

        $ultimaRespuesta = null;

        for ($i = 1; $i <= self::MAX_ITERATIONS; $i++) {
            try {
                $response = $client->chat($system, $messages, $tools, $model, 0.3);
            } catch (\Throwable $e) {
                Log::error('Error del chat de Reportes IA: ' . $e->getMessage());
                $ultimaRespuesta = $sesion->mensajes()->create([
                    'role' => 'assistant',
                    'content' => 'Hubo un error consultando la IA: ' . $e->getMessage(),
                    'created_at' => now(),
                ]);
                break;
            }

            if (!$response->hasToolCalls()) {
                $ultimaRespuesta = $sesion->mensajes()->create([
                    'role' => 'assistant',
                    'content' => $response->text ?: 'No pude generar una respuesta para esa consulta.',
                    'created_at' => now(),
                ]);
                break;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $response->text,
                'tool_calls' => $response->toolCalls,
            ];
            $sesion->mensajes()->create([
                'role' => 'assistant',
                'content' => $response->text,
                'tool_calls' => $response->toolCalls,
                'created_at' => now(),
            ]);

            foreach ($response->toolCalls as $toolCall) {
                $resultado = $this->executeTool($toolCall);
                $contenido = json_encode($resultado, JSON_UNESCAPED_UNICODE);

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'name' => $toolCall['name'],
                    'content' => $contenido,
                ];
                $sesion->mensajes()->create([
                    'role' => 'tool',
                    'tool_name' => $toolCall['name'],
                    'tool_call_id' => $toolCall['id'],
                    'content' => $contenido,
                    'created_at' => now(),
                ]);
            }
        }

        if (!$ultimaRespuesta) {
            $ultimaRespuesta = $sesion->mensajes()->create([
                'role' => 'assistant',
                'content' => 'No pude terminar de responder en el tiempo permitido. Probá con una pregunta más acotada.',
                'created_at' => now(),
            ]);
        }

        if (!$sesion->titulo) {
            $sesion->update(['titulo' => Str::limit($pregunta, 60)]);
        }

        return $ultimaRespuesta;
    }

    protected function client(): LlmClient
    {
        return config('services.reportes.provider') === 'anthropic' ? new AnthropicClient() : new OpenAiClient();
    }

    protected function toolDefinitions(): array
    {
        return [
            VentasQueryTool::definition(),
            MargenQueryTool::definition(),
            StockQueryTool::definition(),
            DeudoresQueryTool::definition(),
            ComprasQueryTool::definition(),
            GastosQueryTool::definition(),
            TesoreriaQueryTool::definition(),
            CuentasPorPagarQueryTool::definition(),
            DevolucionesQueryTool::definition(),
            NotasQueryTool::definition(),
        ];
    }

    protected function executeTool(array $toolCall): array
    {
        try {
            $tool = match ($toolCall['name']) {
                'consultar_ventas' => new VentasQueryTool(),
                'consultar_margen' => new MargenQueryTool(),
                'consultar_stock' => new StockQueryTool(),
                'consultar_deudores' => new DeudoresQueryTool(),
                'consultar_compras' => new ComprasQueryTool(),
                'consultar_gastos' => new GastosQueryTool(),
                'consultar_tesoreria' => new TesoreriaQueryTool(),
                'consultar_cuentas_por_pagar' => new CuentasPorPagarQueryTool(),
                'consultar_devoluciones' => new DevolucionesQueryTool(),
                'consultar_notas' => new NotasQueryTool(),
                default => null,
            };

            if (!$tool) {
                return ['error' => 'Herramienta no disponible'];
            }

            return $tool->execute($toolCall['arguments'] ?? []);
        } catch (\Throwable $e) {
            Log::warning('Error ejecutando tool de Reportes ' . $toolCall['name'] . ': ' . $e->getMessage());
            return ['error' => 'Fallo interno de la herramienta'];
        }
    }

    /** Reconstruye el historial neutral (ultimos N mensajes) para el LLM. */
    protected function buildHistory(ReporteChatSesion $sesion): array
    {
        $recientes = $sesion->mensajes()
            ->orderByDesc('id')
            ->limit(self::HISTORIAL_MENSAJES)
            ->get()
            ->reverse();

        $messages = [];
        foreach ($recientes as $m) {
            if ($m->role === 'tool') {
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $m->tool_call_id,
                    'name' => $m->tool_name,
                    'content' => $m->content,
                ];
            } elseif ($m->role === 'assistant' && $m->tool_calls) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $m->content,
                    'tool_calls' => $m->tool_calls,
                ];
            } else {
                $messages[] = ['role' => $m->role, 'content' => (string) $m->content];
            }
        }

        while ($messages && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }

        return $messages;
    }

    protected function buildSystem(): string
    {
        return "Sos el analista financiero y de negocio interno de Sommy, una distribuidora de colchones. "
            . "Respondes preguntas del equipo sobre ventas, margen, stock, cuentas corrientes de clientes, "
            . "gastos operativos (incluidos publicidad/ads, IA y fletes), tesorería (caja y bancos), cuentas por pagar "
            . "a proveedores y devoluciones, usando EXCLUSIVAMENTE los resultados de las herramientas disponibles: "
            . "nunca inventes cifras. Si una pregunta necesita un dato que ninguna herramienta puede traer "
            . "(por ejemplo el costo real de Meta Ads u OpenAI si todavía no se cargó como gasto en el sistema), "
            . "decilo explícitamente en vez de estimarlo, y sugerí cargarlo en Finanzas > Gastos. "
            . "Cuando falten fechas en la pregunta, asumí el mes actual y aclaralo en la respuesta. "
            . "No te limites a responder lo que se te pregunta literalmente: si al consultar una herramienta ves algo "
            . "que amerita una alerta (deuda vencida, margen cayendo, gasto que se disparó, stock crítico en un producto "
            . "que se vende mucho), mencionalo aunque no te lo hayan preguntado directamente. "
            . "Respondé en español rioplatense, corto y directo, con los números formateados en pesos argentinos cuando corresponda. "
            . "Fecha actual: " . now()->format('d/m/Y') . '.';
    }
}
