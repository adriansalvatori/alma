<?php

use App\Domains\Account\Actions\AttemptLogin;
use App\Domains\Account\Data\LoginResult;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public const MAX_ATTEMPTS = 5;

    public string $login = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate(AttemptLogin $attempt)
    {
        $this->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('login', sprintf(
                __('Too many sign-in attempts. Try again in %d seconds.', 'alma'),
                RateLimiter::availableIn($key),
            ));
            $this->reset('password');

            return null;
        }

        $result = $attempt->execute($this->login, $this->password, $this->remember);
        $this->reset('password');

        if ($result === LoginResult::Failed) {
            RateLimiter::hit($key, 60);
            // One message for every failure, so the form never reveals which accounts exist.
            $this->addError('login', __('These details do not match our records.', 'alma'));

            return null;
        }

        RateLimiter::clear($key);

        return $result === LoginResult::TwoFactorRequired
            ? redirect()->route('two-factor.challenge')
            : redirect()->intended(route('dashboard'));
    }

    protected function throttleKey(): string
    {
        return 'account-login:'.Str::lower($this->login).'|'.request()->ip();
    }
};
?>

<form wire:submit="authenticate" class="space-y-6">
    <flux:input wire:model="login" :label="__('Email or username', 'alma')" type="text" autocomplete="username" required autofocus />
    <flux:input wire:model="password" :label="__('Password', 'alma')" type="password" autocomplete="current-password" required />

    <div class="flex items-center justify-between">
        <flux:checkbox wire:model="remember" :label="__('Remember me', 'alma')" />
        <flux:link :href="wp_lostpassword_url()" class="text-sm">{{ __('Forgot your password?', 'alma') }}</flux:link>
    </div>

    <flux:button variant="primary" type="submit" class="w-full">{{ __('Sign in', 'alma') }}</flux:button>
</form>
