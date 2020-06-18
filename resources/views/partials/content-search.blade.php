  @if (get_post_type() === 'inversion-rentando' || get_post_type() === 'propiedades')
    @include('components.card')
  @else
    @include('components.category-card')
  @endif

