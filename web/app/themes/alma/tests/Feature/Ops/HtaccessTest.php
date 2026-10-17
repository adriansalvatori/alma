<?php

/*
 * The .htaccess files are the only thing standing between the web and .env-adjacent files,
 * Blade templates (PHP) and Acorn's logs on Apache/LiteSpeed shared hosting. A static check is
 * the best a test can do without a web server; it catches the rule being deleted.
 */

function htaccess(string $path): string
{
    $file = dirname(__DIR__, 7).'/'.$path;

    expect($file)->toBeFile();

    return (string) file_get_contents($file);
}

it('passes the Authorization header to PHP for Application Passwords and MCP', function () {
    expect(htaccess('web/.htaccess'))
        ->toContain('SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1')
        ->toContain('RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]');
});

it('keeps the WordPress front controller between its markers', function () {
    expect(htaccess('web/.htaccess'))->toMatch('/# BEGIN WordPress.*RewriteRule \. \/index\.php \[L\].*# END WordPress/s');
});

it('denies dotfiles, logs, dumps, Composer files and XML-RPC', function () {
    $rules = htaccess('web/.htaccess');

    foreach (['^\.', 'log', 'sql', 'composer\.(json|lock)', 'package(-lock)?\.json', 'xmlrpc\.php'] as $pattern) {
        expect($rules)->toContain($pattern);
    }

    expect($rules)->toContain('Require all denied')->toContain('X-Content-Type-Options "nosniff"');
});

it('never runs PHP from uploads and never serves Acorn storage', function () {
    expect(htaccess('web/app/.htaccess'))
        ->toContain('RewriteRule ^cache/ - [F,L]')
        ->toContain('RewriteRule ^uploads/.*\.(php[0-9]?|phtml|phar|pl|py|cgi)$ - [F,L,NC]')
        ->and(htaccess('web/app/uploads/.htaccess'))->toContain('Require all denied');
});

it('exposes only built assets from the theme directory', function () {
    expect(htaccess('web/app/themes/alma/.htaccess'))
        ->toContain('RewriteRule ^(public/|style\.css$|screenshot\.(png|jpe?g|webp)$) - [L]')
        ->toContain('RewriteRule ^ - [F]');
});
