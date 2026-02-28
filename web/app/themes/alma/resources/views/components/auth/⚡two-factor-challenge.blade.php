<?php

use Livewire\Component;
use App\Services\TwoFactorService;
use App\Services\AuthService;

new class extends Component {
    public string $code = '';

    public function mount()
    {
        if (!session()->has('login.id')) {
            return redirect()->route('login');
        }
    }

    public function verify(TwoFactorService $twoFactor, AuthService $auth)
    {
        $this->validate([
            'code' => 'required|string',
        ]);

        $userId = session('login.id');
        $secret = decrypt(get_user_meta($userId, 'two_factor_secret', true));

        if ($twoFactor->verify($secret, $this->code)) {
            $user = get_user_by('id', $userId);

            $auth->completeLogin($user, session('login.remember', false));

            session()->forget(['login.id', 'login.remember']);

            // Mark the session as 2FA verified
            session()->put('two_factor_verified', true);

            return redirect()->intended(route('dashboard'));
        }

        $this->addError('code', __('The provided two factor authentication code was invalid.', 'alma'));
    }
};
?>

<form wire:submit="verify" class="space-y-6">
    <flux:input wire:model="code" label="Authentication Code" type="text" inputmode="numeric" autofocus
        autocomplete="one-time-code" required />

    <div class="flex items-center justify-end mt-4">
        <flux:button variant="primary" type="submit"
            class="w-full relative flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Verify
        </flux:button>
    </div>
</form>