{{-- A heading, an intro and the lead capture form (Lead module). --}}
<section {{ $attributes->merge(['class' => 'alma-lead-form-block py-12']) }}>
  <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
    @if ($heading !== '')
      <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $heading }}</h2>
    @endif

    @if ($intro !== '')
      <p class="mt-3 text-lg text-zinc-600 dark:text-zinc-400">{{ $intro }}</p>
    @endif

    <div class="mt-8">
      <livewire:lead.form
        :form-key="$formKey"
        :fields="$fields"
        :require-consent="$requireConsent"
        :consent-text="$consentText"
        :button-label="$buttonLabel"
        :success-message="$successMessage"
        :redirect-url="$redirectUrl"
        :wire:key="'lead-form-'.($block->block->id ?? $formKey)"
      />
    </div>
  </div>
</section>
