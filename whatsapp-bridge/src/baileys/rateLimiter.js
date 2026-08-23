const { sleep } = require('./presence');

const MIN_GAP_MS = 2000; // separacion minima entre dos envios cualquiera
const MAX_PER_MINUTE = 20; // tope global, generoso para un bot 100% reactivo de bajo volumen

let lastSendAt = 0;
const sentTimestamps = [];

/**
 * Bloquea hasta que sea seguro mandar el proximo mensaje, respetando un
 * espaciado minimo global y un tope por minuto. No es una cola persistente:
 * alcanza para el volumen de un bot reactivo (nunca manda masivos/broadcasts).
 */
async function waitForSlot() {
  const now = Date.now();
  const cutoff = now - 60000;
  while (sentTimestamps.length && sentTimestamps[0] < cutoff) {
    sentTimestamps.shift();
  }

  if (sentTimestamps.length >= MAX_PER_MINUTE) {
    const waitMs = sentTimestamps[0] + 60000 - now;
    if (waitMs > 0) await sleep(waitMs);
  }

  const gap = Date.now() - lastSendAt;
  if (gap < MIN_GAP_MS) {
    await sleep(MIN_GAP_MS - gap);
  }

  lastSendAt = Date.now();
  sentTimestamps.push(lastSendAt);
}

module.exports = { waitForSlot };
