{{-- The fake gateway's hosted checkout page. Deliberately standalone: it stands in for a provider's page. --}}
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Test checkout', 'alma') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f4f5; color: #18181b; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        main { background: #fff; border-radius: 1rem; padding: 2rem; max-width: 26rem; width: calc(100% - 2rem); box-shadow: 0 10px 30px rgb(0 0 0 / .08); }
        .notice { background: #fef3c7; color: #78350f; border-radius: .5rem; padding: .75rem 1rem; font-size: .875rem; }
        .amount { font-size: 2rem; font-weight: 700; margin: 1rem 0 .25rem; }
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-top: 1.5rem; }
        button { border: 0; border-radius: .5rem; padding: .75rem; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .pay { background: #18181b; color: #fff; }
        .decline { background: #e4e4e7; color: #18181b; }
    </style>
</head>

<body>
    <main>
        <p class="notice">{{ __('Test mode. No real payment is taken.', 'alma') }}</p>
        <p class="amount">{{ $payment->money()->format() }}</p>
        <p>{{ $payment->description }}</p>
        <p><small>{{ $payment->customer_email }}</small></p>

        <form method="post" action="{{ $confirmUrl }}" class="actions">
            @csrf
            <button class="pay" name="outcome" value="succeeded">{{ __('Pay', 'alma') }}</button>
            <button class="decline" name="outcome" value="failed">{{ __('Decline', 'alma') }}</button>
        </form>
    </main>
</body>

</html>
