@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full max-w-md space-y-8 py-12">
    <div class="space-y-2 text-center">
      <flux:heading size="xl" level="1">{{ __('Two-factor authentication', 'alma') }}</flux:heading>
      <flux:text>{{ __('Enter the code from your authenticator app, or one of your recovery codes.', 'alma') }}</flux:text>
    </div>

    <livewire:account.two-factor-challenge />
  </div>
@endsection
