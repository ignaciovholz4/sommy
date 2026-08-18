# Publicar Sommy en Hostinger — Guía paso a paso

La app es un Laravel estándar con MySQL: **funciona en el hosting compartido de Hostinger**.
Necesitás: PHP **8.1 o superior** (recomendado 8.2/8.3, se elige en hPanel), una base MySQL y el dominio.

---

## 1. Crear la base de datos

1. En hPanel: **Bases de datos → MySQL** → crear base, usuario y contraseña.
   Anotá los tres datos (van a ser algo como `u146006703_sommy` / `u146006703_admin`).
2. Entrá a **phpMyAdmin** de esa base → pestaña **Importar** → subí el archivo
   **`holzdream_erp_hostinger.sql`** (está en la raíz de este proyecto) → Continuar.
   Debe terminar sin errores y crear 69 tablas con tus datos actuales
   (categorías, productos, usuario admin, cliente de prueba, etc.).

## 2. Subir los archivos

La forma más simple y segura en Hostinger:

1. Comprimí **todo el proyecto** en un `.zip` (incluida la carpeta `vendor/`,
   excluí `node_modules/` si existe — no hace falta).
2. En el **Administrador de archivos**, subí el zip **fuera de `public_html`**
   (por ejemplo en `/home/u146006703/sommy`) y descomprimilo ahí.
3. Copiá el **contenido de la carpeta `public/`** del proyecto dentro de `public_html/`
   (index.php, .htaccess, css/, js/, imagenes/, dist/, plugins/, etc.).
4. Editá `public_html/index.php` y cambiá las dos rutas para que apunten al proyecto:

   ```php
   require __DIR__.'/../sommy/vendor/autoload.php';
   $app = require_once __DIR__.'/../sommy/bootstrap/app.php';
   ```

   (donde `sommy` es la carpeta donde descomprimiste el proyecto).

> Alternativa si tu plan tiene SSH: subí el zip, `unzip`, y hacés lo mismo por consola.

### Variante rápida (si ya subiste TODO el proyecto dentro de `public_html`)

Si descomprimiste el proyecto completo directamente en `public_html` (se ven `vendor/`,
`storage/`, `.env`, etc.) vas a recibir un **403 Forbidden**. Solución: creá un archivo
llamado exactamente **`.htaccess`** en la raíz de `public_html` con este contenido:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Bloquear acceso directo a archivos sensibles
    RewriteRule ^\.env - [F,L]
    RewriteRule ^(composer\.(json|lock)|package\.json|artisan|\.git.*)$ - [F,L]

    # Enviar todo al front controller de Laravel en /public
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>
```

Con esto el sitio abre sin tocar `index.php`. Es un esquema aceptable (el `.htaccess`
bloquea los archivos sensibles), aunque el de arriba —proyecto fuera de `public_html`—
sigue siendo el recomendado.

## 3. Configurar el `.env` de producción

En la carpeta del proyecto (NO en public_html), editá el archivo `.env`:

```env
APP_NAME=Sommy
APP_ENV=production
APP_KEY=base64:VWPHZI1EMZ30FrG6YWcm7RqQUNuCcShU8DVlzZbbTTc=
APP_DEBUG=false
APP_URL=https://TU-DOMINIO.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u146006703_sommy        # el nombre que creaste en el paso 1
DB_USERNAME=u146006703_admin        # tu usuario MySQL de Hostinger
DB_PASSWORD=TU_PASSWORD_MYSQL

# Mail real (necesario para recuperar contraseña y confirmar pedidos).
# Podés usar un mail del propio dominio creado en hPanel → Correos:
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=hola@TU-DOMINIO.com
MAIL_PASSWORD=TU_PASSWORD_DE_CORREO
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="hola@TU-DOMINIO.com"
MAIL_FROM_NAME="${APP_NAME}"

# Mercado Pago (Checkout Pro): pegá tus credenciales de producción de
# https://www.mercadopago.com.ar/developers → la opción aparece sola en el checkout
MERCADOPAGO_ACCESS_TOKEN=
MERCADOPAGO_PUBLIC_KEY=
```

Mantené el resto de las variables como están en tu `.env` local.

## 4. Ajustes finales

- **Permisos**: `storage/` y `bootstrap/cache/` deben ser escribibles (en Hostinger
  normalmente ya lo son; si algo falla, permisos 755/775 desde el administrador de archivos).
- **HTTPS**: activá el certificado SSL gratis en hPanel → el sitio debe abrir con https.
- **PHP**: en hPanel → PHP Configuration elegí 8.2 u 8.3 y verificá extensiones
  `pdo_mysql`, `mbstring`, `openssl`, `gd`, `curl`, `zip` (vienen activas por defecto).
- Si tenés SSH, corré una vez dentro del proyecto:
  `php artisan config:clear && php artisan view:clear && php artisan route:clear`
  (sin SSH no es obligatorio: la app corre igual).

## 5. Probar

1. `https://TU-DOMINIO.com` → tienda con video, categorías y productos.
2. `/login` → panel de gestión (mismo usuario y clave que usás en local).
3. `/cuenta/registro` → crear una cuenta de comprador y hacer un pedido de prueba
   (debe abrirse WhatsApp con el detalle).
4. `/forgot-password` → debe llegar el mail de recuperación (ya con SMTP real).
5. Panel → Informes → tablero CEO.

## Notas

- El dump incluye el **cliente de prueba** `cliente-prueba@sommy.test` (clave `clave12345`);
  podés borrarlo desde el panel cuando quieras.
- El video del hero pesa ~1.2MB (ya optimizado), carga bien en hosting compartido.
- Los webhooks de Mercado Pago apuntan a `https://TU-DOMINIO.com/mercadopago/webhook`;
  configurá esa URL en tu aplicación de MP cuando cargues las credenciales.
- El nombre de empresa que se muestra en el footer sale de Panel → Usuarios → Datos de Empresa.
