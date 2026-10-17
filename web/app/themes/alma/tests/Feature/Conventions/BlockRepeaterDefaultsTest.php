<?php

/*
 * A repeater's preset rows are what the block renders while it is empty, and what the sidebar
 * loads (HasRepeaterDefaults). These must be the same rows, using only real sub-fields, and a
 * repeater without presets must render nothing of its own.
 */

require_once __DIR__.'/Support.php';

use App\Blocks\Concerns\HasRepeaterDefaults;
use Tests\Support\WordPress\WordPress;

it('renders exactly the preset rows for an empty repeater, built from real sub-fields', function () {
    WordPress::$acf = [];
    $problems = [];

    foreach (convention_blocks() as $slug => $block) {
        $presets = in_array(HasRepeaterDefaults::class, class_uses($block), true) ? $block::repeaterDefaults() : [];
        $repeaters = array_filter(convention_fields($block), fn (array $field) => $field['type'] === 'repeater');

        foreach ($presets as $name => $rows) {
            if (! isset($repeaters[$name])) {
                $problems[] = "{$slug}: preset rows for [{$name}], which is not a repeater";

                continue;
            }

            $subFields = array_column($repeaters[$name]['sub_fields'] ?? [], 'name');

            foreach ($rows as $index => $row) {
                foreach (array_diff(array_keys($row), $subFields) as $unknown) {
                    $problems[] = "{$slug}.{$name}[{$index}]: [{$unknown}] is not a sub-field";
                }
            }

            if (method_exists($block, 'rows') && convention_call($block, 'rows', $name) !== array_values($rows)) {
                $problems[] = "{$slug}.{$name}: an empty repeater does not render its preset rows";
            }
        }

        foreach (array_diff_key($repeaters, $presets) as $name => $field) {
            if (method_exists($block, 'rows') && convention_call($block, 'rows', $name) !== []) {
                $problems[] = "{$slug}.{$name}: renders rows the sidebar does not show (add them to repeaterDefaults())";
            }
        }
    }

    expect($problems)->toBe([]);
});

it('keeps an emptied repeater empty instead of falling back to presets', function () {
    $block = convention_blocks()['faq'];

    WordPress::$acf['block'] = ['items' => false];

    expect(convention_call($block, 'rows', 'items'))->toBe([]);
});
