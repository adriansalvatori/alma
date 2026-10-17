{{-- 404: not found message with a search form. --}}
@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  <div class="container-page space-y-6 pb-section">
    <x-alert type="warning">
      {!! __('Sorry, but the page you are trying to view does not exist.', 'alma') !!}
    </x-alert>

    {!! get_search_form(false) !!}
  </div>
@endsection
