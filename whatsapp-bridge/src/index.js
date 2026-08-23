require('dotenv').config();

const { createServer } = require('./http/server');
const { start } = require('./baileys/session');

const PORT = process.env.PORT || 3300;

createServer().listen(PORT, () => {
  console.log(`[bridge] escuchando en http://127.0.0.1:${PORT}`);
});

start().catch((err) => {
  console.error('[baileys] error fatal al iniciar la sesion', err);
  process.exit(1);
});
