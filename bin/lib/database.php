<?php

/**
 * Create the database named in .env if it does not exist, over PDO.
 *
 * `wp db create` needs the mysql client binary, which many local setups (DBngin, Herd) do not put
 * on the PATH. PHP's pdo_mysql is always there.
 *
 * Usage: php bin/lib/database.php create <path/to/.env>
 * Exit codes: 0 created or already present, 1 failure (message on stderr).
 */

[$script, $command, $file] = array_pad($argv, 3, null);

if ($command !== 'create' || $file === null || ! is_file($file)) {
    fwrite(STDERR, "usage: database.php create <path/to/.env>\n");
    exit(1);
}

require dirname(__DIR__, 2).'/vendor/autoload.php';

$env = Dotenv\Dotenv::parse((string) file_get_contents($file));

if (! empty($env['DATABASE_URL'])) {
    $dsn = parse_url($env['DATABASE_URL']);
    $env['DB_HOST'] = ($dsn['host'] ?? 'localhost').(isset($dsn['port']) ? ':'.$dsn['port'] : '');
    $env['DB_USER'] = $dsn['user'] ?? '';
    $env['DB_PASSWORD'] = $dsn['pass'] ?? '';
    $env['DB_NAME'] = ltrim($dsn['path'] ?? '', '/');
}

$name = $env['DB_NAME'] ?? '';

if ($name === '' || ! preg_match('/^[A-Za-z0-9_\-]+$/', $name)) {
    fwrite(STDERR, "DB_NAME is empty or contains characters other than letters, digits, - and _.\n");
    exit(1);
}

[$host, $port] = array_pad(explode(':', $env['DB_HOST'] ?? 'localhost', 2), 2, null);
$dsn = 'mysql:host='.($host === 'localhost' ? '127.0.0.1' : $host).($port ? ";port={$port}" : '');

try {
    $pdo = new PDO($dsn, $env['DB_USER'] ?? '', $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    fwrite(STDERR, "Could not create database {$name}: {$e->getMessage()}\n");
    exit(1);
}

echo "Database {$name} is ready.\n";
