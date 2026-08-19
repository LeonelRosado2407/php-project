<?php

declare(strict_types=1);

return [
    'app' => [
        'env'   => env('APP_ENV', 'production'),
        'debug' => env('APP_ENV', 'production') === 'development',
    ],
    'db' => [
        'host'     => env('DB_HOST'),
        'port'     => env('DB_PORT'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
    ],
    'jwt' => [
        'secret'     => env('JWT_SECRET'),
        'expiration' => env('JWT_EXPIRATION', 3600),
    ],
];
