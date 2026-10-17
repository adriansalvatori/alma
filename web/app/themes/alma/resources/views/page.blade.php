{{-- Page: block content, with the title shown only when the content does not open with its own hero. --}}
@extends('layouts.app')

@section('content')
  @while (have_posts())
    @php(the_post())
    @php($content = (string) get_post_field('post_content'))

    @unless (\App\Support\Pattern::referencedSlug($content) || has_block('acf/hero'))
      @include('partials.page-header')
    @endunless

    @includeFirst(['partials.content-page', 'partials.content'])
  @endwhile
@endsection
