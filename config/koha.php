<?php

return [
    'enabled'  => env('KOHA_ENABLED', false),
    'base_url' => env('KOHA_BASE_URL', ''),
    'api_key'  => env('KOHA_API_KEY', ''),
    'timeout'  => env('KOHA_TIMEOUT', 15),
];
