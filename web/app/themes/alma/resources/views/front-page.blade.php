@extends('layouts.app')

@section('content')
    <div data-reveal data-reveal-delay="300" class="hero is-fullheight is-primary is-boxed mt-0">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h2 data-reveal-text data-reveal-delay="300" class="title is-massive has-text-weight-bold">
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
    <div class="section">
        <x-carousel.container :title="'Slideshow'" :item_width="'28vw'" :item_width_mobile="'85vw'">
            @for ($i = 0; $i < 4; $i++)
                <x-carousel.item :width="'28vw'">
                   <div data-reveal data-reveal-delay="{{ ($i + 1) * 100 }}" class="box is-outlined" style="aspect-ratio: 1/1">
                        <div class="title">Hello {{ $i + 1 }}</div>
                    </div>
                </x-carousel.item>
            @endfor
        </x-slideshow>
    </div>
@endsection
