<?php

// Core Application Configuration
return [
    'app_name' => getenv('APP_NAME') ?: 'Narayan Jewelers',
    'app_env' => getenv('APP_ENV') ?: 'production',
    'app_url' => getenv('APP_URL') ?: 'http://localhost',
    'debug' => getenv('APP_DEBUG') === 'true',

    // Paths
    'paths' => [
        'base' => __DIR__ . '/../',
        'uploads' => __DIR__ . '/../uploads/',
        'assets' => __DIR__ . '/../assets/',
    ],

    // Security
    'security' => [
        'session_lifetime' => 86400, // 24 hours
        'password_algo' => PASSWORD_ARGON2ID,
    ],

    // Currency
    'currency' => [
        'symbol' => '₹',
        'code' => 'INR',
    ],
];
?>