<p align="center">
  <a href="https://alma.roots.io/">
    <img alt="Alma" src="https://cdn.roots.io/app/uploads/logo-bedrock.svg" height="100">
  </a>
</p>

<p align="center">
  <strong>A multipurpose WordPress + Laravel hybrid starter kit</strong>
</p>

<p align="center">
  Build full applications (websites, ecommerce, SaaS) with WordPress and Laravel tools.<br>
  Feels like Laravel Livewire Starter Kit without losing WordPress functionality.
</p>

<p align="center">
  <a href="https://packagist.org/packages/roots/bedrock"><img alt="Packagist Installs" src="https://img.shields.io/packagist/dt/roots/bedrock?label=projects%20created&colorB=2b3072&colorA=525ddc&style=flat-square"></a>
  <a href="https://github.com/roots/bedrock/actions/workflows/ci.yml"><img alt="Build Status" src="https://img.shields.io/github/actions/workflow/status/roots/bedrock/ci.yml?branch=master&logo=github&label=CI&style=flat-square"></a>
  <a href="https://github.com/sponsors/roots"><img src="https://img.shields.io/badge/sponsor%20roots-525ddc?logo=github&style=flat-square&logoColor=ffffff&message=" alt="Sponsor Roots"></a>
</p>

---

## Overview

Alma is a **multipurpose starter kit** that combines the best of WordPress and Laravel ecosystems:

- **WordPress** - World's most popular CMS
- **Bedrock** - Modern WordPress project structure
- **Acorn** - Laravel components in WordPress (replaces Artisan)
- **Livewire 4** - Reactive single-file components
- **Flux UI** - Premium component library
- **Blade** - Elegant templating engine
- **Tailwind CSS 4** - Modern utility-first CSS
- **WooCommerce** - E-commerce functionality

## Features

### 🏗️ Architecture
- Service-oriented architecture
- Laravel-style MVC pattern
- Clean separation of concerns
- Dependency injection
- PSR-4 autoloading

### ⚡ Development
- Vite build system
- Hot module replacement
- Modern JavaScript (ES6+)
- Tailwind CSS 4
- Dark mode support

### 🧩 Components
- Native Gutenberg blocks (single-file Blade)
- Livewire single-file components
- Flux UI components
- Reusable Blade components
- View composers

### 🔐 Security
- Authentication system
- 2FA support
- Role management
- Middleware protection
- Input validation

### 🛍️ E-commerce
- WooCommerce integration
- Custom template overrides
- Tailwind styling
- Flux UI components

### 🧪 Testing
- Pest PHP test suite
- Unit tests
- Feature tests
- Mocking utilities
- Coverage reporting

## Quick Start

### 1. Clone
```bash
git clone <repository-url> my-project
cd my-project
```

### 2. Install
```bash
composer install
npm install
```

### 3. Configure
```bash
cp .env.example .env
# Edit .env with your settings
```

### 4. Setup
```bash
bash bin/setup.sh
```

### 5. Develop
```bash
npm run dev          # Start Vite
wp server --port=3000  # Start WordPress
```

Visit: `http://localhost:3000`

## Documentation

| Guide | Description |
|-------|-------------|
| [Quick Start](QUICKSTART.md) | Get up and running in 5 minutes |
| [Architecture](ARCHITECTURE.md) | Project structure and patterns |
| [Developer Guide](DEVELOPER_GUIDE.md) | Building features reference |
| [Testing Guide](TESTING.md) | Testing setup and examples |
| [Deployment Guide](DEPLOYMENT.md) | Production deployment |
| [API Reference](API.md) | Complete API documentation |
| [Contributing Guide](CONTRIBUTING.md) | How to contribute |

## Project Structure

```
my-project/
├── config/                    # WordPress configuration
├── web/
│   ├── app/
│   │   ├── themes/
│   │   │   └── alma/        # Main theme
│   │   │       ├── app/     # Laravel-style code
│   │   │       │   ├── Console/     # WP-CLI commands
│   │   │       │   ├── Http/        # Controllers, Middleware
│   │   │       │   ├── Providers/   # Service providers
│   │   │       │   ├── Services/    # Business logic
│   │   │       │   └── View/        # View composers
│   │   │       ├── resources/       # Front-end assets
│   │   │       │   ├── views/       # Blade templates
│   │   │       │   ├── css/         # Stylesheets
│   │   │       │   └── js/          # JavaScript
│   │   │       └── routes/          # Application routes
│   │   ├── plugins/         # WordPress plugins
│   │   └── mu-plugins/      # Must-use plugins
│   └── wp/                  # WordPress core
├── vendor/                  # Composer dependencies
└── bin/                     # Build scripts
```

## Common Commands

### Development
```bash
npm run dev          # Start Vite dev server
npm run build        # Production build
wp acorn serve       # PHP dev server
```

### Testing
```bash
composer test        # Run Pest tests
composer test:coverage  # With coverage
composer lint        # Check code style
composer lint:fix    # Fix code style
```

### CLI
```bash
wp acorn list        # List commands
wp acorn make:block  # Create Gutenberg block
wp db export         # Export database
wp cache flush       # Clear cache
```

### Deployment
```bash
npm run deploy:zip   # Create deployment package
```

## Stack Details

### Backend
- **PHP 8.2+** - Modern PHP features
- **WordPress 6.9+** - Latest WordPress
- **Acorn 5.0** - Laravel integration
- **Composer** - Dependency management

### Frontend
- **Vite 7.0** - Fast build tool
- **Tailwind CSS 4** - Utility-first CSS
- **Livewire 4** - Reactive components
- **Flux UI** - Component library
- **Alpine.js** - Lightweight JS

### E-commerce
- **WooCommerce 10.5+** - Full store functionality
- **Wompi** - Payment gateway (Colombia)
- **ACF Pro** - Custom fields

## Code Examples

### Service Pattern
```php
namespace App\Services;

class SearchService
{
    public function search(string $query): array
    {
        return get_posts(['s' => $query]);
    }
}
```

### Livewire Component
```blade
<?php

use Livewire\Component;

new class extends Component {
    public string $search = '';
    
    public function updatedSearch(SearchService $search): void
    {
        $this->results = $search->search($this->search);
    }
};
?>

<div>
    <flux:input wire:model.live="search" />
    @foreach($results as $result)
        <p>{{ $result->post_title }}</p>
    @endforeach
</div>
```

### Gutenberg Block
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

<section class="py-20">
    <h2 class="text-4xl font-bold">{{ $title }}</h2>
</section>
```

Create with: `wp acorn make:block Hero`

## Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.0+ or MariaDB 10.4+
- **Node**: 20 or higher
- **Composer**: Latest version
- **WP-CLI**: Latest version

## Support

- **Documentation**: See guides above
- **Issues**: Open GitHub issue
- **Community**: [Roots Discourse](https://discourse.roots.io/)
- **Twitter**: [@rootswp](https://twitter.com/rootswp)

## Sponsors

Support modern WordPress development by [sponsoring Roots](https://github.com/sponsors/roots).

<a href="https://carrot.com/"><img src="https://cdn.roots.io/app/uploads/carrot.svg" alt="Carrot" width="120" height="90"></a>
<a href="https://wordpress.com/"><img src="https://cdn.roots.io/app/uploads/wordpress.svg" alt="WordPress.com" width="120" height="90"></a>
<a href="https://www.itineris.co.uk/"><img src="https://cdn.roots.io/app/uploads/itineris.svg" alt="Itineris" width="120" height="90"></a>
<a href="https://kinsta.com/"><img src="https://cdn.roots.io/app/uploads/kinsta.svg" alt="Kinsta" width="120" height="90"></a>

## License

MIT License © [Roots](https://roots.io/)

---

**Built with ❤️ using WordPress + Laravel**
