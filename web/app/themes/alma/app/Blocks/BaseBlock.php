<?php

namespace App\Blocks;

use App\Blocks\Contracts\Block;

abstract class BaseBlock implements Block
{
    /**
     * Get the supported features for the block.
     *
     * @return array
     */
    public function supports(): array
    {
        return [
            'align' => true,
            'html' => false,
        ];
    }

    /**
     * Determine if the block is authorized to be rendered.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Register the block type via WordPress API.
     */
    public function register(): void
    {
        // Try to locate block.json
        $blockJsonPath = get_theme_file_path("resources/views/blocks/{$this->name()}/block.json");

        if (file_exists($blockJsonPath)) {
            register_block_type($blockJsonPath, [
                'render_callback' => [$this, 'renderCallback']
            ]);
        } else {
            // Register dynamically without block.json fallback
            register_block_type('alma/' . $this->name(), [
                'title' => $this->title(),
                'description' => $this->description(),
                'render_callback' => [$this, 'renderCallback'],
                'supports' => $this->supports(),
            ]);
        }
    }

    /**
     * The internal render callback for WordPress.
     *
     * @param array $attributes
     * @param string $content
     * @return string
     */
    public function renderCallback($attributes = [], $content = ''): string
    {
        if (!$this->authorize()) {
            return '';
        }

        return (string) $this->render($attributes);
    }
}
