/**
 * Valida "Authorization: Bearer <BRIDGE_TOKEN>" contra el token compartido con Laravel.
 */
function requireBridgeToken(req, res, next) {
  const expected = process.env.BRIDGE_TOKEN;
  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : req.query.token;

  if (!expected || token !== expected) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
}

module.exports = { requireBridgeToken };
