<?php

return [
    'webdriver'   => env('CHROMEDRIVER'),
    'screens_url' => env('SCREENS_URL', config('app.url')),
];
