<?php

use App\Domains\Account\Actions\VerifyTwoFactorChallenge;
use App\Domains\Account\Services\PendingChallenge;
use Livewire\Component;

new class extends Component
{
    public string $code = '';

    public bool $useRecoveryCode = false;

    public function mount(PendingChallenge $challenges)
    {
        if ($challenges->current() === null) {
            return redirect()->route('login');
        }
    }

    public function toggleRecovery(): void
    {
        $this->useRecoveryCode = ! $this->useRecoveryCode;
        $this->reset('code');
        $this->resetErrorBag();
    }

    public function verify(PendingChallenge $challenges, VerifyTwoFactorChallenge $verify)
    {
        $this->validate(['code' => 'required|string|max:32']);

        // Re-read on every attempt: the challenge may have expired or run out of attempts.
        $pending = $challenges->current();

        if ($pending === null) {
            session()->flash('status', __('Your sign-in expired. Please enter your password again.', 'alma'));

            return redirect()->route('login');
        }

        if ($verify->execute($pending, $this->code, $this->useRecoveryCode)) {
            return $pending->redirectTo
                ? redirect()->to($pending->redirectTo)
                : redirect()->intended(route('dashboard'));
        }

        $this->reset('code');

        if ($challenges->current() === null) {
            session()->flash('status', __('Too many incorrect codes. Please sign in again.', 'alma'));

            return redirect()->route('login');
        }

        $this->addError('code', __('That code is not valid.', 'alma'));

        return null;
    }
};
?>

<form wire:submit="verify" class="space-y-6">
    @if ($useRecoveryCode)
        <flux:input wire:model="code" :label="__('Recovery code', 'alma')" type="text" autocomplete="off" required autofocus />
    @else
        <flux:input wire:model="code" :label="__('Authentication code', 'alma')" type="text" inputmode="numeric"
            autocomplete="one-time-code" required autofocus />
    @endif

    <flux:button variant="primary" type="submit" class="w-full">{{ __('Verify', 'alma') }}</flux:button>

    <flux:button variant="ghost" type="button" wire:click="toggleRecovery" class="w-full">
        {{ $useRecoveryCode ? __('Use an authentication code', 'alma') : __('Use a recovery code', 'alma') }}
    </flux:button>
</form>
