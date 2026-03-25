# API Reference

Complete API documentation for Alma theme.

## Services

### AuthService

**Location:** `app/Services/AuthService.php`

#### Methods

##### `login(array $credentials): array|WP_Error`

Attempt to log in user.

**Parameters:**
- `$credentials` - Array with `email`/`username` and `password`

**Returns:**
- Array with `requires_2fa` boolean and `user_id` or `user`
- WP_Error on failure

**Example:**
```php
$result = $authService->login([
    'email' => 'user@example.com',
    'password' => 'password'
]);

if ($result['requires_2fa']) {
    // Redirect to 2FA page
}
```

##### `completeLogin(WP_User $user, bool $remember): void`

Complete login by setting cookies.

**Parameters:**
- `$user` - WordPress user object
- `$remember` - Remember me flag

**Example:**
```php
$authService->completeLogin($user, true);
```

##### `register(array $data): int|WP_Error`

Register new user.

**Parameters:**
- `$data` - Array with `email`, `password`, `username`

**Returns:**
- User ID on success
- WP_Error on failure

**Example:**
```php
$userId = $authService->register([
    'email' => 'new@example.com',
    'password' => 'password',
    'username' => 'newuser'
]);
```

##### `logout(): void`

Log out current user.

**Example:**
```php
$authService->logout();
```

##### `user(): ?WP_User`

Get current user.

**Returns:**
- WP_User or null

**Example:**
```php
$user = $authService->user();
if ($user) {
    echo $user->display_name;
}
```

##### `check(): bool`

Check if user is authenticated.

**Returns:**
- Boolean

**Example:**
```php
if ($authService->check()) {
    // User is logged in
}
```

---

### RoleService

**Location:** `app/Services/RoleService.php`

#### Methods

##### `createRole(string $role, string $displayName, array $capabilities): void`

Create new role.

**Parameters:**
- `$role` - Role slug
- `$displayName` - Display name
- `$capabilities` - Capabilities array

**Example:**
```php
$roleService->createRole('alma_admin', 'Alma Admin', [
    'read' => true,
    'edit_posts' => true
]);
```

##### `assignRole(int $userId, string $role): void`

Assign role to user.

**Parameters:**
- `$userId` - User ID
- `$role` - Role slug

**Example:**
```php
$roleService->assignRole($userId, 'administrator');
```

##### `hasRole(?int $userId, string $role): bool`

Check if user has role.

**Parameters:**
- `$userId` - User ID (null for current)
- `$role` - Role slug

**Returns:**
- Boolean

**Example:**
```php
if ($roleService->hasRole(null, 'administrator')) {
    // Current user is admin
}
```

##### `hasPermission(?int $userId, string $permission): bool`

Check capability.

**Parameters:**
- `$userId` - User ID (null for current)
- `$permission` - Capability name

**Returns:**
- Boolean

**Example:**
```php
if ($roleService->hasPermission(null, 'manage_options')) {
    // Can manage options
}
```

---

### TwoFactorService

**Location:** `app/Services/TwoFactorService.php`

#### Methods

##### `isEnabled(int $userId): bool`

Check if 2FA is enabled.

**Example:**
```php
if ($twoFactorService->isEnabled($userId)) {
    // 2FA is active
}
```

##### `enable(int $userId): void`

Enable 2FA.

**Example:**
```php
$twoFactorService->enable($userId);
```

##### `disable(int $userId): void`

Disable 2FA.

**Example:**
```php
$twoFactorService->disable($userId);
```

##### `verify(int $userId, string $code): bool`

Verify 2FA code.

**Example:**
```php
if ($twoFactorService->verify($userId, $code)) {
    // Code valid
}
```

---

### FavoritesService

**Location:** `app/Services/FavoritesService.php`

#### Methods

##### `add(int $userId, int $productId): void`

Add to favorites.

**Example:**
```php
$favoritesService->add($userId, $productId);
```

##### `remove(int $userId, int $productId): void`

Remove from favorites.

**Example:**
```php
$favoritesService->remove($userId, $productId);
```

##### `get(int $userId): array`

Get user's favorites.

**Returns:**
- Array of product IDs

**Example:**
```php
$favorites = $favoritesService->get($userId);
```

##### `isFavorite(int $userId, int $productId): bool`

Check if product is favorited.

**Example:**
```php
if ($favoritesService->isFavorite($userId, $productId)) {
    // Is favorited
}
```

---

## Controllers

All controllers extend `App\Http\Controllers\Controller`.

### LoginController

**Route:** GET `/login`

**Method:** `showLoginForm()`

**Returns:** View `auth.login`

---

### RegisterController

**Route:** GET `/register`

**Method:** `showRegistrationForm()`

**Returns:** View `auth.register`

---

### DashboardController

**Route:** GET `/dashboard`

**Middleware:** auth, two-factor

**Method:** `index()`

**Returns:** View `dashboard`

---

### SettingsController

**Route:** GET `/settings`

**Middleware:** auth

**Method:** `index()`

**Returns:** View `settings`

---

### TwoFactorController

**Route:** GET `/two-factor-challenge`

**Middleware:** auth

**Method:** `challenge()`

**Returns:** View `auth.two-factor-challenge`

---

## Middleware

### Authenticate

**Class:** `App\Http\Middleware\Authenticate`

**Alias:** `auth`

**Purpose:** Require authentication

**Usage:**
```php
Route::middleware('auth')->group(function () {
    // Protected routes
});
```

---

### RedirectIfAuthenticated

**Class:** `App\Http\Middleware\RedirectIfAuthenticated`

**Alias:** `guest`

**Purpose:** Redirect if logged in

**Usage:**
```php
Route::middleware('guest')->group(function () {
    // Guest routes
});
```

---

### EnsureTwoFactorVerified

**Class:** `App\Http\Middleware\EnsureTwoFactorVerified`

**Alias:** `two-factor`

**Purpose:** Require 2FA verification

**Usage:**
```php
Route::middleware('two-factor')->group(function () {
    // 2FA required routes
});
```

---

## Livewire Components

Components live in `resources/views/` as single-file components.

### Usage

```blade
<livewire:component-name />
```

### Properties

Public properties are reactive:

```php
public string $search = '';

public function updatedSearch(): void
{
    // Auto-called when $search changes
}
```

### Events

Listen to events:

```php
#[On('product-added')]
public function productAdded(int $productId): void
{
    // Handle event
}
```

Dispatch events:

```php
$this->dispatch('product-added', productId: $id);
```

---

## Blocks

Blocks use JSON frontmatter in Blade files.

### Creation

```bash
wp acorn make:block BlockName
```

### Structure

```blade
{{--
{
    "name": "alma/block-name",
    "title": "Block Name",
    "attributes": {
        "title": {"type": "string", "control": "TextControl"}
    }
}
--}}

@php
    $title = $attributes['title'] ?? 'Default';
@endphp

<!-- Output -->
```

### Available Controls

- `TextControl` - Text input
- `TextareaControl` - Text area
- `ToggleControl` - Boolean toggle
- `SelectControl` - Dropdown
- `ImageControl` - Media picker

---

## View Composers

### App Composer

**Class:** `App\View\Composers\App`

**Purpose:** Share data across views

**Data:**
- Site title
- Navigation menu
- User status

---

### Post Composer

**Class:** `App\View\Composers\Post`

**Purpose:** Share post data

**Data:**
- Post object
- Author info
- Categories
- Tags

---

## Template Hierarchy

### Front Page
`front-page.blade.php`

### Home Page
`home.blade.php` or `index.blade.php`

### Single Post
`single.blade.php` → `single-{post-type}.blade.php`

### Page
`page.blade.php` → `page-{template}.blade.php`

### Archive
`archive.blade.php` → `archive-{post-type}.blade.php`

### WooCommerce
- `woocommerce/archive-product.blade.php` - Shop
- `woocommerce/single-product.blade.php` - Product
- `woocommerce/cart.blade.php` - Cart
- `woocommerce/checkout.blade.php` - Checkout

---

## Routes

### Definition

File: `routes/web.php`

### Example

```php
Route::get('/path', [Controller::class, 'method']);
Route::post('/path', [Controller::class, 'method']);
Route::put('/path', [Controller::class, 'method']);
Route::delete('/path', [Controller::class, 'method']);
```

### Middleware

```php
Route::middleware(['auth', '2fa'])->group(function () {
    // Protected
});
```

### Named Routes

```php
Route::get('/dashboard', [Controller::class, 'index'])->name('dashboard');

// Usage
route('dashboard');
```

---

## Helpers

### WordPress Functions

All WP functions available:
- `get_posts()`
- `get_user_by()`
- `wp_insert_post()`
- `wp_send_json()`

### Laravel Helpers

Via Acorn:
- `view()` - Render view
- `route()` - Generate URL
- `session()` - Session access
- `request()` - Request object
- `logger()` - Logging

### Blade Directives

Standard Laravel Blade:
- `@if`, `@else`, `@endif`
- `@foreach`, `@endforeach`
- `@include`, `@yield`, `@section`
- `@push`, `@stack`
- `@inject`, `@can`

Custom directives via `sage-directives`.

---

## Error Handling

### Validation Errors

```blade
@error('email')
    {{ $message }}
@enderror
```

### Try-Catch

```php
try {
    // Operation
} catch (\Exception $e) {
    logger()->error($e->getMessage());
}
```

### Error Pages

- `404.blade.php` - Not found
- `500.blade.php` - Server error (create if needed)

---

## Caching

### Object Cache

```php
wp_cache_set('key', 'value', 'group');
$value = wp_cache_get('key', 'group');
wp_cache_delete('key', 'group');
```

### Transients

```php
set_transient('key', 'value', HOUR_IN_SECONDS);
$value = get_transient('key');
delete_transient('key');
```

### Fragment Cache

```blade
@cache('key', HOUR_IN_SECONDS)
    <!-- Cached content -->
@endcache
```

---

## Security

### Nonces

```php
wp_nonce_field('action_name');
wp_verify_nonce($nonce, 'action_name');
```

### Sanitization

```php
sanitize_text_field($input);
sanitize_email($email);
sanitize_key($key);
```

### Escaping

```blade
{{ esc_html($value) }}
{{ esc_url($url) }}
{{ esc_attr($attr) }}
```

### Capabilities

```php
if (current_user_can('edit_posts')) {
    // User can edit
}
```

---

## Performance

### Query Optimization

```php
// Use get_posts with fields
get_posts([
    'fields' => 'ids', // Only IDs
    'no_found_rows' => true,
]);
```

### Batch Loading

```php
// Prefetch related data
$posts = get_posts([...]);
$authors = get_users(['include' => wp_list_pluck($posts, 'post_author')]);
```

### Lazy Loading

```blade
<livewire:heavy-component lazy />
```

---

## Version

Current: 1.0.0
API Version: 1
