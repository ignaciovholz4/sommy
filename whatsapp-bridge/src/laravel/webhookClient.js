const axios = require('axios');
const FormData = require('form-data');
const { downloadMediaMessage } = require('@whiskeysockets/baileys');

function extractText(msg) {
  const m = msg.message || {};
  return (
    m.conversation ||
    m.extendedTextMessage?.text ||
    m.imageMessage?.caption ||
    m.videoMessage?.caption ||
    m.buttonsResponseMessage?.selectedDisplayText ||
    m.listResponseMessage?.title ||
    ''
  );
}

function detectType(msg) {
  const m = msg.message || {};
  if (m.imageMessage) return 'image';
  if (m.videoMessage) return 'video';
  if (m.audioMessage) return 'audio';
  if (m.documentMessage) return 'document';
  if (m.stickerMessage) return 'sticker';
  if (m.locationMessage) return 'location';
  return 'text';
}

/**
 * Manda el mensaje entrante a Laravel (BaileysWebhookController). Si tiene
 * adjunto, lo descarga aca mismo con Baileys y lo manda como multipart:
 * Laravel lo guarda directo, sin depender de una URL remota.
 */
async function handleInboundMessage(sock, msg) {
  const type = detectType(msg);
  const body = extractText(msg);
  const jid = msg.key.remoteJid;
  const messageId = msg.key.id;
  const pushName = msg.pushName || '';
  const timestamp = Number(msg.messageTimestamp) || Math.floor(Date.now() / 1000);

  // Con JIDs @lid (privacidad de WhatsApp) el numero real viene aparte
  const fromAlt = msg.key.senderPn || msg.key.remoteJidAlt || msg.key.participantPn || '';

  const form = new FormData();
  form.append('message_id', messageId);
  form.append('from', jid);
  form.append('from_alt', fromAlt);
  form.append('push_name', pushName);
  form.append('type', type);
  form.append('body', body);
  form.append('timestamp', String(timestamp));

  if (['image', 'video', 'audio', 'document', 'sticker'].includes(type)) {
    try {
      const buffer = await downloadMediaMessage(msg, 'buffer', {});
      form.append('media', buffer, { filename: `${messageId}` });
    } catch (err) {
      console.error('[webhookClient] no se pudo descargar el adjunto', err.message);
    }
  }

  await axios.post(`${process.env.LARAVEL_URL}/api/whatsapp/baileys/webhook`, form, {
    headers: {
      ...form.getHeaders(),
      'X-Bridge-Token': process.env.INBOUND_TOKEN,
    },
    timeout: 15000,
  });
}

module.exports = { handleInboundMessage };
