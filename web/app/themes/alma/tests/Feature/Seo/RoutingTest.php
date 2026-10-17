<?php

use App\Domains\Seo\Routing\HttpsRedirect;
use App\Domains\Seo\Routing\MissingPathNotFound;
use Tests\Support\WordPress\WordPress;

describe('real 404s', function () {
    beforeEach(fn () => $this->middleware = new MissingPathNotFound);

    it('forces a 404 when an unknown path resolved to an empty query', function () {
        expect($this->middleware->shouldForce404('/no-such-page/', null, []))->toBeTrue();
    });

    it('leaves the front page, matched rules and explicit query vars alone', function (string $uri, ?string $rule, array $vars) {
        expect($this->middleware->shouldForce404($uri, $rule, $vars))->toBeFalse();
    })->with([
        'front page' => ['/', null, []],
        'front page with query string' => ['/?utm_source=x', null, []],
        'matched rule' => ['/about/', '(.?.+?)(?:/([0-9]+))?/?$', ['pagename' => 'about']],
        'explicit query var' => ['/whatever', null, ['s' => 'term']],
    ]);

    it('never touches WordPress and application endpoints', function (string $uri) {
        expect($this->middleware->shouldForce404($uri, null, []))->toBeFalse();
    })->with(['/wp-admin/', '/wp-json/wp/v2/pages', '/wp/wp-login.php', '/robots.txt', '/livewire/update', '/sitemap.xml']);

    it('skips paths served by Acorn routes', function () {
        expect($this->middleware->shouldForce404('/login', null, [], ['login', 'dashboard']))->toBeFalse()
            ->and($this->middleware->shouldForce404('/login/extra', null, [], ['login']))->toBeTrue();
    });

    it('turns the query into a 404 on parse_request', function () {
        $_SERVER['REQUEST_URI'] = '/missing-page/';
        $wp = (object) ['matched_rule' => null, 'query_vars' => []];

        do_action('parse_request', $wp);

        expect($wp->query_vars)->toBe(['error' => '404']);
    });
});

describe('https redirect', function () {
    it('redirects plain http requests to the https home host', function () {
        expect((new HttpsRedirect)->target(['REQUEST_URI' => '/pricing/?a=1']))->toBe('https://example.test/pricing/?a=1');
    });

    it('accepts HTTPS, a proxy header or port 443 as secure', function (array $server) {
        expect((new HttpsRedirect)->target($server + ['REQUEST_URI' => '/']))->toBeNull();
    })->with([
        'HTTPS on' => [['HTTPS' => 'on']],
        'proxy' => [['HTTP_X_FORWARDED_PROTO' => 'https']],
        'port' => [['SERVER_PORT' => '443']],
    ]);

    it('does nothing when the site itself is http (local development)', function () {
        WordPress::$homeUrl = 'http://alma.test';

        expect((new HttpsRedirect)->target(['REQUEST_URI' => '/']))->toBeNull();
    });
});
