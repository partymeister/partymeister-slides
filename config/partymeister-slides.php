<?php

return [
    'webdriver'   => env('CHROMEDRIVER'),
    'screens_url' => env('SCREENS_URL', config('app.url')),

    // Which slidemeister-web Blade template to use: 'index' (Vue 2) or 'index-v3' (Vue 3)
    'slidemeister_web_template' => env('SLIDEMEISTER_WEB_TEMPLATE', 'index-v3'),
];
