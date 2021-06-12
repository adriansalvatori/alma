@extends('layouts.app')

@section('content')

@include('partials.page-header')
  <div class="is-relative">
    <div class="container py-6">
      <h1 class="title is-1 has-margin-bottom-40">@title</h1>
      <div class="content">
        @while(have_posts()) @php(the_post())
          @includeFirst(['partials.content-page', 'partials.content'])
        @endwhile
      </div>
    </div>
  </div>
  
@endsection
