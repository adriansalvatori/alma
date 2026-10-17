<?php

use App\Blocks\LeadForm;
use Tests\Support\WordPress\WordPress;

function leadFormBlock(): LeadForm
{
    return (new ReflectionClass(LeadForm::class))->newInstanceWithoutConstructor();
}

it('falls back to DEFAULTS when nothing is saved', function () {
    expect(leadFormBlock()->with())->toMatchArray([
        'formKey' => LeadForm::DEFAULTS['form_key'],
        'heading' => LeadForm::DEFAULTS['heading'],
        'fields' => ['message'],
        'requireConsent' => true,
        'buttonLabel' => LeadForm::DEFAULTS['button_label'],
    ]);
});

it('uses saved values and keeps a toggle switched off', function () {
    WordPress::$acf['block'] = [
        'heading' => 'Book a demo',
        'show_phone' => true,
        'show_message' => false,
        'require_consent' => false,
        'form_key' => 'demo',
    ];

    expect(leadFormBlock()->with())->toMatchArray([
        'heading' => 'Book a demo',
        'fields' => ['phone'],
        'requireConsent' => false,
        'formKey' => 'demo',
    ]);
});

it('falls back when a text field was cleared', function () {
    WordPress::$acf['block'] = ['button_label' => ''];

    expect(leadFormBlock()->with()['buttonLabel'])->toBe(LeadForm::DEFAULTS['button_label']);
});

it('points every field\'s default_value at DEFAULTS', function () {
    $fields = collect(leadFormBlock()->fields()['fields'])->keyBy('name');

    foreach (LeadForm::DEFAULTS as $name => $default) {
        expect($fields)->toHaveKey($name)
            ->and($fields[$name]['default_value'])->toBe($default);
    }
});
