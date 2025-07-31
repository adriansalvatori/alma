@extends('layouts.app')

@section('content')
    <div data-reveal data-reveal-delay="300" class="hero is-fullheight is-primary is-boxed is-clipped mt-0">
        <div class="is-overlay" data-parallax="scale-opacity" data-video-src="mp4:{{ Vite::asset('resources/images/video/blob.mp4') }}">
        </div>
        <div class="hero-body">
            <div class="container has-text-centered">
                <h2 data-reveal-text data-reveal-delay="300" class="title is-massive has-text-weight-bold">
                    <span>Alma</span><span class="has-text-weight-light">v6</span>
                </h2>
            </div>
        </div>
    </div>
    <div class="section">
        <x-carousel.container :title="'Slideshow'" :item_width="'28vw'" :item_width_mobile="'85vw'">
            @for ($i = 0; $i < 4; $i++)
                <x-carousel.item :width="'28vw'">
                    <div data-reveal data-reveal-delay="{{ ($i + 1) * 100 }}" class="box is-outlined"
                        style="aspect-ratio: 1/1">
                        <div class="title">Hello {{ $i + 1 }}</div>
                    </div>
                </x-carousel.item>
            @endfor
            </x-slideshow>
    </div>
@endsection
