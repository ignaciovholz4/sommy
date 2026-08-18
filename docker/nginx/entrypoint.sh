#!/usr/bin/env sh
set -eu

# This script runs before nginx starts (docker-entrypoint.d/*).
# Goal:
# - For VPS: start nginx on port 80 even before Let's Encrypt cert exists.
# - Once cert exists, switch to the full HTTPS config.
# - For local dev (no /etc/letsencrypt yet): generate a self-signed cert.

pick_le_live_dir() {
  # Prefer exact name, otherwise pick first matching directory
  if [ -d "/etc/letsencrypt/live/facturarg.com" ]; then
    echo "/etc/letsencrypt/live/facturarg.com"
    return
  fi
  for d in /etc/letsencrypt/live/facturarg.com*; do
    if [ -d "$d" ]; then
      echo "$d"
      return
    fi
  done
  echo ""
}

LE_LIVE_DIR="$(pick_le_live_dir)"
LE_CERT=""
LE_KEY=""
if [ -n "${LE_LIVE_DIR}" ]; then
  LE_CERT="${LE_LIVE_DIR}/fullchain.pem"
  LE_KEY="${LE_LIVE_DIR}/privkey.pem"
fi

if [ -n "${LE_CERT}" ] && [ -f "${LE_CERT}" ] && [ -f "${LE_KEY}" ]; then
  echo "Let's Encrypt cert found; using HTTPS config."
  cp /etc/nginx/templates/app.prod.conf /etc/nginx/conf.d/app.conf
  exit 0
fi

echo "Let's Encrypt cert not found; using HTTP bootstrap config for ACME."
cp /etc/nginx/templates/app.http-bootstrap.conf /etc/nginx/conf.d/app.conf

SSL_DIR="/etc/nginx/ssl"
CERT="${SSL_DIR}/fullchain.pem"
KEY="${SSL_DIR}/privkey.pem"
mkdir -p "${SSL_DIR}"

if [ ! -f "${CERT}" ] || [ ! -f "${KEY}" ]; then
  echo "Generating self-signed SSL certificate (local fallback)..."
  openssl req -x509 -nodes -newkey rsa:2048 \
    -days 3650 \
    -keyout "${KEY}" \
    -out "${CERT}" \
    -subj "/C=AR/ST=BuenosAires/L=BuenosAires/O=Facturarg/OU=Dev/CN=localhost"
fi

exit 0
