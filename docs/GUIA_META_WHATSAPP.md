# Guía: dar de alta WhatsApp + Messenger + Instagram (Meta)

> La bandeja del sistema es **multicanal**: WhatsApp, Facebook Messenger e Instagram
> Direct entran a la misma bandeja y los atiende el mismo bot y el mismo equipo.
> Los tres canales se configuran en **la misma app de Meta** (la que estás creando,
> ej. "sommy_bot"): WhatsApp es un producto de la app, y Messenger/Instagram otro.

Estos trámites los hacés vos en las webs de Meta. **Empezá ya por los pasos 1-3**: la
verificación del negocio puede demorar días o semanas, y mientras tanto el sistema se
desarrolla y prueba con el número de prueba gratuito que da Meta.

## 1. Crear Meta Business Manager
1. Entrá a https://business.facebook.com con el Facebook de la empresa (o creá uno).
2. Creá el negocio: nombre de la empresa, tu nombre, mail de la empresa.

## 2. Crear la app y activar WhatsApp (esto da el número de prueba)
1. Entrá a https://developers.facebook.com → "Mis apps" → "Crear app".
2. Tipo de app: **Negocios (Business)**. Vinculala al Business Manager del paso 1.
3. En el panel de la app, buscá el producto **WhatsApp** → "Configurar".
4. Meta te da automáticamente:
   - Un **número de prueba** (permite mandar mensajes a hasta 5 números que registres).
   - El **Phone number ID** y el **WhatsApp Business Account ID (WABA ID)** → copialos.
   - Un **token temporal** (dura 24 h, sirve para las primeras pruebas).
5. Agregá tu celular personal como número de destino de prueba y mandá el "hello world"
   desde la misma pantalla para verificar que todo funciona.

## 3. Verificar el negocio (el paso lento — arrancalo ya)
1. En business.facebook.com → Configuración del negocio → Centro de seguridad →
   **Verificación del negocio**.
2. Vas a necesitar: constancia de inscripción en AFIP/ARCA (CUIT), y un comprobante
   con nombre y domicilio del negocio (factura de servicio, estado bancario, etc.).
3. Meta puede pedir verificación por teléfono o dominio web. Puede tardar de 2 días a
   varias semanas.

## 4. Dar de alta el número real
> ⚠️ El número NO puede tener una cuenta de WhatsApp activa (ni común ni Business App).
> **Recomendación: comprá una línea nueva exclusiva para el sistema.** Si usás tu número
> actual, primero hay que borrar la cuenta de WhatsApp de ese número y se pierde el
> acceso desde el celular: todo pasa a manejarse desde el sistema.

1. En la app de developers → WhatsApp → API Setup → "Add phone number".
2. Cargás nombre visible del negocio, categoría, y verificás el número por SMS o llamada.
3. Copiá el nuevo **Phone number ID**.

## 5. Token permanente (System User)
El token temporal vence a las 24 h. Para producción:
1. business.facebook.com → Configuración del negocio → Usuarios → **Usuarios del sistema**.
2. Creá un usuario del sistema (rol Administrador).
3. "Agregar activos" → tu app → control total.
4. "Generar nuevo token" → seleccioná la app → permisos:
   `whatsapp_business_messaging` y `whatsapp_business_management` → sin vencimiento.
5. Guardá también el **App Secret** (developers.facebook.com → tu app → Configuración
   → Básica → "Clave secreta de la app").

## 6. Configurar el webhook (esto lo hacemos juntos)
1. developers.facebook.com → tu app → WhatsApp → Configuración → Webhook.
2. URL: `https://TU-DOMINIO/api/whatsapp/webhook`
   (para probar en local usamos un túnel: `ngrok http 80` o `cloudflared tunnel`).
3. Token de verificación: el valor de `WHATSAPP_VERIFY_TOKEN` del archivo `.env`.
4. Suscribite al campo **messages**.

## 7. Plantillas de mensaje (HSM)
Para escribirle a un cliente fuera de la ventana de 24 h después de su último mensaje,
Meta exige plantillas pre-aprobadas. En WhatsApp Manager → Herramientas → Plantillas,
creá y mandá a aprobación (suele tardar minutos a horas):
- `seguimiento_pedido` — "Hola {{1}}, te escribimos por tu pedido #{{2}}..."
- `envio_despachado` — "Hola {{1}}! Tu pedido #{{2}} fue despachado..."
- `reapertura_conversacion` — "Hola {{1}}, quedó pendiente tu consulta..."

## 8. Método de pago
En Business Manager → Facturación, cargá una tarjeta. Las conversaciones iniciadas por
la empresa (con plantilla) se cobran por conversación; responder dentro de las 24 h de
un mensaje del cliente es el caso barato/gratis. Revisá el pricing vigente para
Argentina antes del go-live.

## 9. Messenger (Facebook) e Instagram Direct

Requisitos: una **página de Facebook** del negocio y (para IG) una **cuenta de
Instagram profesional** vinculada a esa página (Instagram → Configuración →
Cuenta → Cambiar a cuenta profesional → vincular a la página).

1. En el panel de la app (developers.facebook.com) agregá el producto **Messenger**
   (o el caso de uso "Interactuar con los clientes en Messenger" que ya elegiste).
2. En Messenger → Configuración:
   - **Vinculá tu página de Facebook** → esto genera un **token de página**
     (Page Access Token). Copialo → va en `FB_PAGE_TOKEN` del `.env`.
   - Copiá el **ID de la página** → `FB_PAGE_ID`.
3. **Webhooks**: usá la MISMA URL del paso 6 (`https://TU-DOMINIO/api/whatsapp/webhook`)
   y el mismo verify token. Suscribí:
   - Objeto **page** (Messenger): campo `messages`.
   - Objeto **instagram**: campo `messages` (aparece cuando agregás Instagram a la app).
4. Para Instagram: en la configuración de Messenger de la app hay una sección
   **Instagram** → vinculá la cuenta profesional. El ID de la cuenta de IG va en
   `IG_ACCOUNT_ID` del `.env`.
5. Permisos que la app usa: `pages_messaging`, `pages_manage_metadata`,
   `instagram_basic`, `instagram_manage_messages`. **En modo desarrollo funcionan ya**
   para los administradores/testers de la app (podés probar escribiéndole a tu página
   desde tu propio Facebook/Instagram). Para el público general, Meta pide
   **App Review** de esos permisos + la verificación del negocio (paso 3): es un
   formulario donde mostrás cómo se usan (con la bandeja funcionando alcanza).

### Checklist si los DMs de Instagram NO llegan a la bandeja

En orden de más probable a menos (el sistema loguea todo webhook que entra; si el
log no menciona "instagram", Meta no está enviando nada):

1. **En la app de Instagram del celular**: Configuración → Privacidad → Mensajes →
   **"Permitir el acceso a los mensajes"** (herramientas conectadas) tiene que estar
   ACTIVADO. Es el paso que todo el mundo se saltea: sin esto Meta no manda ningún
   DM al webhook aunque todo lo demás esté perfecto.
2. **Webhooks de la app** (developers.facebook.com → tu app → Webhooks): tiene que
   existir la suscripción al objeto **Instagram** con el campo `messages` marcado —
   es una suscripción SEPARADA de la de WhatsApp y la de Page. Misma URL y mismo
   verify token que ya usás.
3. **La cuenta de IG es profesional y está vinculada** a la página de Facebook del
   `FB_PAGE_ID` (Instagram → Configuración → Cuenta → Herramientas profesionales).
4. **El token de página (`FB_PAGE_TOKEN`) tiene los permisos de IG**: regeneralo
   marcando `instagram_basic` + `instagram_manage_messages` (un token viejo generado
   solo para Messenger no sirve para IG).
5. **La página está suscripta a la app** con el campo messages: en Messenger →
   Configuración de la app, o vía API `POST /{page_id}/subscribed_apps` con
   `subscribed_fields=messages`.
6. **Modo desarrollo**: solo llegan DMs de usuarios con rol en la app
   (administrador/tester). Escribile a la cuenta desde TU Instagram (que es admin);
   si eso funciona y otros no, falta App Review para el público.
7. Mandá un DM de prueba y mirá **Webhooks → Actividad reciente** en el panel de la
   app: si Meta dice que envió y el servidor no lo recibió, revisá que la URL del
   webhook apunte al servidor correcto (producción, no localhost).

Reglas de estos canales (el sistema ya las aplica):
- Igual que WhatsApp, hay **ventana de 24 h** desde el último mensaje del cliente.
- Fuera de la ventana **no hay plantillas**: hay que esperar a que el cliente escriba.

## Qué me tenés que pasar a mí cuando lo tengas
Completar en el archivo `.env` (o pasármelo para que lo cargue):
```
WHATSAPP_TOKEN=            ← token (temporal para pruebas, permanente para producción)
WHATSAPP_PHONE_NUMBER_ID=  ← Phone number ID (del número de prueba primero)
WHATSAPP_WABA_ID=          ← WhatsApp Business Account ID
WHATSAPP_APP_SECRET=       ← clave secreta de la app
WHATSAPP_VERIFY_TOKEN=     ← ya tiene un valor por defecto, se usa en el paso 6
FB_PAGE_ID=                ← ID de la página de Facebook (paso 9)
FB_PAGE_TOKEN=             ← token de página (paso 9)
IG_ACCOUNT_ID=             ← ID de la cuenta profesional de Instagram (paso 9)
```
