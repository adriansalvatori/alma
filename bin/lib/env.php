<?php

/**
 * Read and write .env files safely, for the bin/ scripts.
 *
 * Bash cannot quote arbitrary values for phpdotenv (a database password with a quote, a dollar or
 * a backslash breaks sed-based edits silently), so every write goes through here.
 *
 * Usage:
 *   php bin/lib/env.php get <file> <KEY>                 Print the parsed value (empty if unset).
 *   php bin/lib/env.php set <file> KEY=VALUE [...]       Replace or append keys, quoting safely.
 *   php bin/lib/env.php init <file> <template>           Copy the template if <file> does not exist.
 *   php bin/lib/env.php secrets <file>                   Fill the WordPress salts and APP_KEY when
 *                                                        they are missing, empty or "generateme".
 *
 * Needs only PHP. Uses vlucas/phpdotenv for parsing when the root vendor/ exists, so values read
 * exactly as Bedrock will read them.
 */

const SALTS = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];

const PLACEHOLDERS = ['', 'generateme'];

function fail(string $message): never
{
    fwrite(STDERR, "env.php: {$message}\n");
    exit(1);
}

/**
 * @return array<string, string>
 */
function parse_env(string $file): array
{
    if (! is_file($file)) {
        return [];
    }

    $contents = (string) file_get_contents($file);
    $autoload = dirname(__DIR__, 2).'/vendor/autoload.php';

    if (is_file($autoload)) {
        require_once $autoload;

        return array_map(fn ($value) => (string) $value, Dotenv\Dotenv::parse($contents));
    }

    // Fallback before composer install: good enough for the simple values setup.sh reads.
    $values = [];

    foreach (preg_split('/\R/', $contents) as $line) {
        if (preg_match('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $m)) {
            $raw = trim($m[2]);
            $values[$m[1]] = match (true) {
                str_starts_with($raw, "'") => substr($raw, 1, (int) strrpos($raw, "'") - 1),
                str_starts_with($raw, '"') => stripcslashes(substr($raw, 1, (int) strrpos($raw, '"') - 1)),
                default => trim(preg_replace('/\s+#.*$/', '', $raw)),
            };
        }
    }

    return $values;
}

/**
 * Quote a value so phpdotenv reads it back byte for byte: single quotes are literal, and only a
 * value containing a single quote needs double quotes with \\, \" and \$ escaped.
 */
function quote(string $value): string
{
    if (! str_contains($value, "'")) {
        return "'{$value}'";
    }

    return '"'.strtr($value, ['\\' => '\\\\', '"' => '\\"', '$' => '\\$']).'"';
}

/**
 * @param  array<string, string>  $pairs
 */
function write_env(string $file, array $pairs): void
{
    $lines = is_file($file) ? preg_split('/\R/', rtrim((string) file_get_contents($file), "\n")) : [];

    foreach ($pairs as $key => $value) {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            fail("invalid key: {$key}");
        }

        $line = $key.'='.quote($value);
        $replaced = false;

        foreach ($lines as $i => $existing) {
            // An active assignment of this key. A commented example ("# KEY=...") is left alone.
            if (preg_match('/^\s*(?:export\s+)?'.preg_quote($key, '/').'\s*=/', $existing)) {
                $lines[$i] = $line;
                $replaced = true;
                break;
            }
        }

        if (! $replaced) {
            $lines[] = $line;
        }
    }

    $tmp = $file.'.tmp-'.getmypid();
    file_put_contents($tmp, implode("\n", $lines)."\n");
    @chmod($tmp, 0600);
    rename($tmp, $file) || fail("could not write {$file}");
}

function salt(): string
{
    // No quotes, backslashes or dollars, so the value is safe in any quoting style.
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%^&*()-_[]{}<>~+=,.;:/?|';
    $salt = '';

    for ($i = 0; $i < 64; $i++) {
        $salt .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    return $salt;
}

[$script, $command, $file] = array_pad($argv, 3, null);

if ($command === null || $file === null) {
    fail('usage: env.php get|set|init|secrets <file> ...');
}

switch ($command) {
    case 'get':
        $key = $argv[3] ?? fail('usage: env.php get <file> <KEY>');
        echo parse_env($file)[$key] ?? '';
        break;

    case 'set':
        $pairs = [];

        foreach (array_slice($argv, 3) as $pair) {
            if (! str_contains($pair, '=')) {
                fail("expected KEY=VALUE, got: {$pair}");
            }

            [$key, $value] = explode('=', $pair, 2);
            $pairs[$key] = $value;
        }

        write_env($file, $pairs);
        break;

    case 'init':
        $template = $argv[3] ?? fail('usage: env.php init <file> <template>');

        if (! is_file($file)) {
            copy($template, $file) || fail("could not copy {$template}");
            @chmod($file, 0600);
        }
        break;

    case 'secrets':
        $current = parse_env($file);
        $pairs = [];

        foreach (SALTS as $key) {
            if (in_array($current[$key] ?? '', PLACEHOLDERS, true)) {
                $pairs[$key] = salt();
            }
        }

        if (in_array($current['APP_KEY'] ?? '', PLACEHOLDERS, true)) {
            $pairs['APP_KEY'] = 'base64:'.base64_encode(random_bytes(32));
        }

        if ($pairs !== []) {
            write_env($file, $pairs);
        }

        echo count($pairs);
        break;

    default:
        fail("unknown command: {$command}");
}
