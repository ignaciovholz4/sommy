<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAgent extends Model
{
    protected $table = 'ai_agents';

    protected $fillable = [
        'nombre', 'activo', 'provider', 'model', 'system_prompt', 'temperature',
        'tools_enabled', 'horario', 'solo_fuera_de_horario',
        'max_turnos_sin_humano', 'mensaje_derivacion', 'sucursal_id', 'tope_costo_diario',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'solo_fuera_de_horario' => 'boolean',
        'tools_enabled' => 'array',
        'horario' => 'array',
        'temperature' => 'float',
        'tope_costo_diario' => 'float',
    ];

    public const TOOLS = [
        'buscar_productos'  => 'Buscar productos en el catálogo',
        'consultar_stock'   => 'Consultar stock',
        'cotizar'           => 'Armar cotizaciones',
        'crear_pedido'      => 'Proponer pedidos (requiere confirmación humana)',
        'derivar_a_humano'  => 'Derivar a un vendedor',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function runs()
    {
        return $this->hasMany(AiAgentRun::class, 'ai_agent_id');
    }

    public function toolEnabled(string $tool): bool
    {
        return in_array($tool, $this->tools_enabled ?? [], true);
    }

    /**
     * ¿El agente deberia operar en este momento segun su horario configurado?
     * horario: {"dias":[1..7 ISO],"desde":"09:00","hasta":"18:00"}; null = siempre.
     * solo_fuera_de_horario invierte la logica (bot solo cuando no hay vendedores).
     */
    public function isWithinSchedule(): bool
    {
        if (empty($this->horario)) {
            return true;
        }

        $now = now();
        $dias = $this->horario['dias'] ?? range(1, 7);
        $desde = $this->horario['desde'] ?? '00:00';
        $hasta = $this->horario['hasta'] ?? '23:59';

        $inSchedule = in_array($now->isoWeekday(), $dias)
            && $now->format('H:i') >= $desde
            && $now->format('H:i') <= $hasta;

        return $this->solo_fuera_de_horario ? !$inSchedule : $inSchedule;
    }

    /**
     * Gasto acumulado del dia en USD (para el tope diario).
     */
    public function costoHoy(): float
    {
        return (float) $this->runs()
            ->whereDate('created_at', now()->toDateString())
            ->sum('costo_estimado');
    }

    public function overBudget(): bool
    {
        return $this->tope_costo_diario !== null && $this->costoHoy() >= $this->tope_costo_diario;
    }
}
