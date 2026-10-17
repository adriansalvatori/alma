<?php

/*
 * Every ability checks its own capability. The agent user is an editor, so the line that matters
 * is what an editor can and cannot do, and that personal data (leads) and site-wide settings
 * (redirects) need more than that.
 */

$content = ['alma/site-health', 'alma/list-pages', 'alma/describe-page', 'alma/list-patterns', 'alma/list-blocks', 'alma/create-draft-post', 'alma/list-media', 'alma/upload-media'];
$leads = ['alma/lead-stats', 'alma/query-leads', 'alma/update-lead-status'];
$redirects = ['alma/list-redirects', 'alma/create-redirect', 'alma/delete-redirect'];

function ai_allowed(string $ability): bool
{
    return ai_test_ability($ability)['permission_callback']([]) === true;
}

it('lets a subscriber run nothing', function (string $ability) {
    $this->actingAsWordPressUser(['role' => 'subscriber']);

    expect(ai_allowed($ability))->toBeFalse();
})->with([...$content, ...$leads, ...$redirects]);

it('lets an editor run the content and media abilities', function (string $ability) {
    $this->actingAsWordPressUser(['role' => 'editor']);

    expect(ai_allowed($ability))->toBeTrue();
})->with($content);

it('keeps leads from an editor without alma_manage_leads', function (string $ability) {
    $this->actingAsWordPressUser(['role' => 'editor']);

    expect(ai_allowed($ability))->toBeFalse();
})->with($leads);

it('opens leads with alma_manage_leads', function (string $ability) {
    $this->actingAsWordPressUser(['role' => 'editor'])->add_cap('alma_manage_leads');

    expect(ai_allowed($ability))->toBeTrue();
})->with($leads);

it('keeps redirects from an editor', function (string $ability) {
    $this->actingAsWordPressUser(['role' => 'editor']);

    expect(ai_allowed($ability))->toBeFalse();
})->with($redirects);

it('opens redirects with manage_options or the narrower alma_manage_redirects', function (string $ability) {
    $this->actingAsWordPressUser(['role' => 'administrator']);
    expect(ai_allowed($ability))->toBeTrue();

    $this->actingAsWordPressUser(['role' => 'editor'])->add_cap('alma_manage_redirects');
    expect(ai_allowed($ability))->toBeTrue();
})->with($redirects);

it('denies an editor whose upload capability was explicitly revoked', function () {
    $this->actingAsWordPressUser(['role' => 'editor'])->add_cap('upload_files', false);

    expect(ai_allowed('alma/upload-media'))->toBeFalse();
});
