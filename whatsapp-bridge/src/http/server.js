const express = require('express');

function createServer() {
  const app = express();
  app.use(express.json());

  app.use(require('./routes/health'));
  app.use(require('./routes/qr'));
  app.use(require('./routes/send'));

  return app;
}

module.exports = { createServer };
