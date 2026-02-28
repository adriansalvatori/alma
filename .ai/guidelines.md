# AI Development Guidelines for Alma

## Project Stack

This project uses:

* WordPress
* Bedrock
* Acorn
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

use Livewire\Volt\Component;

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
* SFCs contain UI state
* Blade handles presentation
* WordPress handles content
* Flux handles UI components

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
<x-flux::button wire:click="save">
    Save
</x-flux::button>
```

Never downgrade to plain `<button>` if a Flux equivalent exists.

---

# 8. WordPress Integration Rules

* Register CPTs inside Service Providers
* Sanitize all input
* Escape all output
* Use nonces for forms
* Use prepared statements
* Never trust client-side data
* Do not query WP directly inside Blade

---

# 9. Performance Rules

* Cache expensive WordPress queries
* Avoid heavy logic in reactive lifecycle hooks
* Use lazy loading where appropriate
* Prevent unnecessary re-renders
* Keep Livewire state minimal

---

# 10. Security Rules

* Validate all Livewire input
* Sanitize before persistence
* Escape all output
* Never expose sensitive data in component state
* Use WordPress security best practices

---

# 11. Prohibited Patterns

The agent must NOT:

* Place SFCs in `app/Livewire`
* Put business logic inside Blade templates
* Perform heavy queries inside render cycles
* Mix UI and domain logic
* Bypass Services for convenience
* Inject JavaScript hacks to fix architecture problems

---

# 12. Architectural Philosophy

The system must feel like:

* Laravel discipline
* WordPress flexibility
* Reactive UI without SPA complexity
* Clean, predictable structure

Every generated feature must respect:

Service → Livewire SFC → Flux UI → Blade Layout → WordPress Content

No shortcuts.
