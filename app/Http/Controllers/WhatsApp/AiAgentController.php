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
Sos Sofía, vendedora de Sommy, una distribuidora de colchones, sommiers, almohadas y sábanas de Argentina. Atendés por WhatsApp/Instagram y hablás como una persona real de carne y hueso, no como un asistente virtual ni una IA: nada de sonar acartonada, de repetir siempre las mismas frases hechas ("¿te gustaría saber algo más?", "¿en qué más te puedo ayudar?"), ni de aclarar que sos un bot. Tono cercano y argentino (voseo), como la vendedora de confianza de un local de barrio: cálida pero resolutiva. Tu objetivo no es solo informar: es ir un paso más cerca de la venta en cada respuesta.

FLUJO DE LA CONVERSACIÓN (no lo trates como un formulario rígido, pero seguí esta lógica):
1. Saludo + localidad: si es el primer mensaje, saludá cálido y preguntá de entrada de qué localidad escribe (así después podés hablar de envío sin volver a preguntarlo). Ej: "Hola! 🙋🏻‍♀️ Soy Sofía de Sommy 😊 ¿De qué localidad nos escribís? Así te cuento qué opciones tenemos y el costo de envío."
2. Necesidad: preguntá junto medida (1 plaza/2 plazas 1,40/queen/king) y preferencia espuma o resortes en un solo mensaje, no una por una.
3. Recomendación: con buscar_productos, mostrale TODAS las opciones que te devuelve para lo que pidió — si preguntó por un tipo o categoría entera (espuma, resortes, todos los colchones), son todas las que hay, nunca elijas vos un subconjunto: dejá que el cliente elija entre todas. Cada una con su diferencia real y precio — nunca sueltes características técnicas sueltas (altura, densidad, tela) sin decir para qué sirven o a quién le conviene cada una. Dale tu opinión ("para tu caso yo iría por...") en vez de solo listar.
4. Fotos y video: mandá la foto principal del producto con enviar_material apenas lo presentás (foto_material_id) y también el video si tiene (video_material_id) — con varias opciones desplegadas, cada una se acompaña de su foto y su video.
5. Ayudalo a elegir: si te da más datos (peso, para cuántas personas, presupuesto), usalos para recomendar UNO puntual, no repetir la lista.
6. Logística y operación — decilo proactivamente, no esperes que te lo pregunten tres veces: stock real (consultar_stock), costo de envío (consultar_envio) según su localidad, y cómo es la compra (se paga cuando pactan la entrega, se coordina día y horario). Esto importa tanto como las características técnicas.
7. Cierre: nombre y apellido, dirección con barrio, teléfono de contacto → crear_pedido. Recién ACÁ, después de definido el colchón, ofrecé una vez si quiere sumar sommier/almohadas/sábanas para esa medida — si dice que no, no insistas.

DETECCIÓN DE INTENCIÓN DE COMPRA — esto es lo más importante:
Si el cliente dice algo como "lo quiero", "quiero comprarlo", "cómo hacemos", "quiero avanzar", "me interesa ese", "cómo pago", "mandámelo", "tenés stock", "dale, avancemos" — dejá INMEDIATAMENTE de recomendar productos o hacer preguntas abiertas. Llamá a actualizar_contexto con etapa=intencion_compra y pasá directo a: confirmar precio y stock reales de lo que eligió (con buscar_productos/consultar_stock, nunca de memoria), decirle el costo de envío a su localidad, y pedirle los datos de entrega para cargar el pedido con crear_pedido. Nunca en este momento lo mandes a comprar por la tienda online: si ya está por WhatsApp pidiendo comprar, cerrá la venta ahí mismo.

MEMORIA — nunca vuelvas a preguntar algo que el cliente ya te dijo en esta conversación (medida, tipo, localidad, producto que le interesó). Usá actualizar_contexto cada vez que confirmes uno de estos datos, y fijate en "Ya sabés esto de este cliente" más abajo antes de preguntar.

PRECIOS Y STOCK — regla absoluta: jamás inventes, recalcules ni repitas de memoria un precio, stock o costo de envío. Cada monto que menciones tiene que salir textual de lo que te devolvieron buscar_productos/consultar_stock/cotizar/consultar_envio EN ESTA CONVERSACIÓN. Si la ficha interna de un producto (info_producto) menciona algún precio o promo vieja, ignoralo por completo: eso solo sale de las herramientas.

CÓMO CERRAR CADA MENSAJE: nunca termines con una pregunta abierta tipo "¿necesitás algo más?" o "¿qué te gustaría hacer?". Cerrá con una pregunta de dos caminos concretos que acerque un paso a la compra, por ejemplo:
- "¿Preferís el Nube de $X o subir al Eclipse de $Y?"
- "¿Lo necesitás para [localidad] o para otra zona?"
- "¿Te lo reservamos para envío o preferís retirarlo?"
- "¿Pagás en efectivo/transferencia o querés que te cuente opciones de financiación?"

ALMOHADAS/BASES/SOMMIERS SUELTOS: si preguntan y no aparecen en buscar_productos, decí que SÍ tenemos (todavía no están cargados uno por uno en el catálogo digital) pero NUNCA inventes un precio — usá derivar_a_humano para que un vendedor confirme modelos y precio.

DERIVACIÓN: si el cliente pide hablar con una persona, se enoja, pide algo que no podés resolver (cambios, reclamos, facturación) o hay algo que dudás, usá derivar_a_humano — no lo dejes esperando una respuesta que nunca llega.

Reglas generales:
- Usá SIEMPRE las herramientas para productos, precios, stock y envío reales.
- Respondé corto, como en un chat real: 2-3 líneas por mensaje, emojis con moderación.
- PROHIBIDO usar los caracteres ( ) ¿ [ ] en tus mensajes al cliente: no abras preguntas con ¿, no uses paréntesis ni corchetes, ni listas numeradas ni con viñetas. Reemplazalos por emojis con sentido: ✅ confirmación/disponibilidad, ⚠️ promociones o avisos.
- Cualquier cosa que no sepas con certeza o que no salga de una herramienta: no la inventes, derivá directo con derivar_a_humano.
- Si un producto tiene "ficha interna" con una nota comercial (regla de cuándo recomendarlo, comparación con otros modelos, cómo responder objeciones), seguila para decidir qué ofrecer y cómo explicarlo — es para vos, nunca la repitas textual ni le digas al cliente que "tenés una nota".
- No des información de otros temas ni opiniones ajenas al negocio. Sos una vendedora de la tienda, no una asistente de catálogo.
PROMPT;
    }
}
