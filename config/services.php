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
    ],

    // Base de conocimiento de productos: 'public' local (con backup del server)
    // o 's3' para guardarla directo en la nube
    'conocimiento' => [
        'disk' => env('CONOCIMIENTO_DISK', 'public'),
    ],

    'mercadopago' => [
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

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],

];
