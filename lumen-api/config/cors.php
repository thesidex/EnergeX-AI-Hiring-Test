<?php

return [
    'allowed_origins'      => array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))),
    'allow_credentials'    => (bool) env('CORS_ALLOW_CREDENTIALS', false),
    'expose_headers'       => array_filter(array_map('trim', explode(',', env('CORS_EXPOSE_HEADERS', '')))),
    'max_age'              => (int) env('CORS_MAX_AGE', 600),
];
