<?php

/*
 * Every Blade view in the theme compiles, and every Livewire tag points at a real component.
 *
 * A view only fails when it is rendered, so a typo in a rarely visited template (a Woo override,
 * an account screen) would otherwise reach production unnoticed.
 */

require_once __DIR__.'/Support.php';

use Illuminate\Support\Facades\Blade;

it('compiles every Blade view to valid PHP', function () {
    $problems = [];
    $scratch = sys_get_temp_dir().'/alma-blade-lint-'.getmypid();
    @mkdir($scratch, 0755, true);

    // The theme's views and each module's own (lead::, ai::, seo::, payment::…).
    foreach ([...convention_files('resources/views', '.blade.php'), ...convention_files('app/Domains', '.blade.php')] as $index => $view) {
        try {
            $compiled = Blade::compileString((string) file_get_contents($view));
        } catch (Throwable $e) {
            $problems[] = convention_relative($view).': '.$e->getMessage();

            continue;
        }

        file_put_contents($file = "{$scratch}/{$index}.php", $compiled);
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $output, $status);

        if ($status !== 0) {
            $problems[] = convention_relative($view).': '.trim(implode(' ', $output));
        }

        $output = [];
    }

    expect($problems)->toBe([]);
});

it('resolves every <livewire:...> tag to a component', function () {
    $unresolved = [];
    // The finder only looks the name up. Livewire::exists() would instantiate (and compile) every
    // single-file component, which is slow and touches the compiled-view cache.
    $finder = app('livewire.finder');
    $exists = fn (string $name) => $finder->resolveSingleFileComponentPath($name)
        || $finder->resolveMultiFileComponentPath($name)
        || $finder->resolveClassComponentClassName($name);

    expect($exists('alma-no-such-component'))->toBeFalse();

    foreach (convention_files('resources/views', '.blade.php') as $view) {
        preg_match_all('/<livewire:([A-Za-z0-9._-]+)/', (string) file_get_contents($view), $matches);

        foreach ($matches[1] as $name) {
            if (in_array($name, ['dynamic-component', 'is', 'styles', 'scripts'], true)) {
                continue;
            }

            if (! $exists($name)) {
                $unresolved[$name][] = convention_relative($view);
            }
        }
    }

    expect($unresolved)->toBe([]);
});

it('declares width and height on every literal <img>, so the browser reserves its space', function () {
    $problems = [];

    foreach ([...convention_files('resources/views', '.blade.php'), ...convention_files('patterns', '.php')] as $file) {
        preg_match_all('/<img\b[^>]*>/is', (string) file_get_contents($file), $matches);

        foreach ($matches[0] as $tag) {
            if (! preg_match('/\swidth=/i', $tag) || ! preg_match('/\sheight=/i', $tag)) {
                $problems[] = convention_relative($file).': '.preg_replace('/\s+/', ' ', $tag);
            }
        }
    }

    expect($problems)->toBe([]);
});
