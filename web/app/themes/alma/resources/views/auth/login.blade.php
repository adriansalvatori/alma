@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full max-w-md space-y-8 py-12">
    <flux:heading size="xl" level="1" class="text-center">{{ __('Sign in to your account', 'alma') }}</flux:heading>

    @if (session('status'))
      <flux:callout variant="warning" icon="information-circle" :heading="session('status')" />
    @endif

    <livewire:account.login-form />

    @if (get_option('users_can_register'))
      <flux:text class="text-center">
        {{ __('New here?', 'alma') }}
        <flux:link :href="route('register')">{{ __('Create an account', 'alma') }}</flux:link>
      </flux:text>
    @endif
  </div>
@endsection
