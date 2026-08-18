# Handoff: Identidad de marca Sommy

## Overview
Identidad visual completa de **Sommy**, colchonería. Incluye el manual de marca (brandbook de 8 páginas, apaisado, imprimible) y el archivo de exploración de logotipos. El objetivo en el ecommerce: aplicar esta identidad a la tienda (header, botones, fichas de producto, banners, footer).

## About the Design Files
Los archivos de este paquete son **referencias de diseño creadas en HTML** — no código de producción. La tarea es **recrear esta identidad en el entorno del ecommerce existente** (tema de Shopify/Woo/Tienda Nube, React, etc.) usando sus patrones y librerías. No copiar el HTML tal cual.

## Fidelity
**Alta fidelidad (hifi).** Colores, tipografías, espaciados y reglas son finales y salen del manual oficial de la marca.

## El logotipo
- Archivo maestro: `assets/sommy-logo.png` (wordmark serif "Sommy" azul noche + pluma con destello). Usarlo SIEMPRE tal cual — nunca recomponer el nombre con una fuente.
- Fondo: solo blancos/claros (#F8FAFC, #E0F2FE). Sobre fondos oscuros o fotos: montar el logo en la "placa almohada" (cápsula blanca border-radius:999px con sombra suave `0 10px 30px rgba(27,43,90,.10)`).
- Tamaño mínimo: 120 px de ancho digital. Área de respeto: la altura de la "S" en los 4 lados.
- Prohibido: estirar, rotar, sombras duras, colores fuera de paleta, separar la pluma del texto.

## Design Tokens

### Colores (paleta oficial)
| Token | Hex | Uso |
|---|---|---|
| Azul Sommy (principal) | #1B2B5A | Textos de marca, fondos noche, botones primarios |
| Azul Confort | #2563EB | Acentos con energía, links, hover |
| Verde Agua | #0EA5E9 | Detalles, pluma, micro-acentos |
| Brisa Suave | #E0F2FE | Fondos de sección, cards suaves |
| Blanco Algodón | #F8FAFC | Base de todo el sitio |
| Borde neutro | #E7EAF2 | Bordes de cards y divisores |
| Texto secundario | #5D6884 | Cuerpo de texto |
| Texto terciario | #8A93AD | Labels, captions |

Proporción: 75% blanco/brisa · 20% Azul Sommy · 5% acentos. Prohibido: neón, rojos/amarillos de oferta, negro puro (usar #1B2B5A).

### Tipografía
- **Lora** (Google Fonts, serif) — voz de marca: titulares, precios grandes, frases. Pesos 500/600/700. Itálica solo para la firma verbal.
- **Poppins** (Google Fonts) — información: cuerpo, fichas técnicas, formularios. Pesos 300/400/500.
- Jerarquía: titular Lora 600 · bajada Poppins 400 · dato/precio Lora 700.
- Interlínea 1.2 titulares, 1.7 texto largo.

### Radios y sombras
- Cards: 16–20 px. Placa almohada / botones pill: 999 px.
- Sombra única permitida: `0 10px 30px rgba(27,43,90,.10)` (suave). Sin sombras duras ni 3D.

## Elementos característicos (usar en el ecommerce)
1. **Trazo pluma**: curva SVG fina que subraya precios/titulares, termina en destello de 8 puntas. Stroke #1B2B5A 2–3px + eco #0EA5E9 1.6px.
2. **Destellos ✦**: viñetas y acentos. Máximo 3 por vista. Colores #E0F2FE/#0EA5E9/blanco sobre azul noche.
3. **Placa almohada**: cápsula blanca con sombra suave — así vive el logo sobre fotos de producto (hero banners, cards).

## Tono de voz
Cálido, pacífico, reconfortante y profesional. Voseo argentino, frases cortas, precio siempre con total. Firma verbal: *"Liviano como una pluma."* Promesa: *"El arte del descanso liviano, sereno y confortable."*
Prohibido: mayúsculas gritadas, "¡¡LIQUIDACIÓN!!", promesas médicas.

## Plantillas de publicación (referencia para banners del sitio)
Ver página 07 del brandbook:
- **A** — foto + placa almohada centrada abajo con logo y firma.
- **B** — ficha azul noche: header logo, foto, nombre de producto + precio en Lora.
- **C** — foto con velo degradé `rgba(27,43,90,0) → rgba(27,43,90,.92)` y titular blanco + trazo pluma.

## Files
- `Brandbook Sommy.dc.html` — manual de marca completo (8 páginas). Abrir en navegador.
- `Logo Sommy.dc.html` — exploración histórica de logotipos (contexto, no vigente).
- `assets/sommy-logo.png` — logotipo maestro oficial.
- `doc-page.js`, `image-slot.js` — runtime de los HTML (necesarios para abrirlos).

Nota: los HTML usan un runtime propio (`support.js` referenciado por los .dc.html) — si no renderizan fuera de la herramienta, usar este README + el PNG del logo como fuente de verdad; todos los valores están documentados arriba.
