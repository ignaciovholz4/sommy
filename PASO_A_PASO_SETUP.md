**Pasos a seguir para levantar el entorno local**

## Installar dependencias: (necesrio instalar extenciones de php)
composer install
npm install
npm run dev

## Copiar .env con las conexiones necesarias:
cp .env.backup .env

#--  Revisar las conexiones de base de datos --#

# Tenant Database Configuration
DB_CONNECTION=tenant
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=root
DB_PASSWORD=

# Landlord Database Configuration
DB_CONNECTION=landlord
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facturag
DB_USERNAME=root
DB_PASSWORD=

#-- Puedes crear tu propio usuario para controlar las bases de datos, --#
#-- solo recuerda de darle permisos necesarios para poder ejecutar las migraciones --#

## Limpiar configuracion y cache
php artisan cache:clear
php artisan config:clear

## Ejecutar la migracion
php artisan migrate --database=landlord
php artisan db:seed

## Limpiar configuracion y cache
php artisan cache:clear
php artisan config:clear

## Ejecutar el SuperAdmin
php artisan superadmin:setup

- **URL**: `http://your-domain.com/superadmin/login`
- **Email**: `admin@facturarg.com`
- **Password**: `admin123`

**Una vez llegado a este punto deberiamos levantar el proyecto con Laravel Valet o XAAMP**

- **Laravel Valet**:
Instalar laravel valet
## Revisar documentacion de Laravel Valet

## Ir a donde tiene alojada tu carpeta de proyecto (no en la carpeta de proyecto)
cd path_donde_se_aloja_el_proyecto/
valet park
valet domain nombre_del_dominio

cd path_de_tu_proyecto
valet link

- **Con esto vas a poder utilizar multi-tenants**

## Una vez terminado esto. Deberias poder entrar al superadmin, tenants y dashboard de cada caso