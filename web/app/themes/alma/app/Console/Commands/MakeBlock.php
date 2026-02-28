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
    protected $description = 'Scaffold a new native Gutenberg block (PHP, Blade, block.json)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Ensure Block suffix
        if (!Str::endsWith($name, 'Block')) {
            $name .= 'Block';
        }

        $baseName = str_replace('Block', '', $name);
        $kebabName = Str::kebab($baseName);
        $titleName = Str::title(str_replace('-', ' ', $kebabName));

        // 1. Create PHP Class
        $phpPath = app_path("Blocks/{$name}.php");
        if (file_exists($phpPath)) {
            $this->error("Block {$name} already exists.");
            return;
        }

        $phpContent = <<<PHP
<?php

namespace App\Blocks;

class {$name} extends BaseBlock
{
    public function name(): string
    {
        return '{$kebabName}';
    }

    public function title(): string
    {
        return __('{$titleName}', 'alma');
    }

    public function description(): string
    {
        return __('A custom {$titleName} block.', 'alma');
    }

    public function render(array \$attributes): string
    {
        return view('blocks.{$kebabName}.{$kebabName}', ['attributes' => \$attributes])->render();
    }
}
PHP;

        // 2. Create block.json
        $viewDir = resource_path("views/blocks/{$kebabName}");
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $jsonContent = <<<JSON
{
    "\$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "alma/{$kebabName}",
    "title": "{$titleName}",
    "category": "alma",
    "icon": "block-default",
    "description": "A custom {$titleName} block.",
    "attributes": {
        "title": {
            "type": "string",
            "default": "{$titleName}"
        }
    },
    "supports": {
        "align": [
            "wide",
            "full"
        ],
        "html": false,
        "jsx": true
    },
    "textdomain": "alma"
}
JSON;

        // 3. Create Blade view
        $bladeContent = <<<BLADE
<section class="py-12 {{ \$attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
            {{ \$attributes['title'] ?? '{$titleName}' }}
        </h2>
    </div>
</section>
BLADE;

        file_put_contents($phpPath, $phpContent);
        file_put_contents("{$viewDir}/block.json", $jsonContent);
        file_put_contents("{$viewDir}/{$kebabName}.blade.php", $bladeContent);

        $this->info("Created {$name} successfully!");
        $this->line("- PHP Class: app/Blocks/{$name}.php");
        $this->line("- Block JSON: resources/views/blocks/{$kebabName}/block.json");
        $this->line("- Blade View: resources/views/blocks/{$kebabName}/{$kebabName}.blade.php");
        $this->info("Don't forget to run `composer dump-autoload` if the new block class doesn't show up right away.");
    }
}
