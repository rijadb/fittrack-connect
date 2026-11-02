<?php
/** PDO connection factory. Configured locally via config.php (not tracked in Git). */
final class Database
{
    public static function connect(): PDO
    {
        $path = __DIR__ . '/config.php';
        if (!is_file($path)) {
            throw new RuntimeException('Missing backend/config/config.php. Copy config.example.php and edit it.');
        }

        $config = require $path;
        if (!is_array($config) || !isset($config['db']) || !is_array($config['db'])) {
            throw new RuntimeException('Invalid database configuration.');
        }
        $db = $config['db'];
        foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
            if (!array_key_exists($key, $db)) {
                throw new RuntimeException("Missing database configuration key: {$key}");
            }
        }
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $db['host'], (int) $db['port'], $db['database']);

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
