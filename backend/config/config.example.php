<?php
/** M2–M4: Copy this file to config.php, then supply local secrets. Never commit config.php. */
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'fittrack_connect',
        'username' => 'YOUR_DB_USER',
        'password' => 'YOUR_DB_PASSWORD',
    ],
    'jwt_secret' => 'REPLACE_WITH_LONG_RANDOM_SECRET',
    'spoonacular_api_key' => 'YOUR_API_KEY',
];
