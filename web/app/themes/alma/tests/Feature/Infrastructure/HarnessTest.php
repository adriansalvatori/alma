<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;

it('renders Blade with WordPress helpers available', function () {
    expect(Blade::render('{{ __("Hello", "alma") }} {{ home_url("/x") }}'))->toBe('Hello https://example.test/x');
});

it('can test a Livewire component', function () {
    Livewire::test(new class extends Component
    {
        public int $count = 0;

        public function increment(): void
        {
            $this->count++;
        }

        public function render(): string
        {
            return '<div>{{ $count }}</div>';
        }
    })->call('increment')->assertSet('count', 1)->assertSee('1');
});
