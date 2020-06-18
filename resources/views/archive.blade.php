@extends('layouts.app')

@section('content')

<div class="hero is-medium">
  <div class="hero-body">
    <div class="container">
      @if (! have_posts())
      <div class="columns level">
        <div class="column is-5">
          <h3 class="title is-5">Ups, esto es un error 404. 🤷‍♂️</h3>
          <h1 class="title is-3">Lo que usted está buscando, o bien no existe o simplemente no se encuentra aquí.</h1>
        </div>
        <div class="column is-5">
          <h4 class="title is-4">¿Quiere volver a intentarlo?</h4>
          @include('forms.search')
        </div>
      </div>
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