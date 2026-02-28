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

        // 4. Create edit.jsx
        $jsxContent = <<<JSX
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/block-editor';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: 'py-12'
    });

    return (
        <section { ...blockProps }>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border border-dashed border-zinc-300 p-4 rounded-xl">
                <RichText
                    tagName="h2"
                    className="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl focus:outline-none"
                    value={ attributes.title }
                    onChange={ ( val ) => setAttributes( { title: val } ) }
                    placeholder={ __('Enter {$titleName} title...', 'alma') }
                />
                
                <div className="mt-4 p-4 bg-zinc-50 rounded-lg">
                    <InnerBlocks />
                </div>
            </div>
        </section>
    );
}
JSX;

        file_put_contents($phpPath, $phpContent);
        file_put_contents("{$viewDir}/block.json", $jsonContent);
        file_put_contents("{$viewDir}/{$kebabName}.blade.php", $bladeContent);
        file_put_contents("{$viewDir}/edit.jsx", $jsxContent);

        $this->info("Created {$name} successfully!");
        $this->line("- PHP Class: app/Blocks/{$name}.php");
        $this->line("- JSON: resources/views/blocks/{$kebabName}/block.json");
        $this->line("- Blade: resources/views/blocks/{$kebabName}/{$kebabName}.blade.php");
        $this->line("- React: resources/views/blocks/{$kebabName}/edit.jsx");
        $this->info("Remember to run `composer dump-autoload` if the class is not found, and `npm run build` to compile the JSX.");
    }
}
