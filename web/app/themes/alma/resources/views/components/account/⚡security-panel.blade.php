<?php

use App\Domains\Account\Services\RecoveryCodes;
use App\Domains\Account\Services\TwoFactorService;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public bool $enabled = false;

    #[Locked]
    public bool $settingUp = false;

    /** Shown once, right after they are generated. */
    #[Locked]
    public array $recoveryCodes = [];

    public string $code = '';

    public string $password = '';

    public function mount(TwoFactorService $twoFactor): void
    {
        $this->enabled = $twoFactor->isEnabled($this->user()->ID);
        $this->settingUp = ! $this->enabled && $twoFactor->pendingSecret($this->user()->ID) !== null;
    }

    public function begin(TwoFactorService $twoFactor): void
    {
        if ($this->enabled) {
            return;
        }

        $twoFactor->beginSetup($this->user()->ID);
        $this->settingUp = true;
    }

    public function cancel(TwoFactorService $twoFactor): void
    {
        $twoFactor->cancelSetup($this->user()->ID);
        $this->settingUp = false;
        $this->reset('code');
    }

    public function confirm(TwoFactorService $twoFactor, RecoveryCodes $codes): void
    {
        $this->validate(['code' => 'required|string']);

        if (! $twoFactor->confirmSetup($this->user()->ID, $this->code)) {
            $this->addError('code', __('That code is not valid. Check the time on your phone and try again.', 'alma'));

            return;
        }

        $this->enabled = true;
        $this->settingUp = false;
        $this->recoveryCodes = $codes->generate($this->user()->ID);
        $this->reset('code');
    }

    public function regenerate(RecoveryCodes $codes): void
    {
        if ($this->enabled && $this->passwordConfirmed()) {
            $this->recoveryCodes = $codes->generate($this->user()->ID);
        }
    }

    public function disable(TwoFactorService $twoFactor, RecoveryCodes $codes): void
    {
        if (! $this->enabled || ! $this->passwordConfirmed()) {
            return;
        }

        $twoFactor->disable($this->user()->ID);
        $codes->clear($this->user()->ID);

        $this->enabled = false;
        $this->recoveryCodes = [];
    }

    public function with(TwoFactorService $twoFactor, RecoveryCodes $codes): array
    {
        $user = $this->user();
        $secret = $this->settingUp ? $twoFactor->pendingSecret($user->ID) : null;

        return [
            'secret' => $secret,
            'qrCode' => $secret ? $twoFactor->qrCodeSvg($user, $secret) : null,
            'remaining' => $this->enabled ? $codes->remaining($user->ID) : 0,
        ];
    }

    /**
     * Every action re-reads the WordPress user: a Livewire request is a new HTTP request, and the
     * component must never act for anyone but the person whose cookie came with it.
     */
    protected function user(): WP_User
    {
        $user = wp_get_current_user();
        abort_unless($user->exists(), 403);

        return $user;
    }

    protected function passwordConfirmed(): bool
    {
        $user = $this->user();
        $confirmed = $this->password !== '' && wp_check_password($this->password, $user->user_pass, $user->ID);
        $this->reset('password');

        if (! $confirmed) {
            $this->addError('password', __('That password is not correct.', 'alma'));
        }

        return $confirmed;
    }
};
?>

<section class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('Two-factor authentication', 'alma') }}</flux:heading>
        <flux:text class="mt-1">
            {{ __('Ask for a code from an authenticator app whenever you sign in with your password.', 'alma') }}
        </flux:text>
    </div>

    @if ($recoveryCodes)
        <flux:callout variant="warning" icon="key">
            <flux:callout.heading>{{ __('Save your recovery codes', 'alma') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Each code works once, if you lose your phone. They will not be shown again.', 'alma') }}
            </flux:callout.text>
        </flux:callout>

        <ul class="grid grid-cols-2 gap-2 font-mono text-sm" data-recovery-codes>
            @foreach ($recoveryCodes as $recoveryCode)
                <li>{{ $recoveryCode }}</li>
            @endforeach
        </ul>
    @endif

    @if ($enabled)
        <flux:badge color="green" icon="shield-check">{{ __('Enabled', 'alma') }}</flux:badge>
        <flux:text>{{ sprintf(__('%d recovery codes left.', 'alma'), $remaining) }}</flux:text>

        <div class="space-y-3 max-w-sm">
            <flux:input wire:model="password" type="password" autocomplete="current-password"
                :label="__('Confirm your password to make changes', 'alma')" />
            <div class="flex gap-3">
                <flux:button wire:click="regenerate">{{ __('New recovery codes', 'alma') }}</flux:button>
                <flux:button variant="danger" wire:click="disable">{{ __('Turn off', 'alma') }}</flux:button>
            </div>
        </div>
    @elseif ($settingUp && $qrCode)
        <div class="space-y-4 max-w-sm">
            <flux:text>{{ __('Scan this code with your authenticator app, then enter the 6-digit code it shows.', 'alma') }}</flux:text>
            <div class="inline-block rounded-lg bg-white p-3">{!! $qrCode !!}</div>
            <flux:text class="font-mono text-xs break-all">{{ $secret }}</flux:text>

            <form wire:submit="confirm" class="space-y-3">
                <flux:input wire:model="code" inputmode="numeric" autocomplete="one-time-code" :label="__('Code', 'alma')" required />
                <div class="flex gap-3">
                    <flux:button variant="primary" type="submit">{{ __('Turn on', 'alma') }}</flux:button>
                    <flux:button variant="ghost" type="button" wire:click="cancel">{{ __('Cancel', 'alma') }}</flux:button>
                </div>
            </form>
        </div>
    @else
        <flux:button variant="primary" wire:click="begin">{{ __('Set up two-factor authentication', 'alma') }}</flux:button>
    @endif
</section>
