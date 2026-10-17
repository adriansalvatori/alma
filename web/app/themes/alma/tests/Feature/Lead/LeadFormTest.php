<?php

use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Tests submit instantly; the fill-time check has its own test below.
    config(['leads.spam.min_seconds' => 0]);
    Event::fake([LeadCaptured::class]);
});

it('validates the required fields', function () {
    Livewire::test('lead.form')
        ->call('submit')
        ->assertHasErrors(['name' => 'required', 'email' => 'required']);

    expect(Lead::query()->count())->toBe(0);
});

it('captures a lead and shows the success message', function () {
    Livewire::test('lead.form', ['formKey' => 'contact', 'fields' => ['company', 'message']])
        ->set('name', 'Ada Lovelace')
        ->set('email', 'ada@example.com')
        ->set('company', 'Analytical Engines')
        ->set('message', 'Hello')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSet('email', '')
        ->assertSee(app(LeadSettings::class)->successMessage());

    $lead = Lead::query()->sole();

    expect($lead->form_key)->toBe('contact')
        ->and($lead->company)->toBe('Analytical Engines');

    Event::assertDispatched(LeadCaptured::class);
});

it('ignores fields the form does not show', function () {
    Livewire::test('lead.form', ['fields' => []])
        ->set('name', 'Ada')
        ->set('email', 'ada@example.com')
        ->set('company', 'Injected')
        ->call('submit');

    expect(Lead::query()->sole()->company)->toBeNull();
});

it('requires consent when the form asks for it', function () {
    Livewire::test('lead.form', ['requireConsent' => true])
        ->set('name', 'Ada')
        ->set('email', 'ada@example.com')
        ->call('submit')
        ->assertHasErrors(['consent' => 'accepted'])
        ->set('consent', true)
        ->call('submit')
        ->assertHasNoErrors();

    expect(Lead::query()->sole()->consent)->toBeTrue();
});

it('shows a bot the same success but stores nothing', function () {
    Livewire::test('lead.form')
        ->set('name', 'Bot')
        ->set('email', 'bot@example.com')
        ->set('website', 'http://spam.example')
        ->call('submit')
        ->assertSet('sent', true);

    expect(Lead::query()->count())->toBe(0);
    Event::assertNotDispatched(LeadCaptured::class);
});

it('keeps spam for review when "store spam" is on', function () {
    app(LeadSettings::class)->save(['store_spam' => '1']);

    Livewire::test('lead.form')
        ->set('name', 'Bot')
        ->set('email', 'bot@example.com')
        ->set('website', 'http://spam.example')
        ->call('submit');

    expect(Lead::query()->sole()->status)->toBe(LeadStatus::Spam);
    Event::assertNotDispatched(LeadCaptured::class);
});

it('treats an instant submission as a bot', function () {
    config(['leads.spam.min_seconds' => 3]);

    Livewire::test('lead.form')
        ->set('name', 'Ada')
        ->set('email', 'ada@example.com')
        ->call('submit')
        ->assertSet('sent', true);

    expect(Lead::query()->count())->toBe(0);
});

it('redirects to the thank-you page when one is set', function () {
    Livewire::test('lead.form', ['redirectUrl' => '/thanks/'])
        ->set('name', 'Ada')
        ->set('email', 'ada@example.com')
        ->call('submit')
        ->assertRedirect('https://example.test/thanks/');
});

it('drops a redirect that is not http(s)', function () {
    Livewire::test('lead.form', ['redirectUrl' => 'javascript:alert(1)'])
        ->assertSet('redirectUrl', '');
});

it('does not let the visitor change the locked configuration', function () {
    Livewire::test('lead.form', ['formKey' => 'contact'])
        ->set('formKey', 'other');
})->throws(CannotUpdateLockedPropertyException::class);
