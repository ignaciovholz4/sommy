# whatsapp-bridge

Bridge Node.js que conecta un numero real de WhatsApp (WhatsApp Web / multi-device,
via [Baileys](https://github.com/WhiskeySockets/Baileys)) con la bandeja de Holzdream/Sommy,
sin pasar por la Cloud API oficial de Meta. Pensado para un bot 100% reactivo:
nunca inicia conversaciones ni manda mensajes masivos.

## Como funciona

- Mantiene una sesion de WhatsApp Web persistida en `auth_session/` (se genera al escanear el QR).
- Expone un servidor HTTP chico para que Laravel le pida enviar mensajes (`POST /send`)
  y para mostrar el QR de vinculacion como imagen (`GET /qr`).
- Cuando llega un mensaje de un cliente, hace `POST` al webhook de Laravel
  (`/api/whatsapp/baileys/webhook`) con el texto (y el adjunto, si tiene, ya descargado).
- Antes de mandar cualquier respuesta, simula "escribiendo..." con un delay proporcional
  al mensaje y respeta un rate-limit — medidas para reducir el riesgo de que Meta banee
  el numero por comportamiento de bot.

## Uso local (desarrollo)

```bash
cd whatsapp-bridge
npm install
cp .env.example .env   # completar LARAVEL_URL con la URL local (ej. http://holzdream.test)
                        # y BRIDGE_TOKEN/INBOUND_TOKEN iguales a los del .env de Laravel
npm start
```

Abrir `http://localhost:3300/qr?token=<BRIDGE_TOKEN>` en el navegador y escanear con
WhatsApp Business (Dispositivos vinculados > Vincular un dispositivo) desde el numero real
del negocio. Una vez conectado, `/qr` devuelve 204 y ya no hace falta volver a escanear
(salvo que se cierre sesion desde el celular).

En el `.env` de Laravel, activar la cuenta correspondiente con `provider = 'baileys'`
en `wa_accounts` y cargar `WHATSAPP_BRIDGE_URL`/`WHATSAPP_BRIDGE_TOKEN`/`WHATSAPP_BRIDGE_INBOUND_TOKEN`.

## Despliegue en produccion (hosting compartido)

1. Ver si el plan de Hostinger tiene la seccion "Node.js App" en hPanel. Si la tiene,
   crear una app apuntando a esta carpeta (`whatsapp-bridge/`), startup file `src/index.js`,
   y cargar ahi las variables de entorno reales.
2. Como Hostinger corre esto sobre Passenger (recicla procesos idle), agregar un
   **Cron Job** de hPanel cada 3-5 minutos pegandole a `GET /health` para mantenerlo activo.
   Esto es una mitigacion, no una garantia.
3. Si en la practica se ven desconexiones frecuentes que fuerzan re-escanear el QR
   (riesgo real de baneo por relogueos repetidos), no insistir: mover el bridge a un
   VPS barato (Hetzner/Contabo/DigitalOcean, ~USD 4-6/mes) corriendo
   `pm2 start src/index.js --name wa-bridge`, sin cambios de codigo — solo actualizar
   `WHATSAPP_BRIDGE_URL` en el `.env` de Laravel.
4. `auth_session/` tiene que vivir en una ruta persistente entre deploys (no se debe
   borrar al actualizar el codigo, o habria que volver a escanear el QR cada vez).
