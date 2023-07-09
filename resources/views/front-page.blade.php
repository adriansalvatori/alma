@extends('layouts.app')

@section('content')
@include('partials.intro')

@include('partials.product-slideshow', [
    'title' => __('Doral™️ 2023 Collection - <b>Amsterdam</b>'),
    'tags' => ['Amsterdam'],
    'category' => '',
    ])

@if(function_exists('get_field'))
@include('partials.billboard-list', [
    'billboards' => get_field('billboards_section_1', 81)
    ])
@endif()

@include('partials.hero-demo')

@include('partials.hero')

@include('partials.product-slideshow', [
    'title' => __('<b>Best Sellers</b>'),
    'tags' => [],
    'category' => '',
    ])
@if(function_exists('get_field'))
@include('partials.billboard-list', [
    'billboards' => get_field('billboards_section_2', 81)
    ])
@endif

@endsection
