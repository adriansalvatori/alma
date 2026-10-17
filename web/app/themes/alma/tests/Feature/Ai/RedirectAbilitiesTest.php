<?php

use App\Domains\Seo\Redirects\Redirect;
use App\Domains\Seo\Redirects\RedirectManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsWordPressUser(['role' => 'editor'])->add_cap('alma_manage_redirects');
});

it('creates a redirect through the SEO module\'s guards', function () {
    $created = ai_test_run_ability('alma/create-redirect', ['from' => '/old-pricing/', 'to' => '/pricing/']);

    expect($created)->toMatchArray(['from' => '/old-pricing', 'status' => 301])
        ->and(Redirect::query()->count())->toBe(1);
});

it('passes the SEO module\'s refusal back to the agent', function () {
    $refused = ai_test_run_ability('alma/create-redirect', ['from' => '/wp-admin/', 'to' => '/pricing/']);

    expect($refused)->toBeInstanceOf(WP_Error::class)
        ->and(Redirect::query()->count())->toBe(0);
});

it('lists managed redirects with ids and code redirects as read-only', function () {
    config(['redirects' => ['/from-code/' => '/to-code/']]);
    app(RedirectManager::class)->create('/old/', '/new/');

    $list = ai_test_run_ability('alma/list-redirects');

    expect($list['managed'])->toHaveCount(1)
        ->and($list['managed'][0]['id'])->toBeInt()
        ->and($list['code'][0])->toMatchArray(['id' => null, 'source' => 'code', 'to' => '/to-code/']);
});

it('deletes only after the agent confirms the preview', function () {
    $redirect = app(RedirectManager::class)->create('/old/', '/new/');

    $preview = ai_test_run_ability('alma/delete-redirect', ['id' => $redirect->id]);

    expect($preview['status'])->toBe('confirmation_required')
        ->and($preview['preview']['will_delete'])->toMatchArray(['from' => '/old'])
        ->and(Redirect::query()->count())->toBe(1);

    $done = ai_test_run_ability('alma/delete-redirect', ['id' => $redirect->id, 'confirmation_token' => $preview['confirmation_token']]);

    expect($done)->toBe(['deleted' => $redirect->id])
        ->and(Redirect::query()->count())->toBe(0);
});

it('refuses a token issued for a different redirect', function () {
    $first = app(RedirectManager::class)->create('/one/', '/new/');
    $second = app(RedirectManager::class)->create('/two/', '/new/');

    $token = ai_test_run_ability('alma/delete-redirect', ['id' => $first->id])['confirmation_token'];
    $result = ai_test_run_ability('alma/delete-redirect', ['id' => $second->id, 'confirmation_token' => $token]);

    expect($result)->toBeInstanceOf(WP_Error::class)
        ->and(Redirect::query()->count())->toBe(2);
});

it('does not offer to delete a redirect that does not exist', function () {
    expect(ai_test_run_ability('alma/delete-redirect', ['id' => 999]))->toBeInstanceOf(WP_Error::class);
});
