# Plan de construcción — Plataforma Sommy

Hoja de ruta para pasar de "tienda online" a "central de ventas omnicanal de colchones".
Actualizado: julio 2026.

---

## ✅ Ya construido (base)

- Tienda con identidad Sommy: hero con video, categorías con imágenes, buscador guiado
  (tipo/plazas/firmeza), fichas técnicas de colchón con medidor de firmeza.
- Carrito con cuentas de cliente (registro/login obligatorio para comprar).
- Checkout con apertura automática de WhatsApp + Mercado Pago integrado (falta credencial).
- Panel de gestión: pedidos con etapas y alerta de nuevos, stock multisucursal,
  tablero CEO (facturación, margen, mix de medidas, stock crítico).
- Recuperación de contraseña funcionando. Deploy en Hostinger en marcha.

---

## Etapa 0 — Salir a producción en serio (esta semana)

| # | Tarea | Quién |
|---|---|---|
| 0.1 | Dominio propio + SSL + email del dominio (hPanel) | Vos |
| 0.2 | `.env` de producción: `APP_DEBUG=false`, `https://`, SMTP real | Vos (guía: DEPLOY_HOSTINGER.md) |
| 0.3 | Credenciales de Mercado Pago en `.env` | Vos (developers.mercadopago.com.ar) |
| 0.4 | Fotos reales de los colchones (fondo blanco, mínimo 1 por producto) | Vos |
| 0.5 | Páginas legales: botón de arrepentimiento (obligatorio por ley AR), términos y condiciones, devoluciones | Claude |
| 0.6 | Verificar facturación electrónica ARCA | Ambos |
| 0.7 | Backups automáticos de la base en Hostinger | Vos (activar) |

**Resultado**: tienda legal, cobrando online, con mails funcionando.

## Etapa 1 — Estudio de publicaciones (el multiplicador de ventas)

| # | Tarea | Quién |
|---|---|---|
| 1.1 | ✅ HECHO — Módulo **Tienda → Publicaciones**: producto + formato (ML 1:1, post 1:1, historia 9:16) con **plantillas de marca** estilo claro/noche, precio y specs — sin IA, gratis | Claude |
| 1.2 | Key de Google Gemini (aistudio.google.com) en `.env` | Vos |
| 1.3 | **Generación con IA (Nano Banana)**: tu foto fondo blanco → colchón ambientado en dormitorio, presets de escena con estética Sommy | Claude |
| 1.4 | ✅ HECHO (versión sin IA) — **Textos automáticos**: título ML (máx. 60), descripción larga, caption IG/FB desde la ficha técnica; la versión con IA se suma con la key | Claude |
| 1.5 | Historial por producto + marcar dónde se publicó (ML ✓ / IG ✓ / FB ✓) | Claude |

**Resultado**: de cada producto salen todas las piezas para todos los canales en un clic.

## Etapa 2 — Centralización de canales

| # | Tarea | Quién |
|---|---|---|
| 2.1 | ✅ HECHO — Campo **origen** en pedidos + carga manual de pedidos de otros canales (`/orders/manual`) + reporte "Pedidos por canal" en el tablero CEO | Claude |
| 2.2 | **Feed de catálogo Meta** (URL XML): productos en la tienda de Instagram/Facebook y catálogo de WhatsApp, sincronizados solos | Claude |
| 2.3 | Feed **Google Shopping** (listados gratuitos) | Claude |
| 2.4 | App en developers.mercadolibre.com.ar | Vos |
| 2.5 | **Integración MercadoLibre**: ventas de ML entran como pedidos, descuentan stock, preguntas de ML en bandeja del panel, botón "Publicar en ML" | Claude (proyecto grande) |
| 2.6 | WhatsApp Business con etiquetas + Meta Business Suite para DMs | Vos (sin código, gratis) |

**Resultado**: todos los pedidos y consultas de todos los canales, en un solo lugar.

## Etapa 3 — Conversión y retención

| # | Tarea | Quién |
|---|---|---|
| 3.1 | **Reseñas reales**: mail post-compra para calificar, estrellas en la ficha | Claude |
| 3.2 | **Variantes de medida** por producto (1 plaza a King con precio por medida) usando las combinaciones ya existentes | Claude + vos cargás medidas |
| 3.3 | SEO: sitemap.xml, metas OG, Search Console | Claude + vos (alta en Google) |
| 3.4 | Google Analytics + Meta Pixel | Claude (vos creás las cuentas) |
| 3.5 | **Carritos abandonados**: recordatorio por mail/WhatsApp | Claude |

**Resultado**: más conversión de las visitas que ya tenés y clientes que vuelven.

## Etapa 4 — Escalar

- WhatsApp Business **API** (conversaciones dentro del panel, confirmaciones automáticas).
- Integración logística (Andreani/OCA) para etiquetas y seguimiento.
- Publicación orgánica directa a IG/FB vía API de Meta (requiere aprobación de app).
- Monitoreo de uptime y alertas.

---

## Orden de ejecución acordado

1. **Etapa 0** en paralelo: vos los trámites (dominio, MP, key), Claude las páginas legales.
2. **Etapa 1** completa (Publicaciones es la prioridad elegida).
3. **2.1 + 2.2 + 2.3** (origen + feeds) — rápidos y de alto impacto.
4. **2.5 MercadoLibre** — el proyecto grande.
5. **Etapa 3** según resultados.

## Claves que hay que conseguir (todas van al `.env`, nunca al código)

- `MERCADOPAGO_ACCESS_TOKEN` / `MERCADOPAGO_PUBLIC_KEY` → developers.mercadopago.com.ar
- `GEMINI_API_KEY` → aistudio.google.com
- Credenciales app MercadoLibre → developers.mercadolibre.com.ar (cuando llegue 2.5)
- SMTP del dominio → hPanel → Correos
