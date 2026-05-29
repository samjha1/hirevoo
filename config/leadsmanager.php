<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ads Manager (leadsmanager) integration
    |--------------------------------------------------------------------------
    | Public base URL for impression/click tracking (no trailing slash).
    */
    'api_base_url' => rtrim(env('LEADSMANAGER_URL', 'http://localhost/themesdesign.in/leadsmanager/public'), '/'),

    'enabled' => env('LEADSMANAGER_ADS_ENABLED', true),
];
