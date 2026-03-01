@extends('index')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-zinc-900 dark:text-white">
                    Create new account
                </h2>
            </div>
            <livewire:auth.register-form />
        </div>
    </div>
@endsection
