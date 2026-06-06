<?php

/**
 * Lions Academy — application-level configuration.
 *
 * Centralizes business knobs (file constraints, rate limits, spam protection,
 * notification recipients) so they can be overridden from .env without
 * touching code or scattering env() calls across the codebase.
 */
return [

    'frontend_url' => env('LIONS_FRONTEND_URL', 'http://localhost:5173'),
    'admin_url' => env('LIONS_ADMIN_URL', 'http://localhost:5173/admin'),

    'notify_email' => env('LIONS_NOTIFY_EMAIL', 'contact@lionsacademy.ma'),

    'whatsapp' => [
        // Academy WhatsApp number — digits only (e.g. "212600000000"). Used
        // for wa.me deep-links in registration responses & notifications.
        'number' => env('LIONS_WHATSAPP_NUMBER', '212600000000'),
        'default_message' => env(
            'LIONS_WHATSAPP_MESSAGE',
            "Bonjour Lions Academy, je souhaite avoir plus d'informations sur la formation."
        ),
    ],

    'uploads' => [
        'image' => [
            'max_kb' => (int) env('UPLOAD_MAX_IMAGE_KB', 8192),
            'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'avif'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/avif'],
        ],
        'document' => [
            'max_kb' => (int) env('UPLOAD_MAX_DOCUMENT_KB', 10240),
            'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx'],
            'mime_types' => [
                'image/jpeg', 'image/png', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ],
        'video' => [
            // 200 MB default — bump LIONS_UPLOAD_MAX_VIDEO_KB in .env if the
            // client supplies bigger source files. Requires matching
            // upload_max_filesize / post_max_size in php.ini.
            'max_kb' => (int) env('UPLOAD_MAX_VIDEO_KB', 204800),
            'mimes' => ['mp4', 'm4v', 'webm', 'mov'],
            'mime_types' => [
                'video/mp4',
                'video/webm',
                'video/quicktime',
            ],
        ],
    ],

    'rate_limits' => [
        'public_read' => (int) env('RATE_LIMIT_PUBLIC_PER_MINUTE', 60),
        'public_write_per_hour' => (int) env('RATE_LIMIT_PUBLIC_WRITE_PER_HOUR', 10),
        'auth_per_minute' => (int) env('RATE_LIMIT_AUTH_PER_MINUTE', 5),
        'admin_per_minute' => (int) env('RATE_LIMIT_ADMIN_PER_MINUTE', 120),
    ],

    'honeypot' => [
        'field' => env('HONEYPOT_FIELD', '_hp_field'),
        'timer_field' => env('HONEYPOT_TIMER_FIELD', '_hp_time'),
        'min_seconds' => 2,
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'seed_admin' => [
        'email' => env('LIONS_ADMIN_EMAIL', 'admin@lionsacademy.ma'),
        'password' => env('LIONS_ADMIN_PASSWORD', 'ChangeMe!2026'),
        'name' => 'Administrateur',
    ],

];
