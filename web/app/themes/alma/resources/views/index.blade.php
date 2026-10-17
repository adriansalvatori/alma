{{-- Index: the posts archive (blog, categories, tags) as a list of post summaries. --}}
@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  <div class="container-page space-y-10 pb-section">
    @if (! have_posts())
      <x-alert type="warning">
        {!! __('Sorry, no results were found.', 'alma') !!}
      </x-alert>

      {!! get_search_form(false) !!}
    @endif

    @while (have_posts())
      @php(the_post())
      @includeFirst(['partials.content-'.get_post_type(), 'partials.content'])
    @endwhile

    {!! get_the_posts_navigation() !!}
  </div>
@endsection

@section('sidebar')
  @include('sections.sidebar')
@endsection
