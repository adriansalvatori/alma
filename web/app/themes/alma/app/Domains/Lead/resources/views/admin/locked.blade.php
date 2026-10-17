{{-- Note under a settings field that .env pins. --}}
<p class="description"><span class="dashicons dashicons-lock" aria-hidden="true"></span> {!! sprintf(esc_html__('Set by %s in .env for this environment.', 'alma'), '<code>'.esc_html($variable).'</code>') !!}</p>
