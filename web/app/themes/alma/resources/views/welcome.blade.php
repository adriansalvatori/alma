@extends('layouts.app')

@section('content')

<div class="hero is-fullheight is-dark is-boxed">
    <div class="hero-body">
        <div class="container is-fluid">
            <h2 class="title is-1">
                <span>Hey there, world!</span>
            </h2>
            <p>Yeah, this is a route, not a wordpress page.</p>
            <a href="{{home_url()}}" class="button is-primary">
            Go to the home-page.
            </a>
        </div>
    </div>
</div>

@endsection
