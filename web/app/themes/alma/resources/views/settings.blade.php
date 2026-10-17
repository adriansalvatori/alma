@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full max-w-4xl space-y-12 py-12">
    <div class="flex items-center justify-between gap-4">
      <flux:heading size="xl" level="1">{{ __('Settings', 'alma') }}</flux:heading>
      <flux:link :href="route('dashboard')">{{ __('Back to dashboard', 'alma') }}</flux:link>
    </div>

    <livewire:account.profile-form />

    <flux:separator />

    <livewire:account.security-panel />
  </div>
@endsection
