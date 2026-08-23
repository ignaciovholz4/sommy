const { Router } = require('express');
const QRCode = require('qrcode');
const { requireBridgeToken } = require('../middleware/auth');
const { getLatestQr, isConnected } = require('../../baileys/session');

const router = Router();

// Imagen PNG del QR vigente, para vincular el numero sin acceso a la consola del server.
router.get('/qr', requireBridgeToken, async (req, res) => {
  if (isConnected()) {
    return res.status(204).end(); // ya vinculado, no hay QR que mostrar
  }

  const qr = getLatestQr();
  if (!qr) {
    return res.status(202).json({ status: 'esperando_qr' });
  }

  res.setHeader('Content-Type', 'image/png');
  QRCode.toFileStream(res, qr, { width: 300 });
});

module.exports = router;
