const { Router } = require('express');
const { isConnected } = require('../../baileys/session');

const router = Router();

// Sin auth a proposito: un cron de keep-alive (hPanel) le pega a esto
// seguido para evitar que el hosting recicle el proceso por inactividad.
router.get('/health', (req, res) => {
  res.json({ status: 'ok', connected: isConnected() });
});

module.exports = router;
