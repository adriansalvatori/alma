@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full max-w-4xl space-y-8 py-12">
    <div class="flex items-center justify-between gap-4">
      <flux:heading size="xl" level="1">
        {{ sprintf(__('Welcome, %s', 'alma'), $user->display_name) }}
      </flux:heading>
      <flux:button variant="ghost" :href="wp_logout_url(home_url('/'))">{{ __('Log out', 'alma') }}</flux:button>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
      <flux:card class="space-y-2">
        <flux:heading>{{ __('Account', 'alma') }}</flux:heading>
        <flux:text>{{ $user->user_email }}</flux:text>
        <flux:link :href="route('settings')">{{ __('Edit profile', 'alma') }}</flux:link>
      </flux:card>

      <flux:card class="space-y-2">
        <flux:heading>{{ __('Security', 'alma') }}</flux:heading>
        @if ($twoFactorEnabled)
          <flux:badge color="green" icon="shield-check">{{ __('Two-factor authentication is on', 'alma') }}</flux:badge>
        @else
          <flux:text>{{ __('Two-factor authentication is off.', 'alma') }}</flux:text>
          <flux:link :href="route('settings')">{{ __('Turn it on', 'alma') }}</flux:link>
        @endif
      </flux:card>
    </div>
  </div>
@endsection
