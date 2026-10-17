<?php

use App\Domains\Seo\Robots\PageRobots;
use App\Domains\Seo\Routing\RouteSeo;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    PageRobots::reset();
    RouteSeo::reset();

    // Stand-in for the Account module's middleware: only the name matters to RouteSeo.
    app('router')->aliasMiddleware('guest', RouteSeoPassThrough::class);
});

afterEach(function () {
    PageRobots::reset();
    RouteSeo::reset();
});

/**
 * Runs the WordPress head hooks from inside an Acorn route and returns what they produced.
 */
function routeSeoProbe(): array
{
    ob_start();
    do_action('wp_head');
    $head = ob_get_clean();

    return [
        'supports' => apply_filters('the_seo_framework_query_supports_seo', true),
        'title' => apply_filters('pre_get_document_title', ''),
        'robots' => apply_filters('wp_robots', []),
        'head' => $head,
    ];
}

it('tells The SEO Framework to step aside and prints its own canonical on Acorn routes', function () {
    Route::get('/pricing-calculator', fn () => response()->json(routeSeoProbe()));
    $_SERVER['REQUEST_URI'] = '/pricing-calculator?ref=x';

    $probe = $this->get('/pricing-calculator?ref=x')->json();

    expect($probe['supports'])->toBeFalse()
        ->and($probe['head'])->toContain('<link rel="canonical" href="https://example.test/pricing-calculator">')
        ->and($probe['robots'])->toBe([]);
});

it('uses the title and description a controller sets', function () {
    Route::get('/calculator', function () {
        RouteSeo::set(title: 'Savings calculator', description: 'Work out what you save.');

        return response()->json(routeSeoProbe());
    });

    $probe = $this->get('/calculator')->json();

    expect($probe['title'])->toBe('Savings calculator | Alma')
        ->and($probe['head'])->toContain('<meta name="description" content="Work out what you save.">');
});

it('noindexes routes behind the guest or auth middleware automatically', function () {
    Route::middleware('guest')->get('/sign-in-probe', fn () => response()->json(routeSeoProbe()));

    expect($this->get('/sign-in-probe')->json('robots'))->toMatchArray(['noindex' => true, 'nofollow' => true]);
});

it('leaves pages WordPress renders to The SEO Framework', function () {
    expect(apply_filters('the_seo_framework_query_supports_seo', true))->toBeTrue()
        ->and(apply_filters('pre_get_document_title', 'Original'))->toBe('Original');
});

class RouteSeoPassThrough
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
