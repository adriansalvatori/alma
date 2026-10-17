<?php

/*
 * The block authoring contract (docs/blocks.md) for every block in app/Blocks.
 */

require_once __DIR__.'/Support.php';

use App\Support\BlockMarkup;
use Illuminate\Support\Str;
use Tests\Support\WordPress\WordPress;

it('finds the theme blocks', function () {
    expect(array_keys(convention_blocks()))->toContain('hero', 'faq', 'features-grid');
});

it('gives every block a name, a description, a view and preview data', function () {
    $problems = [];

    foreach (convention_blocks() as $slug => $block) {
        if (blank($block->name) || blank($block->description)) {
            $problems[] = "{$slug}: needs \$name and \$description";
        }

        if (! is_file(theme_path("resources/views/blocks/{$slug}.blade.php"))) {
            $problems[] = "{$slug}: no view at resources/views/blocks/{$slug}.blade.php";
        }

        if (! is_array($block->example) || $block->example === []) {
            $problems[] = "{$slug}: \$example needs preview data (the inserter preview)";
        }
    }

    expect($problems)->toBe([]);
});

it('opens every block view with a comment saying what it renders', function () {
    $problems = [];

    foreach (array_keys(convention_blocks()) as $slug) {
        $view = theme_path("resources/views/blocks/{$slug}.blade.php");

        if (is_file($view) && ! preg_match('/\A\s*\{\{--\s*\S/', (string) file_get_contents($view))) {
            $problems[] = "resources/views/blocks/{$slug}.blade.php";
        }
    }

    expect($problems)->toBe([]);
});

it('names every field group after its block slug, so patterns can address its fields', function () {
    $problems = [];

    foreach (convention_blocks() as $slug => $block) {
        $expected = 'group_'.Str::snake(str_replace('-', '_', $slug)).'_block';
        $actual = $block->fields()['key'] ?? null;

        if ($actual !== $expected) {
            $problems[] = "{$slug}: field group is [{$actual}], expected [{$expected}] (Builder::make('".substr($expected, 6)."'))";
        }
    }

    expect($problems)->toBe([]);
});

it('uses text fields for links, since ACF\'s URL field rejects paths and anchors', function () {
    $problems = [];

    foreach (convention_blocks() as $slug => $block) {
        $walk = function (array $fields) use (&$walk, &$problems, $slug) {
            foreach ($fields as $field) {
                if (($field['type'] ?? '') === 'url') {
                    $problems[] = "{$slug}.{$field['name']}";
                }

                $walk($field['sub_fields'] ?? []);
            }
        };

        $walk($block->fields()['fields'] ?? []);
    }

    expect($problems)->toBe([]);
});

it('renders every block\'s data with nothing stored', function () {
    WordPress::$acf = [];

    foreach (convention_blocks() as $slug => $block) {
        expect($block->with())->toBeArray("{$slug}::with() failed with no stored fields");
    }
});

it('round-trips pattern data through the block\'s real field keys', function () {
    $markup = BlockMarkup::block('faq', ['heading' => 'Q&A', 'items' => [['question' => 'Why?', 'answer' => 'Because.']]]);

    expect($markup)->toStartWith('<!-- wp:acf/faq ')
        ->and($markup)->toContain('"_heading":"field_')
        ->and($markup)->toContain('"items_0_question":"Why?"');
});
