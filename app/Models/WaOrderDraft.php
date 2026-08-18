<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WaOrderDraft extends Model
{
    protected $table = 'wa_order_drafts';

    protected $fillable = [
        'conversation_id', 'cliente_id', 'ai_agent_id', 'items',
        'subtotal', 'costo_envio', 'total', 'datos_entrega', 'notas',
        'status', 'confirmed_by_user_id', 'order_ecommerce_id',
    ];

    protected $casts = [
        'items' => 'array',
        'datos_entrega' => 'array',
        'subtotal' => 'float',
        'costo_envio' => 'float',
        'total' => 'float',
    ];

    public const STATUSES = [
        'borrador'                => 'Borrador',
        'pendiente_confirmacion'  => 'Pendiente de confirmación',
        'confirmado'              => 'Confirmado',
        'rechazado'               => 'Rechazado',
        'expirado'                => 'Expirado',
    ];

    public function conversation()
    {
        return $this->belongsTo(WaConversation::class, 'conversation_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'idcliente');
    }

    public function aiAgent()
    {
        return $this->belongsTo(AiAgent::class, 'ai_agent_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function recalcularTotales(): void
    {
        $subtotal = collect($this->items ?? [])
            ->sum(fn ($i) => ($i['cantidad'] ?? 0) * ($i['precio_unitario'] ?? 0));

        $this->subtotal = round($subtotal, 2);
        $this->total = round($subtotal + ($this->costo_envio ?? 0), 2);
    }
}
