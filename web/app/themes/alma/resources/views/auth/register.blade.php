@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full max-w-md space-y-8 py-12">
    <flux:heading size="xl" level="1" class="text-center">{{ __('Create your account', 'alma') }}</flux:heading>

    <livewire:account.register-form />

    <flux:text class="text-center">
      {{ __('Already have an account?', 'alma') }}
      <flux:link :href="route('login')">{{ __('Sign in', 'alma') }}</flux:link>
    </flux:text>
  </div>
@endsection
