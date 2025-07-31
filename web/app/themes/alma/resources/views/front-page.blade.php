@extends('layouts.app')

@section('content')
    <div class="hero is-fullheight is-boxed mt-0">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h2 class="title is-large has-text-weight-bold">
                    <span>Alma</span>
                </h2>
                <p>Yeah, this is a route, not a wordpress page.</p>
                <div class="buttons is-centered mt-4">
                    <a href="{{ home_url() }}" class="button is-primary">
                        Go to the home-page.
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
