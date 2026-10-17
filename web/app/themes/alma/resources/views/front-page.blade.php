{{-- Front page: the page's blocks edge to edge (usually one pattern reference, patterns/home.php). --}}
@extends('layouts.app')

@section('content')
  @while (have_posts())
    @php(the_post())
    <div class="entry-content">
      @php(the_content())
    </div>
  @endwhile
@endsection
