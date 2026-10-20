{{-- Full-screen menu: every link in display type, rising one after another as it opens; an optional aside (who you are, how to reach you) on the left and the brand in the top bar. Closed, it's inert, so its links can't be focused or read out. Open it from any button with onclick="toggleKitMenu()" and aria-controls="kit-menu". --}}
@props(['links' => []])

<div id="kit-menu" {{ $attributes->class(['kit-menu fixed inset-0 z-[60] flex flex-col bg-zinc-950 text-white']) }} inert>
    <script>
        window.toggleKitMenu = () => {
            const menu = document.getElementById('kit-menu');
            const open = menu.classList.toggle('is-open');

            menu.inert = ! open;
            document.querySelectorAll('[aria-controls="kit-menu"]').forEach((button) => button.setAttribute('aria-expanded', String(open)));
        };

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && document.querySelector('#kit-menu.is-open')) {
                window.toggleKitMenu();
            }
        });
    </script>

    <div class="container-page flex items-center justify-between pt-6">
        <div>{{ $brand ?? '' }}</div>
        <button type="button" class="cursor-pointer p-2" onclick="toggleKitMenu()" aria-controls="kit-menu" aria-label="{{ __('Close the menu', 'alma') }}">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true"><path d="M1 1l20 20M21 1 1 21" stroke="currentColor" stroke-width="2" /></svg>
        </button>
    </div>

    <div class="container-page grid flex-1 content-center gap-12 py-12 md:grid-cols-12">
        @isset($aside)
            <div class="type-body text-white/70 md:col-span-4">{{ $aside }}</div>
        @endisset
        <nav aria-label="{{ __('Menu', 'alma') }}" @class(['md:col-span-8' => isset($aside), 'md:col-span-12' => ! isset($aside)])>
            <ul class="space-y-1">
                @foreach ($links as $link)
                    <li class="kit-menu-link" style="--i: {{ $loop->index }}">
                        <x-kit.arrow-link :href="$link['url']" class="type-menu hover:text-brand-500" onclick="toggleKitMenu()">{{ $link['label'] }}</x-kit.arrow-link>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</div>
