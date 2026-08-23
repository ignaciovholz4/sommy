const path = require('path');
const pino = require('pino');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
} = require('@whiskeysockets/baileys');

const { handleInboundMessage } = require('../laravel/webhookClient');

const AUTH_DIR = path.join(__dirname, '..', '..', 'auth_session');
const logger = pino({ level: 'warn' });

let sock = null;
let latestQr = null;
let connected = false;

// Backoff simple: evita relogueos en ciclo (WhatsApp trata las reconexiones
// agresivas como comportamiento sospechoso, justo lo que queremos evitar).
let reconnectDelayMs = 5000;
const MAX_RECONNECT_DELAY_MS = 60000;

async function start() {
  const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
  const { version } = await fetchLatestBaileysVersion();

  sock = makeWASocket({
    version,
    auth: state,
    logger,
    printQRInTerminal: false,
    syncFullHistory: false,
    markOnlineOnConnect: false,
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      latestQr = qr;
    }

    if (connection === 'open') {
      connected = true;
      latestQr = null;
      reconnectDelayMs = 5000;
      console.log('[baileys] conectado');
    }

    if (connection === 'close') {
      connected = false;
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const loggedOut = statusCode === DisconnectReason.loggedOut;

      if (loggedOut) {
        console.log('[baileys] sesion cerrada (logout) — hace falta escanear un QR nuevo en /qr');
        return;
      }

      console.log(`[baileys] conexion cerrada, reintentando en ${reconnectDelayMs}ms`);
      setTimeout(start, reconnectDelayMs);
      reconnectDelayMs = Math.min(reconnectDelayMs * 2, MAX_RECONNECT_DELAY_MS);
    }
  });

  sock.ev.on('messages.upsert', ({ messages, type }) => {
    if (type !== 'notify') return;
    for (const msg of messages) {
      // 100% reactivo: nunca proceses tus propios mensajes salientes como si fueran entrantes
      if (msg.key.fromMe) continue;
      handleInboundMessage(sock, msg).catch((err) => {
        console.error('[baileys] error procesando mensaje entrante', err.message);
      });
    }
  });
}

function getSocket() {
  return sock;
}

function isConnected() {
  return connected;
}

function getLatestQr() {
  return latestQr;
}

module.exports = { start, getSocket, isConnected, getLatestQr };
