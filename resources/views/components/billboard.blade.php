<billboard-item data-inertia data-inertia-reveal class="column {{$size}}">
    <div class="hero is-primary is-fullheight is-rounded is-clipped is-relative">
        @if($type == 'image')
        <div class="is-overlay is-parallax-contain" style="background: url('{{$background['url']}}');"></div>
        @else
        <div class="is-overlay is-parallax-video" data-url="{{$background['url']}}"></div>
        @endif
        <div class="hero-body">
            <div class="container">
                <div class="columns">
                    <div class="column is-8">
                        <h2 data-inertia data-inertia-reveal class="title is-5">{!!$title!!}</h2>
                        <h3 data-inertia data-inertia-reveal data-inertia-delay="300" class="title is-1">{!!$super!!}</h3>
                    </div>
                    <div class="column is-4"></div>
                </div>
            </div>
        </div>
    </div>
</billboard-item>
