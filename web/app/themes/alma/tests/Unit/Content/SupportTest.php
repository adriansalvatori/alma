<?php

use App\Blocks\Faq;
use App\Blocks\Hero;
use App\Domains\Content\Editor\PageChrome;
use App\Domains\Content\Hooks\CoreButtons;
use App\Support\Assets;
use App\Support\BlockMarkup;
use App\Support\DocumentOutline;
use App\Support\ReadingTime;
use App\Support\ResponsiveImage;
use Illuminate\Filesystem\Filesystem;
use Tests\Support\WordPress\WordPress;

describe('BlockMarkup', function () {
    it('writes a block comment with each value beside its field key', function () {
        $markup = BlockMarkup::block(Hero::class, ['headline' => 'Hello']);
        preg_match('/^<!-- wp:acf\/hero (\{.*\}) \/-->$/', $markup, $m);
        $attributes = json_decode($m[1], true);

        expect($attributes['name'])->toBe('acf/hero')
            ->and($attributes['align'])->toBe('full')
            ->and($attributes['data']['headline'])->toBe('Hello')
            ->and($attributes['data']['_headline'])->toStartWith('field_');
    });

    it('flattens repeater rows the way ACF stores them', function () {
        $data = BlockMarkup::encode(Faq::class, ['items' => [
            ['question' => 'One?', 'answer' => 'Yes.'],
            ['question' => 'Two?', 'answer' => 'No.'],
        ]]);

        expect($data['items'])->toBe(2)
            ->and($data['items_1_question'])->toBe('Two?')
            ->and($data['_items_1_question'])->toStartWith('field_')
            ->and($data['_items'])->toStartWith('field_');
    });

    it('accepts a slug as well as a class', function () {
        expect(BlockMarkup::block('faq'))->toStartWith('<!-- wp:acf/faq ');
    });

    it('fails loudly on a field the block does not have', function () {
        BlockMarkup::block(Hero::class, ['headlin' => 'Typo']);
    })->throws(InvalidArgumentException::class, 'has no field [headlin]');

    it('escapes what would end the comment or confuse the block parser', function () {
        $markup = BlockMarkup::block(Hero::class, ['headline' => 'A -- <b>"x"</b> & y']);

        $json = substr($markup, strlen('<!-- wp:acf/hero '), -strlen(' /-->'));

        expect($json)->not->toContain('--')
            ->and($json)->not->toContain('<')
            ->and(substr_count($markup, '-->'))->toBe(1)
            ->and(json_decode($json, true)['data']['headline'])->toBe('A -- <b>"x"</b> & y');
    });
});

describe('Assets', function () {
    beforeEach(function () {
        $this->dir = theme_path('public/images/__test');
        @mkdir($this->dir, 0755, true);
        // A 2×1 PNG.
        file_put_contents("{$this->dir}/pixel.png", base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAABCAYAAAD0In+KAAAAC0lEQVR42mNgAAIAAAUAAen63NgAAAAASUVORK5CYII='));
    });

    afterEach(function () {
        (new Filesystem)->deleteDirectory($this->dir);
    });

    it('serves the .webp sibling when the build wrote one, and the original otherwise', function () {
        expect(Assets::webp('__test/pixel.png'))->toBe('__test/pixel.png');

        file_put_contents("{$this->dir}/pixel.webp", 'webp');

        expect(Assets::webp('__test/pixel.png'))->toBe('__test/pixel.webp');
    });

    it('treats a zero-byte build output as missing', function () {
        file_put_contents("{$this->dir}/pixel.webp", '');

        expect(Assets::exists('__test/pixel.webp'))->toBeFalse()
            ->and(Assets::webp('__test/pixel.png'))->toBe('__test/pixel.png');
    });

    it('reads dimensions once and caches them', function () {
        expect(Assets::dimensions('__test/pixel.png'))->toBe(['width' => 2, 'height' => 1])
            ->and(WordPress::$transients)->toHaveCount(1);
    });

    it('prints an img with the file\'s own width and height, lazy by default', function () {
        $html = Assets::img('__test/pixel.png', 'A pixel');

        expect($html)->toContain('width="2"', 'height="1"', 'loading="lazy"', 'alt="A pixel"');
    });
});

describe('ResponsiveImage', function () {
    it('accepts whatever an ACF image field returns', function () {
        expect(ResponsiveImage::id(12))->toBe(12)
            ->and(ResponsiveImage::id('12'))->toBe(12)
            ->and(ResponsiveImage::id(['ID' => 12]))->toBe(12)
            ->and(ResponsiveImage::id(null))->toBe(0)
            ->and(ResponsiveImage::html(null))->toBe('');
    });

    it('renders an attachment lazily unless told otherwise', function () {
        $attachment = WordPress::post(['post_type' => 'attachment', 'post_name' => 'photo']);

        expect(ResponsiveImage::html($attachment->ID))->toContain('loading="lazy"')
            ->and(ResponsiveImage::html($attachment->ID, 'full', ['loading' => 'eager']))->toContain('loading="eager"');
    });
});

describe('CoreButtons', function () {
    it('never compiles editor content as Blade', function () {
        $html = (new CoreButtons)->button('', [
            'innerHTML' => '<div class="wp-block-button"><a class="wp-block-button__link" href="/x">@php echo "pwned"; @endphp {{ 1+1 }}</a></div>',
            'attrs' => [],
        ]);

        expect($html)->not->toContain('pwned"')
            ->and($html)->toContain('@php echo')
            ->and($html)->toContain('{{ 1+1 }}')
            ->and($html)->toContain('href="/x"');
    });

    it('maps the outline style to the outline variant', function () {
        $html = (new CoreButtons)->button('', [
            'innerHTML' => '<div><a href="#">Go</a></div>',
            'attrs' => ['className' => 'is-style-outline'],
        ]);

        expect($html)->toContain('Go');
    });
});

describe('PageChrome', function () {
    it('hides every core block not on the allowlist, and keeps acf blocks', function () {
        $script = PageChrome::script(['core/paragraph']);

        expect($script)->toContain("addFilter('blocks.registerBlockType'")
            ->and($script)->toContain('["core/paragraph"]')
            ->and($script)->toContain("name.indexOf('acf/') === 0")
            ->and($script)->toContain('inserter: false');
    });
});

describe('DocumentOutline and ReadingTime', function () {
    it('anchors every h2 once and lists them', function () {
        $outline = DocumentOutline::build('<h2>Intro</h2><p>x</p><h2 id="keep">Kept</h2><h2>Intro</h2>');

        expect($outline['headings'])->toBe([
            ['id' => 'intro', 'text' => 'Intro'],
            ['id' => 'keep', 'text' => 'Kept'],
            ['id' => 'intro-2', 'text' => 'Intro'],
        ])->and($outline['content'])->toContain('<h2 id="intro">Intro</h2>');
    });

    it('estimates at least one minute', function () {
        expect(ReadingTime::minutes('short'))->toBe(1)
            ->and(ReadingTime::minutes(str_repeat('word ', 660)))->toBe(3);
    });
});
