function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Simula "escribiendo..." antes de mandar un mensaje: delay proporcional al
 * largo del texto, con piso y techo. Medida anti-baneo: un bot que responde
 * instantaneo a todo es justamente el patron que WhatsApp marca como sospechoso.
 */
async function simulateTyping(sock, jid, bodyLength) {
  const delayMs = Math.min(4000, Math.max(800, bodyLength * 30));
  try {
    await sock.presenceSubscribe(jid);
    await sock.sendPresenceUpdate('composing', jid);
    await sleep(delayMs);
    await sock.sendPresenceUpdate('paused', jid);
  } catch (err) {
    // La simulacion de presencia es un "nice to have": si falla, igual mandamos el mensaje
    console.warn('[presence] no se pudo simular composing', err.message);
  }
}

module.exports = { simulateTyping, sleep };
