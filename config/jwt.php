<?php

return [

    // JWT signing keys (per role) — read from .env
    'customer_key' => env('JWT_KEY'),
    'admin_key'    => env('ADMIN_JWT_KEY'),
    'client_key'   => env('CLIENT_JWT_KEY'),
    'delivery_key' => env('DELIVERY_JWT_KEY'),

    // Cookie hardening: secure flag (true only over HTTPS / production)
    'cookie_secure' => env('COOKIE_SECURE', false),

];
