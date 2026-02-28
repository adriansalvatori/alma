<?php

use Livewire\Component;
use App\Services\AuthService;

new class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(AuthService $auth)
    {
        $this->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $auth->login([
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ]);

        if (is_wp_error($result)) {
            $this->addError('email', $result->get_error_message());
            return;
        }

        if ($result['requires_2fa']) {
            session([
                'login.id' => $result['user_id'],
                'login.remember' => $this->remember,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended(route('dashboard'));
    }
};
?>

<form wire:submit="login" class="space-y-6">
    <flux:input wire:model="email" label="Email / Username" type="text" required autofocus />
    <flux:input wire:model="password" label="Password" type="password" required />
    <flux:checkbox wire:model="remember" label="Remember me" />

    <div class="flex items-center justify-end mt-4">
        <flux:button variant="primary" type="submit"
            class="w-full relative flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Sign in
        </flux:button>
    </div>
</form>