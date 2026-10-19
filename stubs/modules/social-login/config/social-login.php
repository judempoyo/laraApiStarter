<?php

declare(strict_types=1);

return ['google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI', '/api/'.strtolower(env('API_VERSION', 'v1')).'/auth/google/callback'),
]];
