@extends('index')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-zinc-900 dark:text-white">
                    Two-Factor Challenge
                </h2>
                <p class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    Please enter the authentication code provided by your authenticator application.
                </p>
            </div>
            <livewire:auth.two-factor-challenge />
        </div>
    </div>
@endsection
