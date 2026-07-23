<?php

return [
    'access_audit' => [
        'enabled' => filter_var(env('SYSTEM_ACCESS_AUDIT_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'skip_paths' => array_filter(
            array_map('trim', explode(',', (string) env('SYSTEM_ACCESS_AUDIT_SKIP_PATHS', '_debugbar,livewire')))
        ),
    ],

    'geolocation' => [
        'endpoint' => env('IP_GEOLOCATION_ENDPOINT'),
        'timeout' => max(1, (int) env('IP_GEOLOCATION_TIMEOUT', 2)),
    ],

    'ai_feature_builder' => [
        'endpoint' => env('AI_FEATURE_BUILDER_ENDPOINT'),
        'token' => env('AI_FEATURE_BUILDER_TOKEN'),
        'timeout' => max(5, (int) env('AI_FEATURE_BUILDER_TIMEOUT', 20)),
    ],

    'ai_executor' => [
        'endpoint' => env('AI_FEATURE_EXECUTOR_ENDPOINT'),
        'token' => env('AI_FEATURE_EXECUTOR_TOKEN'),
        'timeout' => max(10, (int) env('AI_FEATURE_EXECUTOR_TIMEOUT', 60)),
    ],

    'api_tester' => [
        'allow_private_network' => filter_var(env('SYSTEM_API_TESTER_ALLOW_PRIVATE_NETWORK', true), FILTER_VALIDATE_BOOLEAN),
        'max_response_bytes' => max(1000, (int) env('SYSTEM_API_TESTER_MAX_RESPONSE_BYTES', 200000)),
    ],
];
