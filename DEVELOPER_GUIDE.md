# Developer Guide

Quick reference for building features in Alma.

## Common Tasks

### Create a New Feature

1. **Create Service** (business logic)
```bash
mkdir -p web/app/themes/alma/app/Services
# Create app/Services/YourFeatureService.php
```

2. **Create Livewire Component** (UI state)
```bash
# Create resources/views/your-feature/search.blade.php
```

3. **Create Route** (if needed)
```php
// web/app/themes/alma/routes/web.php
Route::get('/your-feature', [YourController::class, 'index']);
```

4. **Create View** (presentation)
```bash
# Create resources/views/your-feature/index.blade.php
```

### Create a Gutenberg Block

```bash
wp acorn make:block FeatureGrid
npm run dev  # or build
```

Edit `resources/views/blocks/feature-grid.blade.php`

### Create a Controller

```php
<?php

namespace App\Http\Controllers;

use App\Services\YourService;

class YourController extends Controller
{
    public function index(YourService $service)
    {
        return view('your-view', [
            'data' => $service->getData()
        ]);
    }
}
```

### Create Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;

class YourMiddleware
{
    public function handle($request, Closure $next)
    {
        // Logic here
        return $next($request);
    }
}
```

Register in `ThemeServiceProvider::boot()`:
```php
$router->aliasMiddleware('your-middleware', YourMiddleware::class);
```

### Create a WP-CLI Command

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class YourCommand extends Command
{
    protected $signature = 'your:command {argument}';
    protected $description = 'Do something';

    public function handle()
    {
        $this->info('Done!');
    }
}
```

Run with: `wp acorn your:command value`

## Code Standards

### PHP
- `declare(strict_types=1);`
- Type all properties, parameters, returns
- PSR-12 formatting
- Prefer dependency injection

### Blade
- No raw PHP in templates
- Use View Composers for shared data
- Escape output: `{{ $variable }}`

### Livewire
- Public properties typed
- Delegate logic to Services
- Keep components small
- Use Flux UI

### Tailwind
- Use modern utilities
- Avoid inline styles
- Follow design system

## Debugging

### Enable Debug Mode
```env
WP_ENV=development
WP_DEBUG=true
SCRIPT_DEBUG=true
```

### View Errors
```blade
@error('field')
    {{ $message }}
@enderror
```

### Log Data
```php
logger()->info('Message', ['data' => $value]);
// Check debug.log
```

## Testing

### Manual Testing Checklist
- [ ] Forms validate correctly
- [ ] Authentication works
- [ ] Database queries optimized
- [ ] Mobile responsive
- [ ] Cross-browser tested

### Browser Console
Check for JavaScript errors in dev tools.

### Query Monitor Plugin
Install for debugging WordPress queries.

## Performance Tips

1. **Cache Expensive Queries**
```php
$users = wp_cache_get('users', 'group');
if (!$users) {
    $users = get_users([...]);
    wp_cache_set('users', $users, 'group');
}
```

2. **Avoid N+1**
Use `with()` in queries or batch loading.

3. **Lazy Load Components**
```blade
<livewire:heavy-component lazy />
```

4. **Optimize Images**
Use WebP, proper sizes, lazy loading.

## Security Checklist

- [ ] Nonces on all forms
- [ ] Input sanitization
- [ ] Output escaping
- [ ] Capability checks
- [ ] SQL prepared statements
- [ ] No sensitive data in Livewire state

## Deployment

### Before Deploy
```bash
npm run build
# Test locally
# Run linting
```

### Deploy Commands
```bash
npm run deploy:zip  # Create package
# Upload to server
# Run: wp acorn optimize
```

### Environment Setup
1. Copy `.env` to server
2. Update URLs
3. Generate new salts
4. Set `WP_ENV=production`

## Troubleshooting

### Vite Not Loading
- Check `npm run dev` is running
- Verify `APP_URL` in env
- Clear browser cache

### Livewire Not Working
- Check component in `resources/views/`
- Ensure `@livewireStyles` and `@livewireScripts`
- Verify service container binding

### Blocks Not Appearing
- Run `npm run build`
- Check JSON frontmatter syntax
- Clear block cache

### Routes Not Working
- Check middleware registration
- Verify controller exists
- Clear route cache: `wp acorn optimize`

## Resources

- [Acorn Docs](https://roots.io/acorn/)
- [Livewire Docs](https://livewire.laravel.com/)
- [Flux UI](https://fluxui.xyz/)
- [Sage Docs](https://roots.io/sage/)
- [Bedrock Docs](https://roots.io/bedrock/)
