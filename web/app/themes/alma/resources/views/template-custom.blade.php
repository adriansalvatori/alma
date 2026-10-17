{{--
  Template Name: Narrow content
--}}
{{-- Narrow content: a page title over a single readable column, for legal and policy pages. --}}
@extends('layouts.app')

@section('content')
  @while (have_posts())
    @php(the_post())
    @include('partials.page-header')

    <div class="container-page py-12">
      <div class="prose-content mx-auto max-w-3xl">
        @php(the_content())
      </div>
    </div>
  @endwhile
@endsection
