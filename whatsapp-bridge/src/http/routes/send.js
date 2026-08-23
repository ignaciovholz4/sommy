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

  const { to, body } = req.body || {};
  if (!to || !body) {
    return res.status(422).json({ error: 'Faltan "to" o "body"' });
  }

  try {
    await waitForSlot();

    const sock = getSocket();
    await simulateTyping(sock, to, body.length);

    const result = await sock.sendMessage(to, { text: body });
    res.json({ id: result?.key?.id ?? null });
  } catch (err) {
    console.error('[send] error enviando mensaje', err.message);
    res.status(502).json({ error: err.message });
  }
});

module.exports = router;
