<?php

namespace App\Domains\Ai\Hooks;

use App\Domains\Ai\Abilities\Ability;
use Illuminate\Contracts\Container\Container;
use Throwable;

/**
 * Registers the categories and abilities listed in config/ai.php with the Abilities API.
 *
 * Core fires its init actions lazily, the first time anything asks for the registry (the MCP
 * adapter on rest_api_init, for example), so registration costs nothing on an ordinary page view.
 */
class RegisterAbilities
{
    public function __construct(private readonly Container $container) {}

    public function register(): void
    {
        add_action('wp_abilities_api_categories_init', [$this, 'registerCategories']);
        add_action('wp_abilities_api_init', [$this, 'registerAbilities']);
    }

    public function registerCategories(): void
    {
        foreach ((array) config('ai.categories', []) as $slug => $category) {
            wp_register_ability_category($slug, [
                'label' => __($category['label'], 'alma'),
                'description' => __($category['description'], 'alma'),
            ]);
        }
    }

    public function registerAbilities(): void
    {
        foreach ($this->abilities() as $ability) {
            wp_register_ability($ability->name(), $ability->definition());
        }
    }

    /**
     * The configured abilities that can run here. One that fails to construct (a missing class
     * after a module was removed) is skipped and logged, never fatal: the rest still register.
     *
     * @return list<Ability>
     */
    public function abilities(): array
    {
        $abilities = [];

        foreach ((array) config('ai.abilities', []) as $class) {
            try {
                $ability = $this->container->make($class);
            } catch (Throwable $e) {
                logger()->warning("AI ability {$class} could not be created: {$e->getMessage()}");

                continue;
            }

            if ($ability instanceof Ability && $ability->available()) {
                $abilities[] = $ability;
            }
        }

        return $abilities;
    }
}
