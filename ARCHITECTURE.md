# Alma Architecture

A multipurpose WordPress + Laravel hybrid starter kit for building full applications (websites, ecommerce, SaaS, etc.).

## Stack Overview

- **WordPress** - Content management
- **Bedrock** - Modern WordPress project structure
- **Acorn** - Laravel components in WordPress (replaces Artisan with `wp acorn`)
- **Sage/Alma** - Blade-based theme with Tailwind CSS
- **Livewire 4** - Reactive single-file components
- **Flux UI** - Premium component library
- **WooCommerce** - E-commerce functionality

## Directory Structure

```
v7.alma/
├── config/                          # WordPress configuration
│   ├── application.php              # Base config
│   └── environments/                # Environment-specific configs
├── web/
│   ├── app/
│   │   ├── mu-plugins/              # Must-use plugins
│   │   ├── plugins/                 # Regular plugins
│   │   ├── themes/
│   │   │   └── alma/                # Main theme
│   │   │       ├── app/             # Laravel-style app code
│   │   │       │   ├── Console/     # WP-CLI commands
│   │   │       │   ├── Dashboard/   # Dashboard widgets
│   │   │       │   ├── Http/        # Controllers & Middleware
│   │   │       │   ├── Providers/   # Service providers
│   │   │       │   ├── Services/    # Business logic
│   │   │       │   └── View/        # View composers
│   │   │       ├── resources/       # Front-end assets
│   │   │       │   ├── .blocks/     # Compiled block metadata
│   │   │       │   ├── css/         # Stylesheets
│   │   │       │   ├── js/          # JavaScript
│   │   │       │   └── views/       # Blade templates
│   │   │       │       ├── auth/    # Authentication views
│   │   │       │       ├── blocks/  # Gutenberg blocks
│   │   │       │       ├── components/ # Livewire SFCs
│   │   │       │       ├── layouts/ # Layout templates
│   │   │       │       ├── partials/ # Reusable partials
│   │   │       │       ├── sections/ # Header/footer
│   │   │       │       └── woocommerce/ # Woo overrides
│   │   │       ├── routes/          # Application routes
│   │   │       └── public/          # Compiled assets
│   │   └── uploads/                 # Media uploads
│   └── wp/                          # WordPress core
├── vendor/                          # Composer dependencies
└── bin/                             # Build scripts
```

## Core Concepts

### 1. Service Architecture

All business logic lives in `app/Services/`:

```php
namespace App\Services;

class AuthService
{
    public function login(array $credentials)
    {
        // Business logic here
    }
}
```

Controllers and Livewire components delegate to services.

### 2. Livewire Single File Components

Livewire 4 SFCs live in `resources/views/`:

```blade
<?php

use Livewire\Component;

new class extends Component {
    public string $search = '';
};
?>

<div>
    <input wire:model.live="search">
</div>
```

**Rules:**
- Public properties must be typed
- Business logic delegated to Services
- Keep components small and focused
- Use Flux UI components

### 3. Native Gutenberg Blocks

Single-file blocks with JSON frontmatter:

```blade
{{--
{
    "name": "alma/hero",
    "title": "Hero",
    "attributes": {
        "title": {"type": "string", "control": "TextControl"}
    }
}
--}}

@php
    $title = $attributes['title'] ?? 'Hero';
@endphp

<section>
    <h2>{{ $title }}</h2>
</section>
```

Create with: `wp acorn make:block Hero`

### 4. Routing System

Routes defined in `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

Middleware aliases registered in `ThemeServiceProvider`.

### 5. Blade Templates

Layout: `resources/views/layouts/app.blade.php`
Templates extend using `@yield('content')`

## Development Workflow

### Setup
```bash
composer install
npm install
bash bin/setup.sh
```

### Development
```bash
npm run dev        # Start Vite dev server
wp acorn serve     # Optional: PHP dev server
```

### Build
```bash
npm run build      # Production build
```

### CLI Commands
```bash
wp acorn make:block BlockName    # Create Gutenberg block
wp acorn list                    # List available commands
```

## Key Patterns

### Service → Component → View

```
Service (business logic)
  ↓
Livewire SFC (UI state)
  ↓
Flux UI (presentation)
  ↓
Blade Layout (structure)
```

### WordPress Integration

- Register CPTs in Service Providers
- Sanitize all input
- Escape all output
- Use nonces for forms
- Never query WP directly in Blade

### Performance

- Cache expensive queries
- Avoid heavy logic in reactive hooks
- Use lazy loading
- Keep Livewire state minimal

## Security

- Validate all Livewire input
- Sanitize before persistence
- Escape all output
- Use prepared statements
- Never expose sensitive data in component state

## Testing

*Currently using WordPress test suite (TODO: add Pest)*

## Deployment

```bash
npm run deploy:zip   # Create deployment package
```

## Environment Variables

See `.env.example` for required variables:
- `DB_NAME`, `DB_USER`, `DB_PASSWORD`
- `WP_HOME`, `WP_SITEURL`
- `WP_ENV` (development/staging/production)
- Security keys (generate via [roots.io/salts.html](https://roots.io/salts.html))
