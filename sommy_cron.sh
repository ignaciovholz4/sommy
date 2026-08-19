#!/bin/sh
# Mantenimiento de producción — lo llama el cron de Hostinger cada minuto.
# Vive en la raíz del proyecto (junto a artisan) y viaja con cada deploy:
#  1) aplica las migraciones pendientes después de cada push
#  2) procesa la cola de jobs (webhooks de WhatsApp/Instagram, mails, media)
cd "$(dirname "$0")" || exit 1

/usr/bin/php artisan migrate --force

# Primera puesta en marcha: si la base no tiene NINGÚN usuario, corre los
# seeders (crean admin@gmail.com + roles/permisos + configuración inicial).
# El chequeo de usuarios hace imposible que esto toque una base con datos.
USERS=$(/usr/bin/php artisan tinker --execute='echo \App\User::count();' 2>/dev/null | tail -1)
if [ "$USERS" = "0" ]; then
    echo "Base sin usuarios: corriendo seeders iniciales..."
    /usr/bin/php artisan db:seed --force
fi

# Tareas programadas (recordatorios de cobranza, reposición, alertas de stock)
/usr/bin/php artisan schedule:run >/dev/null 2>&1

/usr/bin/php artisan queue:work --stop-when-empty --max-time=50 >/dev/null 2>&1
