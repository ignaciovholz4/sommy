#!/bin/sh
# Vigilante del bridge de WhatsApp (Baileys) para hosting compartido.
# Corre cada minuto via Cron Job de hPanel: si el bridge no responde el
# health-check lo levanta de nuevo con nohup. CloudLinux puede matar el
# proceso cuando quiere; este script lo resucita en el proximo minuto.

BASE="$HOME/domains/palegreen-tiger-296316.hostingersite.com/public_html"
BRIDGE_DIR="$BASE/whatsapp-bridge"
NODE_BIN="/opt/alt/alt-nodejs22/root/usr/bin/node"
LOG="$BASE/storage/logs/wa-bridge.log"
PORT=3300

# Sin .env no hay nada que arrancar (los tokens viven ahi)
[ -f "$BRIDGE_DIR/.env" ] || exit 0

# ¿Responde el health-check? Entonces esta vivo, no hay nada que hacer.
if curl -s -m 5 "http://127.0.0.1:$PORT/health" | grep -q '"status":"ok"'; then
    exit 0
fi

# Proceso colgado que no responde: matarlo antes de relanzar
pkill -f "node src/index.js" 2>/dev/null
sleep 1

cd "$BRIDGE_DIR" || exit 1
echo "[$(date '+%Y-%m-%d %H:%M:%S')] wa_bridge_cron: relanzando bridge" >> "$LOG"
nohup "$NODE_BIN" src/index.js >> "$LOG" 2>&1 &
