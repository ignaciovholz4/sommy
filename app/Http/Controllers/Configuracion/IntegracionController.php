<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;

/**
 * Panel de Integraciones: carga de todas las claves de API que el software
 * necesita (IA, Meta/WhatsApp, MercadoPago, mail) sin tocar el .env a mano.
 *
 * Las claves secretas nunca se muestran completas: solo los últimos 4
 * caracteres. Un campo vacío conserva el valor actual.
 */
class IntegracionController extends Controller
{
    /** Definición de cada grupo y sus variables de entorno. */
    public static function grupos(): array
    {
        return [
            'ia' => [
                'titulo' => 'Inteligencia Artificial',
                'icono' => 'fa-magic',
                'descripcion' => 'Textos, escenas y videos del Estudio de Publicaciones, y el bot de ventas de WhatsApp.',
                'campos' => [
                    ['env' => 'OPENAI_API_KEY', 'label' => 'OpenAI API Key', 'secreto' => true, 'ayuda' => 'platform.openai.com/api-keys — textos del Estudio y bot de ventas'],
                    ['env' => 'ANTHROPIC_API_KEY', 'label' => 'Anthropic API Key (opcional)', 'secreto' => true, 'ayuda' => 'console.anthropic.com — alternativa para el bot de ventas'],
                    ['env' => 'GEMINI_API_KEY', 'label' => 'Google Gemini API Key', 'secreto' => true, 'ayuda' => 'aistudio.google.com/apikey — escenas de producto y videos UGC'],
                    ['env' => 'PUBLICACIONES_COPY_MODEL', 'label' => 'Modelo para textos', 'secreto' => false, 'ayuda' => 'default: gpt-4o-mini'],
                    ['env' => 'GEMINI_IMAGE_MODEL', 'label' => 'Modelo de imágenes', 'secreto' => false, 'ayuda' => 'default: gemini-2.5-flash-image'],
                    ['env' => 'GEMINI_VIDEO_MODEL', 'label' => 'Modelo de video', 'secreto' => false, 'ayuda' => 'default: veo-3.0-fast-generate-001 (requiere plan pago de Google AI)'],
                ],
                'configurada' => fn () => (bool) (env('OPENAI_API_KEY') || env('GEMINI_API_KEY')),
            ],
            'meta' => [
                'titulo' => 'Meta · WhatsApp, Facebook e Instagram',
                'icono' => 'fa-comments',
                'descripcion' => 'WhatsApp Cloud API, publicación en la página de Facebook y en Instagram. Ver docs/GUIA_META_WHATSAPP.md.',
                'campos' => [
                    ['env' => 'WHATSAPP_TOKEN', 'label' => 'WhatsApp Token', 'secreto' => true, 'ayuda' => 'Token permanente de la app de Meta'],
                    ['env' => 'WHATSAPP_PHONE_NUMBER_ID', 'label' => 'Phone Number ID', 'secreto' => false, 'ayuda' => 'ID del número de WhatsApp Business'],
                    ['env' => 'WHATSAPP_WABA_ID', 'label' => 'WABA ID', 'secreto' => false, 'ayuda' => 'ID de la cuenta de WhatsApp Business'],
                    ['env' => 'WHATSAPP_VERIFY_TOKEN', 'label' => 'Verify Token del webhook', 'secreto' => true, 'ayuda' => 'El mismo que pusiste al configurar el webhook'],
                    ['env' => 'WHATSAPP_APP_SECRET', 'label' => 'App Secret', 'secreto' => true, 'ayuda' => 'Configuración › Básica de la app de Meta'],
                    ['env' => 'IG_APP_SECRET', 'label' => 'App Secret de Instagram', 'secreto' => true, 'ayuda' => 'Producto "API de Instagram" › clave secreta de la app de IG (firma sus webhooks)'],
                    ['env' => 'IG_ACCESS_TOKEN', 'label' => 'Token de acceso de Instagram', 'secreto' => true, 'ayuda' => 'Producto "API de Instagram" › Generar tokens. Dura ~60 días: regenerarlo antes de que venza'],
                    ['env' => 'FB_PAGE_ID', 'label' => 'ID de la página de Facebook', 'secreto' => false, 'ayuda' => 'Para publicar desde el Estudio y Messenger'],
                    ['env' => 'FB_PAGE_TOKEN', 'label' => 'Token de la página de Facebook', 'secreto' => true, 'ayuda' => 'Token de página con permisos de publicación'],
                    ['env' => 'IG_ACCOUNT_ID', 'label' => 'ID de la cuenta de Instagram', 'secreto' => false, 'ayuda' => 'Cuenta profesional vinculada a la página'],
                ],
                'configurada' => fn () => (bool) (env('WHATSAPP_TOKEN') || env('FB_PAGE_TOKEN')),
            ],
            'whatsapp_baileys' => [
                'titulo' => 'WhatsApp no oficial (Baileys)',
                'icono' => 'fa-qrcode',
                'descripcion' => 'Bot reactivo sobre WhatsApp Web mientras se completa la verificación de Meta. Requiere levantar el proceso Node de whatsapp-bridge/ (ver whatsapp-bridge/README.md) y vincular el número escaneando el QR en {bridge_url}/qr — cargar estas claves NO levanta el proceso por sí solo.',
                'campos' => [
                    ['env' => 'WHATSAPP_BRIDGE_URL', 'label' => 'URL del bridge', 'secreto' => false, 'ayuda' => 'Donde corre el proceso Node, ej: http://127.0.0.1:3300'],
                    ['env' => 'WHATSAPP_BRIDGE_TOKEN', 'label' => 'Token del bridge (Laravel → bridge)', 'secreto' => true, 'ayuda' => 'Mismo valor que BRIDGE_TOKEN en whatsapp-bridge/.env'],
                    ['env' => 'WHATSAPP_BRIDGE_INBOUND_TOKEN', 'label' => 'Token de entrada (bridge → Laravel)', 'secreto' => true, 'ayuda' => 'Mismo valor que INBOUND_TOKEN en whatsapp-bridge/.env'],
                ],
                'configurada' => fn () => (bool) (env('WHATSAPP_BRIDGE_TOKEN') && env('WHATSAPP_BRIDGE_INBOUND_TOKEN')),
            ],
            'mercadopago' => [
                'titulo' => 'MercadoPago',
                'icono' => 'fa-credit-card',
                'descripcion' => 'Checkout Pro del ecommerce. DESHABILITADO a pedido: aunque cargues el Access Token acá, el checkout no lo va a ofrecer como medio de pago hasta que MERCADOPAGO_ENABLED=true se active a mano en el servidor.',
                'campos' => [
                    ['env' => 'MERCADOPAGO_ACCESS_TOKEN', 'label' => 'Access Token', 'secreto' => true, 'ayuda' => 'Credenciales de producción de tu cuenta'],
                    ['env' => 'MERCADOPAGO_PUBLIC_KEY', 'label' => 'Public Key', 'secreto' => false, 'ayuda' => 'Clave pública del checkout'],
                ],
                'configurada' => fn () => (bool) env('MERCADOPAGO_ACCESS_TOKEN'),
            ],
            'mail' => [
                'titulo' => 'Correo saliente',
                'icono' => 'fa-envelope',
                'descripcion' => 'Avisos de pedidos a clientes y recupero de contraseña.',
                'campos' => [
                    ['env' => 'MAIL_MAILER', 'label' => 'Mailer', 'secreto' => false, 'ayuda' => 'smtp en producción; log para pruebas locales'],
                    ['env' => 'MAIL_HOST', 'label' => 'Servidor SMTP', 'secreto' => false, 'ayuda' => 'ej: smtp.hostinger.com'],
                    ['env' => 'MAIL_PORT', 'label' => 'Puerto', 'secreto' => false, 'ayuda' => '465 (ssl) o 587 (tls)'],
                    ['env' => 'MAIL_USERNAME', 'label' => 'Usuario', 'secreto' => false, 'ayuda' => 'Casilla desde la que se envía'],
                    ['env' => 'MAIL_PASSWORD', 'label' => 'Contraseña', 'secreto' => true, 'ayuda' => ''],
                    ['env' => 'MAIL_ENCRYPTION', 'label' => 'Encriptación', 'secreto' => false, 'ayuda' => 'ssl o tls'],
                    ['env' => 'MAIL_FROM_ADDRESS', 'label' => 'Remitente', 'secreto' => false, 'ayuda' => 'Dirección visible en los mails'],
                ],
                'configurada' => fn () => env('MAIL_MAILER') === 'smtp' && env('MAIL_HOST'),
            ],
        ];
    }

    public function index()
    {
        Gate::authorize('haveaccess', 'configuracion.index');

        $grupos = collect(self::grupos())->map(function ($g) {
            $g['estado'] = ($g['configurada'])();
            $g['campos'] = array_map(function ($c) {
                $valor = env($c['env']);
                $c['cargada'] = (bool) $valor;
                // Los secretos solo muestran los últimos 4 caracteres
                $c['valor'] = $c['secreto']
                    ? ($valor ? '••••' . substr($valor, -4) : '')
                    : ($valor ?? '');
                return $c;
            }, $g['campos']);
            unset($g['configurada']);
            return $g;
        });

        return view('configuracion.integraciones', ['grupos' => $grupos]);
    }

    public function guardar(Request $request)
    {
        Gate::authorize('haveaccess', 'configuracion.index');

        $permitidas = collect(self::grupos())->flatMap(fn ($g) => array_column($g['campos'], null, 'env'));

        $cambios = [];
        foreach ($request->input('claves', []) as $env => $valor) {
            $campo = $permitidas->get($env);
            $valor = trim((string) $valor);

            // Campo vacío o máscara sin tocar => conservar el valor actual
            if (!$campo || $valor === '' || str_starts_with($valor, '••••')) {
                continue;
            }

            $cambios[$env] = $valor;
        }

        if (!$cambios) {
            return back()->with('int_ok', 'No había cambios para guardar.');
        }

        $this->escribirEnv($cambios);
        Artisan::call('config:clear');

        return back()->with('int_ok', 'Se guardaron ' . count($cambios) . ' clave(s). Ya están activas.');
    }

    /** Actualiza o agrega variables en el .env, con backup previo. */
    protected function escribirEnv(array $cambios): void
    {
        $ruta = base_path('.env');
        $contenido = file_get_contents($ruta);

        copy($ruta, base_path('.env.bak'));

        foreach ($cambios as $clave => $valor) {
            // Comillas si el valor tiene espacios o caracteres especiales
            $valorEnv = preg_match('/[\s#"\']/', $valor)
                ? '"' . str_replace('"', '\\"', $valor) . '"'
                : $valor;

            $linea = $clave . '=' . $valorEnv;

            if (preg_match('/^' . preg_quote($clave, '/') . '=.*$/m', $contenido)) {
                $contenido = preg_replace('/^' . preg_quote($clave, '/') . '=.*$/m', $linea, $contenido);
            } else {
                $contenido = rtrim($contenido) . PHP_EOL . $linea . PHP_EOL;
            }
        }

        file_put_contents($ruta, $contenido);
    }
}
