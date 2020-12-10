@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="content">
      @while(have_posts()) @php(the_post())
        @include('partials.page-header')
        @includeFirst(['partials.content-page', 'partials.content'])
      @endwhile
    </div>
  </div>
@endsection
