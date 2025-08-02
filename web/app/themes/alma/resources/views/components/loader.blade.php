<div class="preloader is-loading has-background-light">
    <div class="logo">
        @php
            $logoPath = asset('images/logo-primary.svg');
        @endphp

        @if (file_exists(public_path('images/logo-primary.svg')))
            <img src="{{ $logoPath }}" width="200px" alt="" />
        @else
            <span class="title is-3 has-text-dark">{{ $siteName }}</span>
        @endif
    </div>
</div>

    <style>
        .layout {
            transition: ease-out 0.4s;

            &.is-loading {
                opacity: 0.2;
                translate: 0 100px;
                border-radius: var(--radius-large);
            }
        }

        .preloader {
            pointer-events: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            z-index: 9999;
            transition: ease-out 0.4s;
            display: flex;
            height: 0;
            align-items: center;
            justify-content: center;
            overflow: hidden;

            .logo {
                opacity: 0;
                translate: 0 30%;
                transition: ease-out 200ms;
                transition-delay: 100ms;
            }

            &.is-loading {
                border-radius: var(--radius-large);
                top: unset;
                bottom: 0;
                height: 100vh;

                .logo {
                    opacity: 1;
                    translate: 0 0;
                }
            }
        }
    </style>
