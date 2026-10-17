{{-- Section heading: centred heading with an optional introduction, shared by the section blocks. --}}
@if ($heading || ($intro ?? null))
  <header class="mx-auto mb-12 max-w-2xl text-center">
    @if ($heading)
      <h2 class="text-heading font-bold tracking-tight text-zinc-900 dark:text-white">{{ $heading }}</h2>
    @endif
    @if ($intro ?? null)
      <p class="text-lead mt-4 text-zinc-600 dark:text-zinc-400">{{ $intro }}</p>
    @endif
  </header>
@endif
