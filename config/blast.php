<?php

$retryBackoff = array_values(array_filter(
    array_map(
        static fn (string $value): int => (int) trim($value),
        explode(',', (string) env('BLAST_RETRY_BACKOFF_SECONDS', '30,120,300'))
    ),
    static fn (int $seconds): bool => $seconds >= 0
));

if ($retryBackoff === []) {
    $retryBackoff = [30, 120, 300];
}

return [
    'rate_limits' => [
        'email_per_minute' => max(1, (int) env('BLAST_EMAIL_RATE_PER_MINUTE', 90)),
        'whatsapp_per_minute' => max(1, (int) env('BLAST_WHATSAPP_RATE_PER_MINUTE', 45)),
    ],

    'batch' => [
        'size' => max(1, (int) env('BLAST_BATCH_SIZE', 50)),
        'delay_seconds' => max(0, (int) env('BLAST_BATCH_DELAY_SECONDS', 10)),
    ],

    'retry' => [
        'max_attempts' => max(1, (int) env('BLAST_RETRY_ATTEMPTS', 3)),
        'backoff_seconds' => $retryBackoff,
    ],

    'pause_poll_seconds' => max(5, (int) env('BLAST_PAUSE_POLL_SECONDS', 30)),

    'dispatch' => [
        'email_mode' => strtolower((string) env('BLAST_EMAIL_MODE', 'queue')),
        'whatsapp_mode' => strtolower((string) env('BLAST_WHATSAPP_MODE', 'queue')),
    ],

    'email_accounts' => [
        'enabled' => filter_var(env('BLAST_EMAIL_ACCOUNTS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'default_provider' => strtolower((string) env('BLAST_EMAIL_ACCOUNT_DEFAULT_PROVIDER', 'gmail')),
        'gmail' => [
            'host' => env('BLAST_EMAIL_GMAIL_HOST', 'smtp.gmail.com'),
            'port' => max(1, (int) env('BLAST_EMAIL_GMAIL_PORT', 587)),
            'encryption' => strtolower((string) env('BLAST_EMAIL_GMAIL_ENCRYPTION', 'tls')),
            'timeout' => max(5, (int) env('BLAST_EMAIL_GMAIL_TIMEOUT', 30)),
        ],
    ],

    'import' => [
        'default_email_domain' => env('BLAST_IMPORT_DEFAULT_EMAIL_DOMAIN', 'gmail.com'),
    ],

    'queues' => [
        'email' => [
            'high' => env('BLAST_QUEUE_EMAIL_HIGH', 'blast-email-high'),
            'normal' => env('BLAST_QUEUE_EMAIL_NORMAL', 'blast-email-normal'),
            'low' => env('BLAST_QUEUE_EMAIL_LOW', 'blast-email-low'),
        ],
        'whatsapp' => [
            'high' => env('BLAST_QUEUE_WHATSAPP_HIGH', 'blast-whatsapp-high'),
            'normal' => env('BLAST_QUEUE_WHATSAPP_NORMAL', 'blast-whatsapp-normal'),
            'low' => env('BLAST_QUEUE_WHATSAPP_LOW', 'blast-whatsapp-low'),
        ],
    ],
];
