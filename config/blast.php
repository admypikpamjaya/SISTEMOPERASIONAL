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
