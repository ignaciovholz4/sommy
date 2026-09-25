<?php

namespace App\Services\CreativeStudio\PromptEngine;

use Illuminate\Support\Facades\DB;

/**
 * Arma el prompt final que se manda a Gemini, combinando: estilo de marca
 * fijo (Mi marca) + fidelidad de producto + encuadre pedido + indicación
 * puntual del usuario + reglas negativas automáticas. Un solo lugar para
 * esta lógica: lo usan tanto el chat (ImagenIaService) como el modo Create.
 */
class PromptBuilder
{
    /** Presets de escena base. */
    public const ESCENAS = [
        'dormitorio' => 'un dormitorio real luminoso de estilo escandinavo, luz natural de manana entrando por una ventana, ropa de cama blanca y celeste pastel, madera clara, plantas',
        'noche'      => 'un dormitorio premium de noche, iluminacion calida tenue de veladores, tonos azul profundo y madera oscura, atmosfera serena de hotel boutique',
        'minimal'    => 'un estudio fotografico minimalista con fondo liso en degrade celeste muy suave (#E0F2FE a #F8FAFC), sombra suave debajo del producto, estetica de catalogo premium',
        'familia'    => 'un dormitorio familiar calido y acogedor con luz de tarde, manta tejida, libros en la mesa de luz, sensacion hogarena argentina',
        'estudio'    => 'un estudio fotografico publicitario profesional, fondo neutro, iluminacion controlada de producto',
        'fabrica'    => 'un entorno de fabrica textil prolijo y luminoso, telas y materiales de colchoneria a la vista, sensacion de control de calidad artesanal',
    ];

    /** Modo Create: objetivo de la pieza. */
    public const OBJETIVOS = [
        'producto'      => '',
        'oferta'        => 'Enfoque comercial de oferta: dejar espacio visual claro y limpio para destacar despues un precio y una llamada a la accion, sin saturar la escena.',
        'lifestyle'     => 'Enfoque de estilo de vida: la escena debe transmitir una rutina cotidiana real y cercana, no una foto de catalogo fria.',
        'fabricacion'   => 'Enfoque de fabricacion: transmitir calidad y control de produccion, sin inventar procesos ni maquinaria que no se hayan indicado.',
        'educativo'     => 'Enfoque educativo/informativo: composicion limpia y ordenada, pensada para agregar despues un texto explicativo.',
        'combo'         => 'Enfoque de combo: la composicion debe dejar lugar para insinuar el conjunto completo (colchon + base + almohadas), sin inventar productos que no esten en la foto real.',
        'institucional' => 'Enfoque institucional de marca: tono sobrio, sin urgencia comercial.',
        'comparativa'   => 'Enfoque comparativo: composicion clara tipo ficha tecnica, sin elementos decorativos que distraigan.',
        'testimonial'   => 'Enfoque testimonial: escena hogarena creible, como para acompanar la opinion real de un cliente.',
        'lanzamiento'   => 'Enfoque de lanzamiento: composicion con protagonismo maximo del producto, tono de novedad sin urgencia falsa.',
    ];

    /** Modo Create: intensidad comercial (nunca afecta al producto, solo el tratamiento visual/espacio para textos). */
    public const INTENSIDADES = [
        'institucional' => 'Tratamiento visual sobrio e institucional: bajo contraste, sin elementos promocionales, prioridad a la calma visual.',
        'equilibrado'   => 'Tratamiento visual equilibrado entre estetica de marca y necesidad comercial.',
        'comercial'     => 'Tratamiento visual comercial: mas contraste y mas espacio reservado para precio y llamada a la accion.',
        'promo_fuerte'  => 'Tratamiento visual de promocion fuerte: alto contraste y espacio prominente reservado para precio/CTA destacado (el texto y los colores de oferta se agregan despues en el editor, no dentro de esta imagen).',
    ];

    public const ILUMINACION = [
        'natural_soft' => 'luz natural suave',
        'morning'      => 'luz calida de manana',
        'golden_hour'  => 'luz dorada de atardecer',
        'studio'       => 'iluminacion de estudio fotografico controlada',
        'dark_premium' => 'iluminacion oscura premium, tenue y calida',
        'night'        => 'luz nocturna tenue de veladores',
        'soft_window'  => 'luz suave entrando por una ventana',
    ];

    public const CAMARA = [
        'frontal'         => 'camara frontal',
        '3-4-izquierda'   => 'camara en tres cuartos desde la izquierda',
        '3-4-derecha'     => 'camara en tres cuartos desde la derecha',
        'lateral'         => 'camara lateral',
        'close-up'        => 'primer plano de detalle del producto',
        'top-detail'      => 'vista superior de detalle',
        'low-angle'       => 'angulo bajo',
    ];

    public const COMPOSICION = [
        'product-left-copy-right' => 'composicion con el producto a la izquierda y espacio libre para texto a la derecha',
        'copy-left-product-right' => 'composicion con espacio libre para texto a la izquierda y el producto a la derecha',
        'center-hero'              => 'composicion con el producto centrado como protagonista absoluto',
        'product-bottom'           => 'composicion con el producto en la parte inferior y espacio libre para texto arriba',
        'full-product'             => 'composicion con el producto ocupando todo el encuadre',
        'split'                    => 'composicion dividida 50/50 entre producto y espacio libre',
    ];

    public const DENSIDAD = [
        'minimal'   => 'ambientacion minimalista, con muy pocos elementos decorativos, foco total en el producto',
        'normal'    => 'ambientacion normal, con decoracion moderada',
        'decorated' => 'ambientacion decorada, con varios elementos de interiorismo, sin quitarle protagonismo al colchon',
    ];

    public const PERSONAS = [
        'ninguna'   => '',
        'una'       => 'Incluir una persona real de aspecto argentino en la escena, de forma natural, sin posar artificialmente.',
        'pareja'    => 'Incluir una pareja real de aspecto argentino en la escena, interactuando de forma natural.',
        'manos'     => 'Mostrar solo manos o una interaccion parcial de una persona con el producto, sin mostrar el rostro.',
        'lifestyle' => 'Incluir una persona interactuando naturalmente con el producto en un momento cotidiano, sin posar artificialmente.',
    ];

    public const ZONAS_TEXTO = [
        'ninguna'            => '',
        'superior-izquierda' => 'Reservar la zona superior izquierda del encuadre libre de elementos importantes del producto, para poder superponer texto (titular/precio/CTA) ahi despues.',
        'superior-derecha'   => 'Reservar la zona superior derecha del encuadre libre de elementos importantes del producto, para poder superponer texto (titular/precio/CTA) ahi despues.',
        'inferior-izquierda' => 'Reservar la zona inferior izquierda del encuadre libre de elementos importantes del producto, para poder superponer texto (titular/precio/CTA) ahi despues.',
        'inferior-derecha'   => 'Reservar la zona inferior derecha del encuadre libre de elementos importantes del producto, para poder superponer texto (titular/precio/CTA) ahi despues.',
        'centro'             => 'Reservar una franja central del encuadre libre de elementos importantes del producto, para poder superponer texto ahi despues.',
    ];

    protected static function estiloMarca(): string
    {
        $estilo = DB::table('publicaciones_ajustes')->value('estilo_imagen');

        return trim((string) $estilo) !== ''
            ? trim($estilo)
            : 'fotografia comercial realista de alta calidad, colores serenos (azules, celestes, blancos), sin personas';
    }

    /**
     * Instrucción para que Gemini dibuje DIRECTAMENTE el banner/sticker promocional
     * con el texto real (nunca inventado) sobre la escena, imitando el estilo de las
     * imágenes de referencia (cinta diagonal, tipografía gruesa con contorno, colores
     * promocionales). Reemplaza la regla de "no agregar texto" cuando se pide.
     *
     * @param array $producto ficha real (mapProducto/mapCombo): nombre, precio, precioFinal, descuento, altura, firmeza...
     */
    protected static function bloqueGraficaPromocional(array $producto, bool $conPrecio, ?string $headline = null, bool $conReferencias = false): string
    {
        $headline = trim((string) $headline) !== '' ? trim($headline) : mb_strtoupper((string) ($producto['nombre'] ?? 'SOMMY'));
        $piezas = ['Titular en letras grandes: "' . $headline . '"'];

        if ($conPrecio) {
            $descuento = (float) ($producto['descuento'] ?? 0);
            $precioFinal = '$' . number_format((float) ($producto['precioFinal'] ?? 0), 0, ',', '.');
            if ($descuento > 0) {
                $piezas[] = 'callout de descuento: "' . round($descuento) . '% OFF"';
                $piezas[] = 'precio final: "' . $precioFinal . '"';
            } else {
                $piezas[] = 'precio: "' . $precioFinal . '"';
            }
        }

        $specs = array_filter([
            !empty($producto['altura']) ? $producto['altura'] . ' CM DE DESCANSO' : null,
            !empty($producto['firmeza']) ? 'FIRMEZA ' . mb_strtoupper((string) $producto['firmeza']) : null,
        ]);
        if ($specs) {
            $piezas[] = 'dato técnico real: "' . implode(' · ', $specs) . '"';
        }

        $listado = implode('. ', $piezas) . '.';

        return 'ADEMAS: esto no es una foto de producto pelada, es una PIEZA PUBLICITARIA TERMINADA lista para publicar en Instagram. '
            . 'Incluí directamente en la imagen un banner o cinta diagonal tipo sticker/ribbon (rotado levemente, no perfectamente horizontal), '
            . 'con tipografía gruesa, redondeada, tipo display/comic con contorno grueso de color contrastante, en los colores promocionales de la marca '
            . '(amarillo eléctrico y fucsia, sobre fondo azul noche o blanco)'
            . ($conReferencias ? ', imitando EXACTAMENTE el estilo gráfico (forma de cinta, grosor de letra, composición) de las imágenes de referencia adjuntas al final. ' : '. ')
            . 'Escribí en la imagen, EXACTAMENTE como está acá (sin inventar, sin cambiar ni un número ni una palabra): ' . $listado . ' '
            . 'No agregues ningún otro precio, porcentaje o dato que no esté en esta lista. '
            . 'PROHIBIDO dibujar ningún logo, isotipo ni marca de agua: NO escribas la palabra "Sommy" ni ninguna variante en ningún lugar de la imagen '
            . '(ni como logo, ni como texto suelto, ni integrada al banner) — el logo real se superpone después por separado, en software, '
            . 'con el archivo de marca real. Dejá un espacio limpio y libre de elementos importantes en la esquina superior izquierda para superponerlo.';
    }

    /**
     * Instrucción de fidelidad de producto: dice explícitamente que la PRIMERA
     * imagen adjunta es la foto real (única fuente válida del colchón), para
     * que Gemini no la confunda con las imágenes de referencia de estilo que
     * se adjuntan después.
     */
    protected static function fidelidadProducto(int $cantidadFidelidad = 1): string
    {
        $orden = $cantidadFidelidad > 1
            ? "Las PRIMERAS {$cantidadFidelidad} imagenes adjuntas a este mensaje son FOTOS REALES de productos Sommy (el colchon, posiblemente distintos angulos del mismo colchon, y/o la base/sommier real): "
                . 'son las UNICAS fuentes validas de producto para el resultado.'
            : 'La PRIMERA imagen adjunta a este mensaje es una FOTO REAL de un colchon Sommy: es el UNICO producto que puede aparecer en el resultado.';

        return 'Foto publicitaria profesional. ' . $orden . ' Colocar ESE/ESOS producto(s) real(es) (manteniendo EXACTAMENTE su forma, '
            . 'tela, costuras, etiqueta, pillow top y colores tal cual se ven en esas fotos, sin rediseñarlos ni modificarlos) en '
            . ($cantidadFidelidad > 1 ? '' : 'sobre una base o sommier en ');
    }

    /**
     * Aclaración obligatoria cuando hay imágenes de referencia adjuntas: son SOLO
     * guía de estilo gráfico de campaña, nunca de producto. Sin esto, Gemini tiende
     * a mezclar el colchón real con el que aparece en las fotos de referencia.
     */
    protected static function bloqueReferenciasEstilo(bool $conProducto, int $cantidadFidelidad = 1): string
    {
        $referenciaPrevia = $cantidadFidelidad > 1 ? 'las fotos reales de producto' : 'la foto del producto';
        $ordenProducto = $cantidadFidelidad > 1 ? 'de las primeras imagenes adjuntas' : 'de la primera imagen adjunta';

        $productoClause = $conProducto
            ? " El unico/s colchon/es real/es que puede/n aparecer en el resultado final es/son el/los {$ordenProducto}: "
                . 'cualquier colchon, cama o mueble que se vea en las imagenes de referencia tiene que ser ignorado por completo '
                . '(no copiar su forma, tela, color, textura ni diseño), esas fotos no son productos Sommy.'
            : '';

        return " Las imagenes adjuntas DESPUES de {$referenciaPrevia} (si las hay) son SOLO una referencia grafica de estilo de "
            . 'campaña (forma del banner/cinta, tipografia, paleta de colores, composicion del texto), no son productos Sommy ni '
            . 'le pertenecen a la marca.' . $productoClause;
    }

    protected static function orientacion(string $formato): string
    {
        return match ($formato) {
            'story' => 'Encuadre vertical 9:16 (historia de Instagram/Facebook), con aire libre arriba y abajo para superponer textos.',
            'ml'    => 'Encuadre cuadrado 1:1 (ficha de MercadoLibre), con aire en el tercio superior e inferior para superponer textos.',
            default => 'Encuadre vertical 4:5 (post de feed de Instagram/Facebook), con aire en el tercio superior e inferior para superponer textos.',
        };
    }

    /**
     * Prompt para una escena CON producto real (fidelidad obligatoria).
     *
     * @param string|null $extra indicación puntual del usuario o de un preset (escena/cámara/composición/text safe zone)
     * @param bool $conReferencias si hay imágenes de referencia de estilo adjuntas
     */
    public static function paraProducto(string $formato, string $escena = 'dormitorio', ?string $extra = null, bool $conReferencias = false, ?string $promptLibre = null, ?array $producto = null, bool $conPrecio = false, ?string $headline = null, int $cantidadFidelidad = 1): string
    {
        if (trim((string) $promptLibre) !== '') {
            $cuerpo = trim($promptLibre);
        } else {
            $cuerpo = (self::ESCENAS[$escena] ?? self::ESCENAS['dormitorio']) . '. '
                . 'Estilo de la marca: ' . self::estiloMarca()
                . (trim((string) $extra) !== '' ? ' ' . trim($extra) : '');
        }

        $prompt = self::fidelidadProducto($cantidadFidelidad)
            . $cuerpo . ' '
            . self::orientacion($formato) . ' '
            . NegativeRulesBuilder::paraProducto();

        $reglasExtra = NegativeRulesBuilder::extra();
        if ($reglasExtra !== '') {
            $prompt .= ' ' . $reglasExtra;
        }

        $prompt .= $producto !== null
            ? ' ' . self::bloqueGraficaPromocional($producto, $conPrecio, $headline, $conReferencias)
            : ' IMPORTANTE: no agregar ningun texto, logo, marca de agua ni precio a la imagen.';

        if ($conReferencias) {
            $prompt .= self::bloqueReferenciasEstilo($producto !== null, $cantidadFidelidad)
                . ' Imitá unicamente el estilo visual general (paleta de color, iluminacion, composicion, mood/atmosfera'
                . ($producto !== null ? ' y el estilo del banner/texto' : '') . ') de esas imagenes de referencia.';
        }

        return $prompt;
    }

    /**
     * Prompt del modo Create: combina objetivo + intensidad comercial + escena +
     * densidad + iluminacion + camara + composicion + zona de texto + personas,
     * siempre con la misma fidelidad de producto y reglas negativas que el chat.
     *
     * @param array{objetivo?:string,intensidad?:string,escena?:string,densidad?:string,iluminacion?:string,camara?:string,composicion?:string,zona_texto?:string,personas?:string} $opciones
     */
    public static function paraProductoStudio(string $formato, array $opciones, bool $conReferencias = false, ?array $producto = null, bool $conPrecio = false, ?string $headline = null, int $cantidadFidelidad = 1): string
    {
        $escena = $opciones['escena'] ?? 'dormitorio';

        $fragmentos = array_filter([
            (self::ESCENAS[$escena] ?? self::ESCENAS['dormitorio']) . '.',
            'Estilo de la marca: ' . self::estiloMarca() . '.',
            self::OBJETIVOS[$opciones['objetivo'] ?? 'producto'] ?? null,
            self::INTENSIDADES[$opciones['intensidad'] ?? 'equilibrado'] ?? null,
            self::DENSIDAD[$opciones['densidad'] ?? 'normal'] ?? null,
            isset(self::ILUMINACION[$opciones['iluminacion'] ?? '']) ? 'Iluminacion: ' . self::ILUMINACION[$opciones['iluminacion']] . '.' : null,
            isset(self::CAMARA[$opciones['camara'] ?? '']) ? 'Camara: ' . self::CAMARA[$opciones['camara']] . '.' : null,
            isset(self::COMPOSICION[$opciones['composicion'] ?? '']) ? ucfirst(self::COMPOSICION[$opciones['composicion']]) . '.' : null,
            self::ZONAS_TEXTO[$opciones['zona_texto'] ?? 'ninguna'] ?? null,
            self::PERSONAS[$opciones['personas'] ?? 'ninguna'] ?? null,
        ]);

        $cuerpo = implode(' ', $fragmentos);

        $prompt = self::fidelidadProducto($cantidadFidelidad)
            . $cuerpo . ' '
            . self::orientacion($formato) . ' '
            . NegativeRulesBuilder::paraProducto();

        $reglasExtra = NegativeRulesBuilder::extra();
        if ($reglasExtra !== '') {
            $prompt .= ' ' . $reglasExtra;
        }

        $prompt .= $producto !== null
            ? ' ' . self::bloqueGraficaPromocional($producto, $conPrecio, $headline, $conReferencias)
            : ' IMPORTANTE: no agregar ningun texto, logo, marca de agua ni precio a la imagen.';

        if ($conReferencias) {
            $prompt .= self::bloqueReferenciasEstilo($producto !== null, $cantidadFidelidad)
                . ' Imitá unicamente el estilo visual general (paleta de color, iluminacion, composicion, mood/atmosfera'
                . ($producto !== null ? ' y el estilo del banner/texto' : '') . ') de esas imagenes de referencia.';
        }

        return $prompt;
    }

    /** Prompt para contenido de marca SIN producto puntual (estilo de vida, informativo). */
    public static function sinProducto(string $formato, string $instrucciones, bool $conReferencias = false): string
    {
        $prompt = 'Foto de contenido de marca para redes sociales de Sommy (fabrica argentina de colchones), '
            . 'SIN mostrar ningun producto puntual ni logo dentro de la escena. '
            . trim($instrucciones) . '. Estilo de la marca: ' . self::estiloMarca() . ' '
            . self::orientacion($formato)
            . ' Fotografia realista, personas reales de aspecto argentino si corresponde, nada de texto ni marca de agua en la imagen.';

        if ($conReferencias) {
            $prompt .= ' Ademas, imita el estilo visual general (paleta de color, iluminacion, composicion, mood/atmosfera) '
                . 'de las imagenes de referencia adjuntas al final, sin copiar literalmente su contenido.';
        }

        return $prompt;
    }
}
