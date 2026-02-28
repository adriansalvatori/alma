<?php

namespace App\Blocks\Contracts;

interface Block
{
    /**
     * Get the name of the block.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the title of the block.
     *
     * @return string
     */
    public function title(): string;

    /**
     * Get the description of the block.
     *
     * @return string
     */
    public function description(): string;

    /**
     * Render the block.
     *
     * @param array $attributes
     * @return string
     */
    public function render(array $attributes, string $content = ''): string;

    /**
     * Get the supported features for the block.
     *
     * @return array
     */
    public function supports(): array;

    /**
     * Determine if the block is authorized to be rendered.
     *
     * @return bool
     */
    public function authorize(): bool;
}
