<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->bootBlocks();
    }

    /**
     * Automatically discover and register block classes.
     */
    protected function bootBlocks()
    {
        $blocksDir = app_path('Blocks');

        if (!is_dir($blocksDir)) {
            return;
        }

        add_action('init', function () use ($blocksDir) {
            foreach (glob($blocksDir . '/*.php') as $file) {
                $class = 'App\\Blocks\\' . basename($file, '.php');

                if (class_exists($class) && is_subclass_of($class, \App\Blocks\BaseBlock::class)) {
                    $reflection = new \ReflectionClass($class);
                    if (!$reflection->isAbstract()) {
                        $block = new $class();
                        $block->register();
                    }
                }
            }
        });
    }
}
