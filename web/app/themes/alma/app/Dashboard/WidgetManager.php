<?php

namespace App\Dashboard;

use App\Dashboard\Contracts\Widget;
use Illuminate\Support\Collection;

class WidgetManager
{
    /**
     * @var Collection
     */
    protected Collection $widgets;

    public function __construct()
    {
        $this->widgets = collect();
    }

    /**
     * Register a new widget.
     *
     * @param Widget $widget
     * @return void
     */
    public function register(Widget $widget): void
    {
        $this->widgets->put($widget->id(), $widget);
    }

    /**
     * Get all registered widgets, sorted by order.
     *
     * @return Collection
     */
    public function getWidgets(): Collection
    {
        return $this->widgets->sortBy(fn(Widget $widget) => $widget->order());
    }

    /**
     * Render all widgets.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->getWidgets()->map(fn(Widget $widget) => $widget->render())->implode('');
    }
}
