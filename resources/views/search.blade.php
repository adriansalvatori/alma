@extends('layouts.app')

@section('content')

<div class="hero is-medium">
  <div class="hero-body">
    <div class="container">
      @if (! have_posts())
        @include('components.404')
      @endif

      <div class="columns is-multiline">
        <div class="column is-12">
          <div class="title is-4">
          Resultados de su búsqueda: {{ get_search_query() }}
          </div>
        </div>
        @while(have_posts()) @php(the_post())
        <div class="column is-4">
          @include('partials.content-search')
        </div>
        @endwhile

      </div>

      {!! get_the_posts_navigation() !!}
    </div>
  </div>
</div>

@endsection
