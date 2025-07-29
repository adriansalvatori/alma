@if (env('LOCOMOTIVE_ENABLED'))
    <x-rts.locomotive/>
@endif

@if (env('BARBA_ENABLED'))
    <x-rts.barba/>
@endif

<x-rts.console/>

