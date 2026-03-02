<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:block {name : The name of the block (e.g. MyNewBlock)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new native single-file Blade block with JSON frontmatter';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $kebabName = Str::kebab($name);
        $titleName = Str::title(str_replace('-', ' ', $kebabName));

        $viewPath = resource_path("views/blocks/{$kebabName}.blade.php");

        if (file_exists($viewPath)) {
            $this->error("Block {$kebabName}.blade.php already exists.");
            return;
        }

        $bladeContent = <<<BLADE
{{--
{
    "name": "alma/{$kebabName}",
    "title": "{$titleName}",
    "description": "A custom {$titleName} block.",
    "category": "alma",
    "icon": "block-default",
    "supports": {
        "align": true,
        "multiple": true,
        "jsx": true,
        "color": {
            "background": true,
            "text": true,
            "gradient": true
        }
    },
    "attributes": {
        "title": {
            "type": "string",
            "control": "TextControl",
            "label": "Title",
            "default": "{$titleName}"
        }
    }
}
--}}

@php
    \$title = \$attributes['title'] ?? '{$titleName}';
@endphp

<section class="py-12 {{ \$block->classes ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl text-center">
            {{ \$title }}
        </h2>
    </div>
</section>
BLADE;

        if (!is_dir(dirname($viewPath))) {
            mkdir(dirname($viewPath), 0755, true);
        }

        file_put_contents($viewPath, $bladeContent);

        $this->info("Successfully created native block: resources/views/blocks/{$kebabName}.blade.php");
        $this->info("Run `npm run build` or `npm run dev` to compile the block metadata.");
    }
}
