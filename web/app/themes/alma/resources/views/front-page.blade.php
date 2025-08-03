@extends('layouts.app')

@section('content')
    <div class="hero is-fullheight is-dark is-boxed is-clipped" data-reveal>
        <div class="hero-body">
            <div class="container">
                <h1 class="title is-massive" data-reveal-text data-reveal-delay="600">Hello <span class="has-text-weight-bold">Galena</span></h1>
                <livewire:alma-ai />
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
    <div data-reveal data-reveal-delay="300" class="hero is-fullheight is-primary is-boxed is-clipped mt-0">
        <div class="is-overlay" data-parallax="scale-opacity"
            data-video-src="mp4:{{ Vite::asset('resources/images/video/blob.mp4') }}">
        </div>
        <div class="hero-body">
            <div class="container has-text-centered">
                <h2 data-reveal-text data-reveal-delay="300" class="title is-massive has-text-weight-bold">
                    <span>Alma</span><span class="has-text-weight-light">v6</span>
                </h2>
            </div>
        </div>
    </div>
@endsection
