<?php

namespace App\Domains\Content;

use App\Domains\Content\Console\BlockInventoryCommand;
use App\Domains\Content\Deploy\EnsurePatternPages;
use App\Domains\Content\Editor\PageChrome;
use App\Domains\Content\Hooks\BlockCategories;
use App\Domains\Content\Hooks\CoreButtons;
use App\Domains\Content\Hooks\FrontEndCleanup;
use App\Domains\Content\Hooks\MediaDefaults;
use App\Domains\Content\Patterns\PatternPageEditing;
use App\Domains\Content\Patterns\ThemePatterns;
use App\Infrastructure\Providers\ModuleServiceProvider;
use App\Support\Navigate;
use Illuminate\Support\Facades\Blade;

/**
 * Pages, patterns, blocks and the front end: everything an editor composes a page from.
 *
 * Blocks themselves live in app/Blocks (ACF Composer discovers them there); this module owns
 * how pages are built from them and how the editor presents them. See docs/blocks.md.
 */
class ContentServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        ThemePatterns::class,
        PatternPageEditing::class,
        PageChrome::class,
        BlockCategories::class,
        CoreButtons::class,
        FrontEndCleanup::class,
        MediaDefaults::class,
    ];

    protected array $commands = [
        BlockInventoryCommand::class,
    ];

    protected array $deployTasks = [
        EnsurePatternPages::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // @navigate prints wire:navigate on a plain link when content.navigate is on.
        Blade::directive('navigate', fn () => '<?php echo \\'.Navigate::class.'::attribute(); ?>');
    }
}
