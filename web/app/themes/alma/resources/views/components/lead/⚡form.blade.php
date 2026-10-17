<?php

use App\Domains\Lead\Actions\CaptureLeadAction;
use App\Domains\Lead\Data\LeadData;
use App\Domains\Lead\Services\LeadSettings;
use App\Domains\Lead\Services\SpamGuard;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
 * The lead form. Placed by the "Lead Form" block, or directly:
 *
 *     <livewire:lead.form form-key="pricing" :fields="['phone', 'company']" />
 *
 * Everything that configures the form is #[Locked]: a visitor can change what they type, never
 * which form it counts as, where it redirects or whether consent is required.
 */
new class extends Component {
    public const FIELDS = ['phone', 'company', 'message'];

    #[Locked]
    public string $formKey = 'default';

    /** @var list<string> Optional fields shown besides name and email. */
    #[Locked]
    public array $fields = ['message'];

    #[Locked]
    public bool $requireConsent = false;

    #[Locked]
    public string $consentText = '';

    #[Locked]
    public string $buttonLabel = '';

    #[Locked]
    public string $successMessage = '';

    #[Locked]
    public string $redirectUrl = '';

    /** When the form was rendered, encrypted. See SpamGuard. */
    #[Locked]
    public string $token = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company = '';

    public string $message = '';

    public bool $consent = false;

    /** The honeypot. Hidden from people; bots fill it in. */
    public string $website = '';

    public bool $sent = false;

    /**
     * @param  list<string>  $fields
     */
    public function mount(
        SpamGuard $guard,
        string $formKey = 'default',
        array $fields = ['message'],
        bool $requireConsent = false,
        string $consentText = '',
        string $buttonLabel = '',
        string $successMessage = '',
        string $redirectUrl = '',
    ): void {
        $this->formKey = sanitize_key($formKey) ?: 'default';
        $this->fields = array_values(array_intersect(self::FIELDS, $fields));
        $this->requireConsent = $requireConsent;
        $this->consentText = $consentText !== '' ? $consentText : __('I agree to be contacted about my enquiry.', 'alma');
        $this->buttonLabel = $buttonLabel !== '' ? $buttonLabel : __('Send', 'alma');
        $this->successMessage = $successMessage;
        $this->redirectUrl = $this->safeRedirect($redirectUrl);
        $this->token = $guard->issueToken();
    }

    public function submit(SpamGuard $guard, CaptureLeadAction $capture, LeadSettings $settings)
    {
        $this->validate($this->rules());

        $data = LeadData::fromArray([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->shows('phone') ? $this->phone : null,
            'company' => $this->shows('company') ? $this->company : null,
            'message' => $this->shows('message') ? $this->message : null,
            'form_key' => $this->formKey,
            'consent' => $this->consent,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $spam = $guard->inspect($this->website, $this->token, $this->email, request()->ip());

        // A bot gets the same success a person does, so it learns nothing about the checks.
        if ($spam !== null) {
            if ($settings->storeSpam()) {
                $capture->recordSpam($data, $spam);
            }

            return $this->finish();
        }

        $capture->execute($data);

        return $this->finish();
    }

    public function shows(string $field): bool
    {
        return in_array($field, $this->fields, true);
    }

    public function successText(): string
    {
        return $this->successMessage !== '' ? $this->successMessage : app(LeadSettings::class)->successMessage();
    }

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return array_filter([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => $this->shows('phone') ? ['nullable', 'string', 'max:64'] : null,
            'company' => $this->shows('company') ? ['nullable', 'string', 'max:255'] : null,
            'message' => $this->shows('message') ? ['nullable', 'string', 'max:5000'] : null,
            'consent' => $this->requireConsent ? ['accepted'] : null,
        ]);
    }

    protected function finish()
    {
        $this->reset('name', 'email', 'phone', 'company', 'message', 'consent', 'website');
        $this->sent = true;

        if ($this->redirectUrl !== '') {
            return $this->redirect($this->redirectUrl);
        }

        return null;
    }

    /**
     * A thank-you page on this site (relative or same host) or any http(s) URL. Anything else
     * (javascript:, data:) is dropped.
     */
    protected function safeRedirect(string $url): string
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '//')) {
            return '';
        }

        if (str_starts_with($url, '/')) {
            return home_url($url);
        }

        return preg_match('#^https?://#i', $url) ? esc_url_raw($url) : '';
    }
};
?>

<div class="alma-lead-form">
  @if ($sent)
    <div role="status" class="rounded-lg border border-green-200 bg-green-50 p-6 text-green-900 dark:border-green-900 dark:bg-green-950 dark:text-green-100">
      <p class="font-medium">{{ $this->successText() }}</p>
    </div>
  @else
    <form wire:submit="submit" class="space-y-5" novalidate>
      <div class="grid gap-5 sm:grid-cols-2">
        <flux:input wire:model="name" :label="__('Name', 'alma')" autocomplete="name" required />
        <flux:input wire:model="email" type="email" :label="__('Email', 'alma')" autocomplete="email" required />

        @if ($this->shows('phone'))
          <flux:input wire:model="phone" type="tel" :label="__('Phone', 'alma')" autocomplete="tel" />
        @endif

        @if ($this->shows('company'))
          <flux:input wire:model="company" :label="__('Company', 'alma')" autocomplete="organization" />
        @endif
      </div>

      @if ($this->shows('message'))
        <flux:textarea wire:model="message" :label="__('Message', 'alma')" rows="5" />
      @endif

      {{-- Honeypot: off-screen, out of the tab order, ignored by password managers. --}}
      <div aria-hidden="true" style="position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;">
        <label for="{{ $this->getId() }}-website">Website</label>
        <input id="{{ $this->getId() }}-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
      </div>

      @if ($requireConsent)
        <flux:checkbox wire:model="consent" :label="$consentText" />
      @endif

      <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="submit">{{ $buttonLabel }}</span>
        <span wire:loading wire:target="submit">{{ __('Sending…', 'alma') }}</span>
      </flux:button>
    </form>
  @endif
</div>
