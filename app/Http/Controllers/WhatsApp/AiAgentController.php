<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AiAgentController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'agents.index');

        $agents = AiAgent::withCount('runs')->orderBy('id')->get()
            ->map(function ($agent) {
                $agent->costo_hoy = $agent->costoHoy();
                return $agent;
            });

        return view('whatsapp.agents.index', compact('agents'));
    }

    public function create()
    {
        Gate::authorize('haveaccess', 'agents.crud');

        return view('whatsapp.agents.form', [
            'agent' => new AiAgent([
                'provider' => 'openai',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'tools_enabled' => array_keys(AiAgent::TOOLS),
                'max_turnos_sin_humano' => 10,
                'system_prompt' => $this->defaultPrompt(),
                'mensaje_derivacion' => 'Te paso con una persona del equipo que te va a ayudar enseguida 🙌',
            ]),
            'sucursales' => Sucursal::where('activo', 1)->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'agents.crud');

        $data = $this->validated($request);
        AiAgent::create($data);

        return redirect()->route('whatsapp.agents.index')
            ->with('message', 'Agente creado correctamente.')->with('typealert', 'success');
    }

    public function edit($id)
    {
        Gate::authorize('haveaccess', 'agents.crud');

        return view('whatsapp.agents.form', [
            'agent' => AiAgent::findOrFail($id),
            'sucursales' => Sucursal::where('activo', 1)->get(['id', 'nombre']),
        ]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'agents.crud');

        $agent = AiAgent::findOrFail($id);
        $agent->update($this->validated($request));

        return redirect()->route('whatsapp.agents.index')
            ->with('message', 'Agente actualizado.')->with('typealert', 'success');
    }

    public function toggle($id)
    {
        Gate::authorize('haveaccess', 'agents.toggle');

        $agent = AiAgent::findOrFail($id);
        $agent->update(['activo' => !$agent->activo]);

        return response()->json(['status' => 1, 'activo' => $agent->activo]);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'agents.crud');

        AiAgent::findOrFail($id)->delete();

        return redirect()->route('whatsapp.agents.index')
            ->with('message', 'Agente eliminado.')->with('typealert', 'success');
    }

    /**
     * Log de ejecuciones (costos y herramientas usadas).
     */
    public function runs($id)
    {
        Gate::authorize('haveaccess', 'agents.index');

        $agent = AiAgent::findOrFail($id);
        $runs = $agent->runs()->with('conversation:id,phone_e164,profile_name')
            ->orderByDesc('id')->limit(100)->get();

        return view('whatsapp.agents.runs', compact('agent', 'runs'));
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'activo' => 'nullable|boolean',
            'provider' => 'required|in:openai,anthropic',
            'model' => 'required|string|max:80',
            'system_prompt' => 'required|string|max:20000',
            'temperature' => 'required|numeric|min:0|max:2',
            'tools_enabled' => 'nullable|array',
            'tools_enabled.*' => 'in:' . implode(',', array_keys(AiAgent::TOOLS)),
            'horario_desde' => 'nullable|date_format:H:i',
            'horario_hasta' => 'nullable|date_format:H:i',
            'horario_dias' => 'nullable|array',
            'solo_fuera_de_horario' => 'nullable|boolean',
            'max_turnos_sin_humano' => 'required|integer|min:1|max:100',
            'mensaje_derivacion' => 'nullable|string|max:1000',
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
            'tope_costo_diario' => 'nullable|numeric|min:0',
        ]);

        $data['activo'] = $request->boolean('activo');
        $data['solo_fuera_de_horario'] = $request->boolean('solo_fuera_de_horario');
        $data['tools_enabled'] = $request->input('tools_enabled', []);

        if ($request->filled('horario_desde') && $request->filled('horario_hasta')) {
            $data['horario'] = [
                'dias' => array_map('intval', $request->input('horario_dias', range(1, 7))),
                'desde' => $request->horario_desde,
                'hasta' => $request->horario_hasta,
            ];
        } else {
            $data['horario'] = null;
        }
        unset($data['horario_desde'], $data['horario_hasta'], $data['horario_dias']);

        return $data;
    }

    protected function defaultPrompt(): string
    {
        return <<<PROMPT
Sos un vendedor de Sommy, una distribuidora de colchones, sommiers, almohadas y sábanas de Argentina. Atendés por WhatsApp/Instagram y hablás como una persona real de carne y hueso, no como un asistente virtual ni una IA: nada de sonar acartonado, de repetir siempre las mismas frases, ni de aclarar que sos un bot. Tono cercano y argentino (voseo), como el vendedor de confianza de un local de barrio. Tu objetivo es ayudar al cliente a elegir el producto correcto y concretar la venta.

Cómo arrancar:
- Si es el primer mensaje del cliente en la conversación, abrí con un saludo de bienvenida cálido (ej: "Hola, ¿cómo andás? Bienvenido/a a Sommy Argentina 😊, ¿en qué te puedo ayudar?").

Reglas:
- Usá SIEMPRE las herramientas para consultar productos, precios y stock reales. Nunca inventes precios ni stock.
- Cuando presentes un producto con buscar_productos/info_producto y tenga foto_material_id, mandá SIEMPRE la foto con enviar_material antes o junto con el texto — nunca describas un producto sin mostrarlo. Si hay video o audio cargado también, mandalo cuando sea relevante para lo que se está hablando.
- Respondé corto y claro, como en un chat real: máximo 3-4 líneas por mensaje, podés usar emojis con moderación.
- Preguntá lo necesario para asesorar bien: medida (1 plaza, 2 plazas, queen, king), preferencia de firmeza, presupuesto.
- Si preguntan cuánto sale el envío, usá consultar_envio y contales el costo real según su localidad.
- Si preguntan por almohadas, bases o sommiers sueltos: SÍ tenemos, aunque todavía no estén cargados uno por uno en el catálogo digital. Decilo con naturalidad, pero NUNCA inventes un precio para estos ítems — usá derivar_a_humano para que un vendedor le confirme modelos y precio.
- Cuando el cliente confirme qué quiere, armá la cotización con la herramienta cotizar y presentale el total.
- Si el cliente acepta, pedile de forma conversacional (no como un formulario) su nombre, dirección de entrega y localidad, y usá crear_pedido. Aclarale que un asesor confirma el pedido a la brevedad.
- Si el cliente pide hablar con una persona, se enoja, pide algo que no podés resolver (cambios, reclamos, facturación) o dudás, usá derivar_a_humano.
- No des información de otros temas ni opiniones. Sos un vendedor de la tienda.
PROMPT;
    }
}
