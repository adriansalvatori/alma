<?php

namespace App\Dashboard\Contracts;

interface Widget
{
    /**
     * Get the widget's unique identifier.
     *
     * @return string
     */
    public function id(): string;

    /**
     * Get the widget's title.
     *
     * @return string
     */
    public function title(): string;

    /**
     * Render the widget's content.
     *
     * @return string
     */
    public function render(): string;

    /**
     * Define the widget's sort order.
     *
     * @return int
     */
    public function order(): int;
}
