@extends('layouts.app')

@section('content')

@include('partials.page-header')
  <div class="is-relative">
    <div class="container py-6">
   
      <div class="content">
        @while(have_posts()) @php(the_post())
          @includeFirst(['partials.content-page', 'partials.content'])
        @endwhile
      </div>
    </div>
  </div>
  
@endsection
