<?php

return [
    'generate_screenshots' => env('GENERATE_SCREENSHOTS'),
    'screens_url' => env('SCREENS_URL', config('app.url')),

    // Which slidemeister-web Blade template to use: 'index' (Vue 2) or 'index-v3' (Vue 3)
    'slidemeister_web_template' => env('SLIDEMEISTER_WEB_TEMPLATE', 'index-v3'),

    // Shared secret for the external /api/slide_clients/{id}/control/* endpoints.
    // Callers send it as the X-Slidemeister-Token header. Empty = feature disabled (503).
    'control_token' => env('SLIDEMEISTER_CONTROL_TOKEN'),
];
