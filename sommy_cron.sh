#!/bin/sh
# Mantenimiento de producción — lo llama el cron de Hostinger cada minuto.
# Vive en la raíz del proyecto (junto a artisan) y viaja con cada deploy:
#  1) aplica las migraciones pendientes después de cada push
#  2) procesa la cola de jobs (webhooks de WhatsApp/Instagram, mails, media)
cd "$(dirname "$0")" || exit 1

/usr/bin/php artisan migrate --force
/usr/bin/php artisan queue:work --stop-when-empty --max-time=50 >/dev/null 2>&1
