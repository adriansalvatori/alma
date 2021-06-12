@if (get_post_type() === 'post')
  @include('components.card')
@else
  @include('components.category-card')
@endif

