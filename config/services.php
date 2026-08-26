<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
        'video_model' => env('GEMINI_VIDEO_MODEL', 'veo-3.0-fast-generate-001'),
        'text_model' => env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash'),
    ],

    'publicaciones' => [
        'copy_model' => env('PUBLICACIONES_COPY_MODEL', 'gpt-4o-mini'),
    ],

    'reportes' => [
        'provider' => env('REPORTES_AI_PROVIDER', 'openai'),
        'model' => env('REPORTES_AI_MODEL', 'gpt-4o-mini'),
    ],

    'cobranzas' => [
        'dias_gracia_default' => env('COBRANZAS_DIAS_GRACIA_DEFAULT', 30),
        'nota_model' => env('COBRANZAS_NOTA_MODEL', 'gpt-4o-mini'),
    ],

    // Base de conocimiento de productos: 'public' local (con backup del server)
    // o 's3' para guardarla directo en la nube
    'conocimiento' => [
        'disk' => env('CONOCIMIENTO_DISK', 'public'),
    ],

    'mercadopago' => [
        'enabled' => env('MERCADOPAGO_ENABLED', false),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'waba_id' => env('WHATSAPP_WABA_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        // Producto "API de Instagram": app propia con clave propia; sus webhooks
        // vienen firmados con esta clave (no con la de la app principal)
        'ig_app_secret' => env('IG_APP_SECRET'),
        // Token de acceso de la cuenta de IG (API de Instagram, graph.instagram.com)
        'ig_access_token' => env('IG_ACCESS_TOKEN'),
        'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),
        // Messenger / Instagram (misma app de Meta)
        'page_id' => env('FB_PAGE_ID'),
        'page_token' => env('FB_PAGE_TOKEN'),
        'ig_account_id' => env('IG_ACCOUNT_ID'),
    ],

    // Promo del bot de ventas: los precios reales se presentan como precio con
    // descuento ya aplicado. El % define el "precio de lista" tachado que se
    // muestra (precio_actual * (1 + %/100)). 0 = promo apagada.
    'bot_promo' => [
        'porcentaje' => (int) env('BOT_PROMO_OFF', 20),
        'nombre' => env('BOT_PROMO_NOMBRE', 'Promo Sommy'),
    ],

    // WhatsApp no oficial (Baileys): bridge Node.js propio, sin pasar por Meta.
    // Bot 100% reactivo mientras se completa la verificacion de negocio en Meta.
    'whatsapp_baileys' => [
        'bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3300'),
        'bridge_token' => env('WHATSAPP_BRIDGE_TOKEN'),
        'inbound_token' => env('WHATSAPP_BRIDGE_INBOUND_TOKEN'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],

    // Chytapay: cobros por transferencia + conciliacion bancaria automatica.
    // OAuth2 (Authorization Code) contra auth-api, cobros contra integration-api.
    'chytapay' => [
        'enabled' => env('CHYTAPAY_ENABLED', false),
        'client_id' => env('CHYTAPAY_CLIENT_ID'),
        'client_secret' => env('CHYTAPAY_CLIENT_SECRET'),
        'redirect_uri' => env('CHYTAPAY_REDIRECT_URI'),
        'auth_base_url' => env('CHYTAPAY_ENV', 'test') === 'prod'
            ? 'https://auth-api.chytapay.com.ar'
            : 'https://auth-api.test.chytapay.com.ar',
        'api_base_url' => env('CHYTAPAY_ENV', 'test') === 'prod'
            ? 'https://integration-api.chytapay.com.ar'
            : 'https://integration-api.test.chytapay.com.ar',
    ],

    // Meta Ads (Marketing API): token con permiso ads_read sobre la cuenta
    // publicitaria (distinto del token de WhatsApp/Graph de mensajeria).
    'meta_ads' => [
        'enabled' => env('META_ADS_ENABLED', false),
        'access_token' => env('META_ADS_ACCESS_TOKEN'),
        'ad_account_id' => env('META_ADS_AD_ACCOUNT_ID'),
        'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),
        // Moneda de facturacion de la cuenta publicitaria (la que Meta ya usa para cobrar)
        'moneda' => env('META_ADS_MONEDA', 'ARS'),
    ],

    // Google Ads API: requiere developer token aprobado por Google + OAuth2
    // (client_id/secret de un proyecto de Google Cloud + refresh_token de la
    // cuenta que administra el Google Ads).
    'google_ads' => [
        'enabled' => env('GOOGLE_ADS_ENABLED', false),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),
        'login_customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
        'api_version' => env('GOOGLE_ADS_API_VERSION', 'v17'),
        'moneda' => env('GOOGLE_ADS_MONEDA', 'ARS'),
    ],

    // Microsoft Clarity: mapas de calor + grabaciones de sesion de la tienda online.
    // Gratis, no guarda nada en nuestro servidor. Crear proyecto en clarity.microsoft.com
    // y pegar el Project ID aqui — sin eso, el script ni se inyecta.
    'clarity' => [
        'project_id' => env('MICROSOFT_CLARITY_ID'),
    ],

];
