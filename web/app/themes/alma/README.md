# Alma Theme

Advanced hybrid WordPress theme with Laravel Blade, Livewire 4, and Flux UI.

## Overview

Alma is a **modern WordPress theme** that brings Laravel development patterns to WordPress:

- **Blade templating** - Clean, component-based views
- **Livewire 4** - Reactive single-file components
- **Flux UI** - Premium UI components
- **Tailwind CSS 4** - Modern utility-first styling
- **Acorn** - Laravel framework integration
- **Native Blocks** - Single-file Gutenberg blocks
- **WooCommerce** - Full e-commerce support

## Features

### 🎨 Front-End
- Blade template engine
- Livewire reactive components
- Flux UI component library
- Tailwind CSS 4
- Dark mode support
- View transitions
- Hot module replacement

### 🏗️ Architecture
- Service-oriented design
- Laravel MVC pattern
- PSR-4 autoloading
- Dependency injection
- Middleware system
- Route definitions

### 🧩 Blocks
- Native single-file blocks
- JSON frontmatter configuration
- No ACF Pro required
- Auto-generated block.json
- Custom block category

### 🛍️ E-commerce
- WooCommerce template overrides
- Custom styling with Tailwind
- Flux UI components
- Cart integration
- Checkout customization

### 🔐 Authentication
- User registration
- Login/logout
- 2FA support
- Role management
- Protected routes

## Quick Start

### Create Block
```bash
wp acorn make:block Hero
npm run dev
```

### Create Component
Create `resources/views/livewire/search.blade.php`:
```blade
<?php

use Livewire\Component;

new class extends Component {
    public string $query = '';
};
?>

<div>
    <input wire:model.live="query">
</div>
```

### Create Service
Create `app/Services/SearchService.php`:
```php
<?php

namespace App\Services;

class SearchService
{
    public function search(string $query): array
    {
        return get_posts(['s' => $query]);
    }
}
```

## Directory Structure

```
alma/
├── app/
│   ├── Console/          # WP-CLI commands
│   ├── Http/             # Controllers, Middleware
│   ├── Providers/        # Service providers
│   ├── Services/         # Business logic
│   └── View/             # View composers
├── resources/
│   ├── .blocks/          # Compiled block metadata
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── views/
│       ├── auth/         # Auth templates
│       ├── blocks/       # Gutenberg blocks
│       ├── components/   # Livewire SFCs
│       ├── layouts/      # Layout templates
│       ├── partials/     # Reusable partials
│       ├── sections/     # Header/footer
│       └── woocommerce/  # Woo overrides
├── routes/               # Application routes
└── public/               # Compiled assets
```

## Commands

### Development
```bash
npm run dev          # Start Vite
npm run build        # Production build
```

### Blocks
```bash
wp acorn make:block BlockName
```

### Testing
```bash
composer test        # Run tests
composer test:coverage  # Coverage report
```

### Translation
```bash
npm run translate    # Generate translations
```

## Code Standards

### PHP
- Strict types
- Type declarations
- PSR-12 formatting
- Dependency injection

### Blade
- No raw PHP in templates
- Escape output
- Use View Composers
- Keep templates simple

### Livewire
- Single file components
- Typed public properties
- Delegate to Services
- Use Flux UI

### Tailwind
- Utility classes
- No inline styles
- Follow design system

## Services

### AuthService
User authentication, login, logout, registration

### RoleService
Role creation, assignment, capability checks

### TwoFactorService
2FA enable/disable/verification

### FavoritesService
Product favorites management

## Middleware

- `auth` - Require authentication
- `guest` - Redirect if logged in
- `two-factor` - Require 2FA verification

## Routes

Defined in `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

## Blocks

### Create
```bash
wp acorn make:block FeatureGrid
```

### Structure
```blade
{{--
{
    "name": "alma/feature-grid",
    "title": "Feature Grid",
    "attributes": {
        "title": {"type": "string", "control": "TextControl"}
    }
}
--}}

@php
    $title = $attributes['title'] ?? 'Features';
@endphp

<section>
    <h2>{{ $title }}</h2>
</section>
```

## Templates

### Layout
`resources/views/layouts/app.blade.php`

### Templates
- `front-page.blade.php` - Front page
- `index.blade.php` - Blog index
- `page.blade.php` - Pages
- `single.blade.php` - Single posts
- `archive.blade.php` - Archives

### WooCommerce
- `woocommerce/archive-product.blade.php`
- `woocommerce/single-product.blade.php`
- `woocommerce/cart.blade.php`
- `woocommerce/checkout.blade.php`

## Requirements

- **PHP**: 8.2+
- **WordPress**: 6.9+
- **Node**: 20+
- **Composer**: Latest

## Dependencies

- `roots/acorn` - Laravel integration
- `livewire/flux` - UI components
- `log1x/acf-composer` - ACF blocks
- `log1x/sage-directives` - Blade directives
- `generoi/sage-woocommerce` - Woo integration

## Development

### Setup
```bash
composer install
npm install
```

### Watch
```bash
npm run dev
```

### Build
```bash
npm run build
```

### Test
```bash
composer test
```

## Deployment

```bash
npm run build
npm run deploy:zip
```

## Resources

- [Alma Documentation](../../README.md)
- [Acorn Documentation](https://roots.io/acorn/)
- [Livewire Documentation](https://livewire.laravel.com/)
- [Flux UI](https://fluxui.xyz/)
- [Tailwind CSS](https://tailwindcss.com/)

## License

MIT License © [Alma Team](https://alma.io/)
