# Testing Guide

## Current State

No automated testing suite currently configured.

## Recommended Setup

### 1. PHPUnit/WordPress Test Suite

#### Installation
```bash
# Install WordPress test library
composer require --dev yoast/phpunit-polyfills
composer require --dev brain/monkey

# Install test configuration
bash bin/install-test-suite.sh
```

#### Configuration
Create `phpunit.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="vendor/autoload.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    stopOnFailure="false"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="WP_ENV" value="testing"/>
        <env name="WP_DEBUG" value="true"/>
    </php>
</phpunit>
```

#### Test Structure
```
tests/
├── Unit/
│   ├── Services/
│   │   └── AuthServiceTest.php
│   └── Controllers/
│       └── LoginControllerTest.php
├── Feature/
│   ├── Auth/
│   │   └── LoginTest.php
│   └── Blocks/
│       └── HeroBlockTest.php
└── TestCase.php
```

#### Example Test
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthService;

class AuthServiceTest extends TestCase
{
    public function test_login_success()
    {
        $service = new AuthService();
        
        $result = $service->login([
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        
        $this->assertFalse($result['requires_2fa']);
    }
}
```

### 2. Pest PHP (Recommended)

Pest provides cleaner syntax than PHPUnit.

#### Installation
```bash
composer require --dev pestphp/pest
./vendor/bin/pest --init
```

#### Example Pest Test
```php
<?php

use App\Services\AuthService;

it('can authenticate user', function () {
    $service = new AuthService();
    
    $result = $service->login([
        'email' => 'test@example.com',
        'password' => 'password'
    ]);
    
    expect($result['requires_2fa'])->toBeFalse();
});
```

### 3. JavaScript Testing

#### Vitest Setup
```bash
npm install --save-dev vitest @testing-library/vue @testing-library/jest-dom
```

#### Vitest Config
```js
// vite.config.js
import { defineConfig } from 'vite'

export default defineConfig({
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.js'],
  },
})
```

#### Example JS Test
```js
import { render, screen } from '@testing-library/vue'
import SearchComponent from '../resources/views/components/search.blade.php'

describe('SearchComponent', () => {
  it('renders search input', () => {
    render(SearchComponent)
    expect(screen.getByRole('textbox')).toBeInTheDocument()
  })
})
```

### 4. E2E Testing (Playwright)

```bash
npm install --save-dev @playwright/test
npx playwright install
```

#### Example E2E Test
```js
// tests/e2e/auth.spec.js
import { test, expect } from '@playwright/test'

test('can login', async ({ page }) => {
  await page.goto('/login')
  await page.fill('#email', 'test@example.com')
  await page.fill('#password', 'password')
  await page.click('button[type="submit"]')
  
  await expect(page).toHaveURL('/dashboard')
})
```

### 5. Visual Regression (Argo)

```bash
npm install --save-dev @argos-ci/cli
```

### 6. Integration Scripts

Add to `package.json`:
```json
{
  "scripts": {
    "test": "pest",
    "test:js": "vitest",
    "test:e2e": "playwright test",
    "test:coverage": "pest --coverage",
    "test:all": "npm run test && npm run test:js && npm run test:e2e"
  }
}
```

Add to `composer.json`:
```json
{
  "scripts": {
    "test": "pest",
    "test:coverage": "pest --coverage",
    "test:ci": "pest --coverage --min=80"
  }
}
```

## Testing Checklist

### Unit Tests
- [ ] Services tested in isolation
- [ ] Controllers tested with mocks
- [ ] Middleware tested
- [ ] View composers tested

### Feature Tests
- [ ] Authentication flows
- [ ] Form submissions
- [ ] Database operations
- [ ] API endpoints

### Integration Tests
- [ ] Livewire components
- [ ] Block rendering
- [ ] WooCommerce integration
- [ ] Third-party services

### E2E Tests
- [ ] User registration
- [ ] Login/logout
- [ ] Purchase flow
- [ ] Admin actions

### Performance Tests
- [ ] Query count checked
- [ ] Load time measured
- [ ] Memory usage monitored

## Continuous Integration

### GitHub Actions Workflow
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress_test
        options: --health-cmd="mysqladmin ping" --health-interval=10s
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2
      
      - name: Install dependencies
        run: composer install --no-interaction
      
      - name: Install WordPress test suite
        run: bash bin/install-test-suite.sh
      
      - name: Run tests
        run: composer test
```

## Mocking WordPress Functions

Use Brain Monkey for mocking:

```php
<?php

use Brain\Monkey\Functions;

it('can register user', function () {
    Functions\when('wp_insert_user')
        ->justReturn(123);
    
    $service = new AuthService();
    $userId = $service->register([...]);
    
    expect($userId)->toBe(123);
});
```

## Test Data Factory

Create factory for test data:

```php
<?php

namespace Tests\Factories;

class UserFactory
{
    public function create(array $attributes = []): int
    {
        $defaults = [
            'user_login' => 'testuser',
            'user_email' => 'test@example.com',
            'role' => 'subscriber',
        ];
        
        return wp_insert_user(array_merge($defaults, $attributes));
    }
}
```

Usage:
```php
$userId = (new UserFactory)->create(['role' => 'administrator']);
```

## Coverage Requirements

Aim for:
- **Unit tests**: 80%+ coverage
- **Feature tests**: Critical paths covered
- **E2E tests**: Happy paths covered

## Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific file
./vendor/bin/pest tests/Unit/Services/AuthServiceTest.php

# Run with filter
./vendor/bin/pest --filter=test_login

# Watch mode
./vendor/bin/pest --watch
```
