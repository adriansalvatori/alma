# AI Development Guidelines for Alma

## Project Stack

This project uses:

* WordPress
* Bedrock
* Acorn (instead of Artisan - use `wp acorn`)
* Alma (Blade-based theme)
* Livewire 4 (Single File Components)
* Flux UI

All generated code must respect architectural separation and modern Livewire 4 conventions.

---

# 1. Directory Structure

Root structure:

```
├── config/
├── web/
│   ├── app/
│   │   ├── themes/
│   │   │   └── alma/
│   │   │       ├── app/
│   │   │       │   ├── Providers/
│   │   │       │   ├── View/Composers/
│   │   │       │   └── Services/
│   │   │       └── resources/
│   │   │           ├── views/
│   │   │           ├── js/
│   │   │           └── css/
│   │   ├── plugins/
│   │   └── mu-plugins/
│   └── wp/
└── vendor/
```

---

# 2. Livewire 4 Single File Components (SFC)

## Location

Livewire 4 SFCs must live inside:

```
web/app/themes/alma/resources/views/
```

They must follow standard Blade view structure.

Example:

```
resources/views/livewire/post-search.blade.php
```

or domain-based grouping:

```
resources/views/pages/blog/search.blade.php
```

Do NOT place Livewire SFCs inside `app/Livewire`.

---

## SFC Structure

Single File Components contain both logic and template in one Blade file.

Example:

```blade
<?php

use Livewire\Component;

new class extends Component {
    public string $search = '';

    public function updatedSearch(): void
    {
        // Handle reactivity
    }
};
?>

<div>
    <input type="text" wire:model.live="search">
    <p>{{ $search }}</p>
</div>
```

---

## SFC Rules

* Public properties must be typed
* Business logic must NOT live inside the SFC
* Heavy queries must NOT run directly inside reactive hooks
* Domain logic must be delegated to Services
* Keep components small and focused
* Avoid unnecessary reactivity
* Avoid N+1 queries
* Keep hydration payloads minimal

---

# 3. Domain Logic Architecture

All domain/business logic must live inside:

```
web/app/themes/alma/app/Services/
```

Livewire SFCs must call Services.

Example pattern:

```php
public function updatedSearch(PostService $posts): void
{
    $this->results = $posts->search($this->search);
}
```

Rules:

* Services contain business rules
* SFCs contain UI state and isolated component logic
* Blade handles presentation
* WordPress handles content
* Flux handles UI components
* **Reactivity:** Use Livewire global events (`#[On('event-name')]` attributes and `$this->dispatch('event-name')`) to communicate between isolated Livewire components (e.g., Cart and Add to Cart buttons).

Separation of concerns is mandatory.

---

# 4. PHP Standards

* Use `declare(strict_types=1);` in PHP classes
* Follow PSR-12
* Type all properties
* Type all parameters
* Declare return types
* Prefer dependency injection
* Avoid global state
* Avoid static logic unless justified

---

# 5. Blade Standards

* Use Blade syntax only
* No raw PHP tags inside templates
* Keep templates logic-light
* Use View Composers where appropriate
* Use components for reusable UI
* Escape output properly

---

# 6. Rendering Livewire Components

Render components using Blade:

```blade
<livewire:post-search />
```

Do not manually instantiate components.

---

# 7. Flux UI Rules

Flux UI is the required UI layer.

## Requirements

* Always prefer Flux components over raw HTML
* Maintain consistent design language
* Avoid inline styles
* Do not override Flux defaults without reason
* Follow accessibility best practices

Example:

```blade
<flux:button variant="primary" wire:click="save">
    <flux:icon.check class="w-5 h-5 mr-2" />
    Save
</flux:button>
```

Never downgrade to plain `<button>` if a Flux equivalent exists.

---

# 8. WooCommerce Integration Rules

When overriding WooCommerce templates or interacting with Woo data:

* **Overrides:** Place template overrides inside `web/app/themes/alma/resources/views/woocommerce/` (e.g. `archive-product.blade.php`, `content-single-product.blade.php`).
* **Design Integration:** WooCommerce outputs raw, unstyled HTML by default. You MUST wrap WooCommerce hooks (like `do_action('woocommerce_before_shop_loop')`) in Tailwind Flex/Grid containers to prevent floating elements from breaking the layout (e.g. overlapping product cards).
* **Native Functions:** Be cautious with native function arguments (e.g. `woocommerce_output_related_products()`). Check if they accept arguments or if they rely purely on filters.
* **Forms & Inputs:** If forced to use native WooCommerce forms (like the checkout or product reviews form), intercept their classes via hooks or replace the template entirely to apply Tailwind CSS forms and Flux UI aesthetics.

---

# 9. Front-End Styling & Tailwind CSS

* **Aesthetics:** The project demands a *premium, beautiful, and elegant* design. Always pay attention to typography, generous whitespace (padding/margins), and structural balance.
* **Component Stacking:** Prevent visual overlaps. If multiple absolute positioned elements exist (like a sale badge and a favorite toggle), group them inside dedicated flex containers or position them in opposite corners (e.g. `top-left` vs `top-right`).
* **Linting/Modern Tailwind:** Use modern, un-aliased Tailwind utility classes. For example:
  * Use `shrink-0` instead of `flex-shrink-0`.
  * Avoid conflicting margin utilities (e.g., `-mx-2` vs `mx-auto` on the same element).
* **Alpine.js:** Use Alpine for simple frontend interactions that don't require server roundtrips (e.g. global keyboard shortcuts like `@keydown.window="cmdK = true"` to open a search modal).

---

# 10. WordPress Integration Rules

* Register CPTs inside Service Providers
* Sanitize all input
* Escape all output
* Use nonces for forms
* Use prepared statements
* Never trust client-side data
* Do not query WP directly inside Blade

---

# 11. Gutenberg Block Architecture (Acorn/Sage)

* **Composer:** Use `Log1x\AcfComposer\Block` to create blocks.
* **Fields:** Use `StoutLogic\AcfBuilder\FieldsBuilder` to define ACF interfaces programmatically.
* **Location:** Blocks must live in `web/app/themes/alma/app/Blocks/` and their views in `web/app/themes/alma/resources/views/blocks/`.
* **State Mapping:** Always map data in the `with()` array to prevent crashing Blade loops if `get_field` returns blank.
* **Nested Blocks:** Set `'jsx' => true` in `$supports` to allow an `<InnerBlocks />` component to render inside the view.

---

# 12. Performance Rules

* Cache expensive WordPress queries
* Avoid heavy logic in reactive lifecycle hooks
* Use lazy loading where appropriate
* Prevent unnecessary re-renders
* Keep Livewire state minimal

---

# 12. Security Rules

* Validate all Livewire input
* Sanitize before persistence
* Escape all output
* Never expose sensitive data in component state
* Use WordPress security best practices

---

# 13. Prohibited Patterns

The agent must NOT:

* Place SFCs in `app/Livewire`
* Put business logic inside Blade templates
* Perform heavy queries inside render cycles
* Mix UI and domain logic
* Bypass Services for convenience
* Inject JavaScript hacks to fix architecture problems

---

# 14. Architectural Philosophy

The system must feel like:

* Laravel discipline
* WordPress flexibility
* Reactive UI without SPA complexity
* Clean, predictable structure

Every generated feature must respect:

Service → Livewire SFC → Flux UI → Blade Layout → WordPress Content

No shortcuts.
