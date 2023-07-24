@extends('layouts.app')

@section('content')

<div class="hero is-medium">
  <div class="hero-body">
    <div class="container">
      @if (! have_posts())
      @include('components.404')
      @else 
        <div class="container has-text-centered">
          <div class="card-content  has-margin-top-100 has-margin-bottom-100">
            <h2 class="title is-2 has-text-uppercase">@title</h2>
            <p class="has-margin-top-30">@excerpt</p>
          </div>
        </div>
      @endif

      <div class="columns is-multiline">

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