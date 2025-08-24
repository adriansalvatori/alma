<style>
    html {
        margin-top: 0 !important;
    }

    #wpadminbar {
        display: none !important;
    }
</style>

@if (env('LOCOMOTIVE_ENABLED'))
    <x-rts.locomotive />
@endif

@if (env('BARBA_ENABLED'))
    <x-rts.barba />
@endif

<x-rts.console />

<x-rts.store />
