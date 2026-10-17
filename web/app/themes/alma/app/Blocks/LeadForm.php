<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

/**
 * A lead capture form (the Lead module's Livewire form) with a heading and intro.
 *
 * Fallback copy lives in DEFAULTS only: with() falls back to it and every field's default_value
 * points at it, so the sidebar always shows what the block renders.
 */
class LeadForm extends Block
{
    public const DEFAULTS = [
        'form_key' => 'contact',
        'heading' => 'Get in touch',
        'intro' => 'Tell us a little about what you need and we will get back to you within one business day.',
        'show_phone' => false,
        'show_company' => false,
        'show_message' => true,
        'require_consent' => true,
        'consent_text' => 'I agree to be contacted about my enquiry.',
        'button_label' => 'Send message',
        'success_message' => '',
        'redirect_url' => '',
    ];

    public $name = 'Lead Form';

    public $description = 'A contact form that stores leads, emails the team and posts to your CRM webhook.';

    public $category = 'alma';

    public $icon = 'email-alt';

    public $keywords = ['form', 'contact', 'lead', 'enquiry'];

    public $mode = 'preview';

    public $blockVersion = 3;

    public $supports = [
        'align' => ['wide', 'full'],
        'anchor' => true,
        'mode' => true,
        'jsx' => false,
    ];

    public $example = [
        'heading' => 'Get in touch',
        'intro' => 'Tell us a little about what you need and we will get back to you within one business day.',
        'show_company' => true,
    ];

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        $fields = array_keys(array_filter([
            'phone' => $this->value('show_phone'),
            'company' => $this->value('show_company'),
            'message' => $this->value('show_message'),
        ]));

        return [
            'formKey' => (string) $this->value('form_key'),
            'heading' => (string) $this->value('heading'),
            'intro' => (string) $this->value('intro'),
            'fields' => $fields,
            'requireConsent' => (bool) $this->value('require_consent'),
            'consentText' => (string) $this->value('consent_text'),
            'buttonLabel' => (string) $this->value('button_label'),
            'successMessage' => (string) $this->value('success_message'),
            'redirectUrl' => (string) $this->value('redirect_url'),
        ];
    }

    /**
     * @return array<mixed>
     */
    public function fields(): array
    {
        $fields = Builder::make('lead_form_block');

        $fields
            ->addText('heading', ['label' => 'Heading', 'default_value' => self::DEFAULTS['heading']])
            ->addTextarea('intro', ['label' => 'Intro', 'rows' => 3, 'default_value' => self::DEFAULTS['intro']])
            ->addText('button_label', ['label' => 'Button label', 'default_value' => self::DEFAULTS['button_label']])
            ->addTextarea('success_message', [
                'label' => 'Success message',
                'rows' => 2,
                'instructions' => 'Leave empty to use the message from Leads → Settings.',
                'default_value' => self::DEFAULTS['success_message'],
            ])
            // A plain text field, not a URL field: ACF's URL field rejects relative paths like /thanks/.
            ->addText('redirect_url', [
                'label' => 'Redirect to',
                'instructions' => 'Optional thank-you page, e.g. /thanks/. Overrides the success message.',
                'default_value' => self::DEFAULTS['redirect_url'],
            ])
            ->addTrueFalse('show_phone', ['label' => 'Ask for a phone number', 'ui' => 1, 'default_value' => self::DEFAULTS['show_phone']])
            ->addTrueFalse('show_company', ['label' => 'Ask for a company', 'ui' => 1, 'default_value' => self::DEFAULTS['show_company']])
            ->addTrueFalse('show_message', ['label' => 'Ask for a message', 'ui' => 1, 'default_value' => self::DEFAULTS['show_message']])
            ->addTrueFalse('require_consent', ['label' => 'Require consent', 'ui' => 1, 'default_value' => self::DEFAULTS['require_consent']])
            ->addText('consent_text', [
                'label' => 'Consent text',
                'default_value' => self::DEFAULTS['consent_text'],
                'conditional_logic' => [[['field' => 'require_consent', 'operator' => '==', 'value' => '1']]],
            ])
            ->addText('form_key', [
                'label' => 'Form key',
                'instructions' => 'Groups leads by form in Leads (e.g. "contact", "pricing-demo").',
                'default_value' => self::DEFAULTS['form_key'],
            ]);

        return $fields->build();
    }

    /**
     * The saved field value, or its default. An empty text field or an unset toggle falls back;
     * a toggle switched off (false) is a real value and is kept.
     */
    private function value(string $key): mixed
    {
        $value = get_field($key);

        if ($value === null || $value === '') {
            return self::DEFAULTS[$key];
        }

        return $value;
    }
}
