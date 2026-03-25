# Quick Start Guide

Get up and running with Alma in 5 minutes.

## Prerequisites

- PHP 8.2+
- MySQL 8.0+ or MariaDB
- Composer
- Node 20+
- WP-CLI

## Installation

### 1. Clone or Download

```bash
git clone <repository-url> my-project
cd my-project
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

```bash
cp .env.example .env
```

Edit `.env`:

```env
WP_ENV=development
WP_HOME=http://localhost:3000
WP_SITEURL=${WP_HOME}/wp
DB_NAME=alma_db
DB_USER=root
DB_PASSWORD=password
DB_HOST=localhost
```

Generate security keys at [roots.io/salts.html](https://roots.io/salts.html) and paste them in `.env`.

### 4. Run Setup

```bash
bash bin/setup.sh
```

This will:
- Create database
- Install WordPress
- Configure theme
- Set permissions

### 5. Start Development Server

Terminal 1 (Vite):
```bash
npm run dev
```

Terminal 2 (WordPress):
```bash
wp server --port=3000
# Or use your local server (Local, DevKinsta, etc.)
```

### 6. Access Site

Open: `http://localhost:3000`

Default admin:
- Email: admin@example.com
- Password: (check setup output)

## Next Steps

### Create Your First Block

```bash
wp acorn make:block Hero
npm run dev
```

Edit: `web/app/themes/alma/resources/views/blocks/hero.blade.php`

### Create Your First Component

Create: `web/app/themes/alma/resources/views/livewire/search.blade.php`

```blade
<?php

use Livewire\Component;

new class extends Component {
    public string $query = '';
};
?>

<div>
    <input wire:model.live="query" placeholder="Search...">
</div>
```

Use in template:
```blade
<livewire:search />
```

### Create Your First Service

Create: `web/app/themes/alma/app/Services/SearchService.php`

```php
<?php

namespace App\Services;

class SearchService
{
    public function search(string $query): array
    {
        $args = [
            's' => $query,
            'post_type' => 'post',
        ];
        
        return get_posts($args);
    }
}
```

Use in component:
```php
public function updatedQuery(SearchService $search): void
{
    $this->results = $search->search($this->query);
}
```

### Create Your First Route

Edit: `web/app/themes/alma/routes/web.php`

```php
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search']);
```

### Create Your First Controller

Create: `web/app/themes/alma/app/Http/Controllers/SearchController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\SearchService;

class SearchController extends Controller
{
    public function search(SearchService $service)
    {
        return view('search.results', [
            'results' => $service->search(request('q'))
        ]);
    }
}
```

## Common Commands

```bash
# Development
npm run dev          # Start Vite
wp acorn serve       # PHP dev server

# Build
npm run build        # Production build

# Testing
composer test        # Run tests
composer lint        # Check code style
composer lint:fix    # Fix code style

# CLI
wp acorn list        # List commands
wp acorn make:block  # Create block

# Database
wp db export         # Export database
wp db import         # Import database

# Cache
wp cache flush       # Clear cache
wp acorn optimize    # Optimize
```

## Project Structure

```
my-project/
├── config/              # WordPress config
├── web/
│   ├── app/
│   │   └── themes/
│   │       └── alma/    # Your theme
│   │           ├── app/       # PHP code
│   │           ├── resources/ # Assets
│   │           └── routes/    # Routes
│   └── wp/            # WordPress core
└── vendor/            # Dependencies
```

## Learn More

- [Architecture Guide](ARCHITECTURE.md)
- [Developer Guide](DEVELOPER_GUIDE.md)
- [Testing Guide](TESTING.md)
- [Deployment Guide](DEPLOYMENT.md)

## Troubleshooting

### Vite Not Loading
```bash
# Check dev server is running
npm run dev

# Check APP_URL in .env
# Clear browser cache
```

### Database Error
```bash
# Check credentials in .env
# Ensure MySQL is running
# Test connection: mysql -u root -p
```

### White Screen
```bash
# Enable debug
WP_DEBUG=true
# Check error log
tail -f web/app/debug.log
```

### Block Not Appearing
```bash
# Rebuild
npm run build
# Clear cache
wp cache flush
```

## Support

- Documentation: See guides
- Issues: Open GitHub issue
- Community: Roots Discourse

## Ready to Build!

Start creating your application. Alma provides the foundation - you build the features.

Happy coding! 🚀
