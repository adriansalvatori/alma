<?php

use App\Domains\Account\Actions\UpdateProfile;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $currentPassword = '';

    public bool $saved = false;

    public function mount(): void
    {
        $user = $this->user();
        $this->name = (string) $user->display_name;
        $this->email = (string) $user->user_email;
    }

    public function save(UpdateProfile $update): void
    {
        $this->saved = false;

        $this->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email:filter|max:100',
        ]);

        try {
            $update->execute($this->user(), $this->name, $this->email, $this->currentPassword);
        } finally {
            $this->reset('currentPassword');
        }

        $this->saved = true;
    }

    protected function user(): WP_User
    {
        $user = wp_get_current_user();
        abort_unless($user->exists(), 403);

        return $user;
    }
};
?>

<form wire:submit="save" class="space-y-6 max-w-md">
    <flux:heading size="lg">{{ __('Profile', 'alma') }}</flux:heading>

    <flux:input wire:model="name" :label="__('Name', 'alma')" autocomplete="name" required />
    <flux:input wire:model="email" :label="__('Email', 'alma')" type="email" autocomplete="email" required />
    <flux:input wire:model="currentPassword" :label="__('Current password', 'alma')" type="password"
        autocomplete="current-password" :description="__('Only needed to change your email.', 'alma')" />

    <div class="flex items-center gap-3">
        <flux:button variant="primary" type="submit">{{ __('Save', 'alma') }}</flux:button>
        @if ($saved)
            <flux:text>{{ __('Saved.', 'alma') }}</flux:text>
        @endif
    </div>
</form>
