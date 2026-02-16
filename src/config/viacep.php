<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for the ViaCEP API.
    |
    */
    'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br/ws'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum wait time for requests in seconds.
    |
    */
    'timeout' => env('VIACEP_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Retry
    |--------------------------------------------------------------------------
    |
    | Number of retries in case of request failure.
    |
    */
    'retry' => env('VIACEP_RETRY', 3),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Cache settings for API responses.
    |
    */
    'cache_enabled' => env('VIACEP_CACHE_ENABLED', true),
    'cache_ttl'     => env('VIACEP_CACHE_TTL', 3600), // 1 hour in seconds
    'cache_prefix'  => env('VIACEP_CACHE_PREFIX', 'viacep'),

    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    |
    | Default response format: json, xml, piped, jsonp
    |
    */
    'default_format' => env('VIACEP_DEFAULT_FORMAT', 'json'),

    /*
    |--------------------------------------------------------------------------
    | JSONP Callback
    |--------------------------------------------------------------------------
    |
    | Default callback function name for JSONP requests.
    |
    */
    'jsonp_callback' => env('VIACEP_JSONP_CALLBACK', 'callback'),
];
