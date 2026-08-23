const { Router } = require('express');
const { requireBridgeToken } = require('../middleware/auth');
const { getSocket, isConnected } = require('../../baileys/session');
const { simulateTyping } = require('../../baileys/presence');
const { waitForSlot } = require('../../baileys/rateLimiter');

const router = Router();

// Laravel (SendWhatsAppMessage/WhatsAppBaileysService) pide el envio aca.
// El delay de "escribiendo..." y el rate-limit se aplican siempre aca adentro,
// sin importar que dispare el envio del lado de Laravel.
router.post('/send', requireBridgeToken, async (req, res) => {
  if (!isConnected()) {
    return res.status(503).json({ error: 'WhatsApp no conectado (escanear QR en /qr)' });
  }

  const { to, body, media } = req.body || {};
  if (!to || (!body && !media)) {
    return res.status(422).json({ error: 'Faltan "to" y "body" o "media"' });
  }

  try {
    await waitForSlot();

    const sock = getSocket();
    await simulateTyping(sock, to, (body || '').length || 60);

    // Media nativa: {type: image|video|audio|document, url, caption?, filename?, mimetype?}
    // Baileys descarga el archivo desde la URL (debe ser accesible desde el server).
    let content;
    if (media && media.url) {
      const caption = media.caption || body || undefined;
      switch (media.type) {
        case 'image':
          content = { image: { url: media.url }, caption };
          break;
        case 'video':
          content = { video: { url: media.url }, caption };
          break;
        case 'audio':
          content = { audio: { url: media.url }, mimetype: media.mimetype || 'audio/mpeg' };
          break;
        default:
          content = {
            document: { url: media.url },
            fileName: media.filename || 'archivo',
            mimetype: media.mimetype || 'application/octet-stream',
            caption,
          };
      }
    } else {
      content = { text: body };
    }

    const result = await sock.sendMessage(to, content);

    // El audio no lleva caption: si venia texto junto al audio, va aparte
    if (media && media.type === 'audio' && (media.caption || body)) {
      await sock.sendMessage(to, { text: media.caption || body });
    }

    res.json({ id: result?.key?.id ?? null });
  } catch (err) {
    console.error('[send] error enviando mensaje', err.message);
    res.status(502).json({ error: err.message });
  }
});

module.exports = router;
