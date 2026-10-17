<?php

use App\Domains\Account\Actions\CompleteLogin;
use App\Domains\Account\Actions\RegisterUser;
use App\Domains\Account\Data\RegistrationData;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    /** Bots submit instantly; people do not. */
    public const MIN_SECONDS = 3;

    public const MAX_PER_HOUR = 5;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** Honeypot: hidden from people, filled in by bots. */
    public string $website = '';

    #[Locked]
    public int $renderedAt = 0;

    public function mount(): void
    {
        abort_unless((bool) get_option('users_can_register'), 404);

        $this->renderedAt = now()->getTimestamp();
    }

    public function register(RegisterUser $register, CompleteLogin $completeLogin)
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email:filter|max:100',
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $key = 'account-register:'.request()->ip();

        if ($this->website !== '' || now()->getTimestamp() - $this->renderedAt < self::MIN_SECONDS || RateLimiter::tooManyAttempts($key, self::MAX_PER_HOUR)) {
            $this->addError('email', __('We could not create an account with these details.', 'alma'));

            return null;
        }

        RateLimiter::hit($key, 3600);

        $user = $register->execute(RegistrationData::fromArray($this->only(['name', 'email', 'password'])));
        $completeLogin->execute($user);

        return redirect()->route('dashboard');
    }
};
?>

<form wire:submit="register" class="space-y-6">
    <flux:input wire:model="name" :label="__('Name', 'alma')" type="text" autocomplete="name" required autofocus />
    <flux:input wire:model="email" :label="__('Email', 'alma')" type="email" autocomplete="email" required />
    <flux:input wire:model="password" :label="__('Password', 'alma')" type="password" autocomplete="new-password" required />
    <flux:input wire:model="password_confirmation" :label="__('Confirm password', 'alma')" type="password" autocomplete="new-password" required />

    <div class="hidden" aria-hidden="true">
        <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <flux:button variant="primary" type="submit" class="w-full">{{ __('Create account', 'alma') }}</flux:button>
</form>
