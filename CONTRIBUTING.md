# Contributing Guide

Thank you for considering contributing to Alma! This guide outlines how to contribute to the project.

## How to Contribute

### 1. Fork and Clone

```bash
git clone https://github.com/your-username/v7.alma.git
cd v7.alma
```

### 2. Setup Development Environment

```bash
composer install
npm install
bash bin/setup.sh
```

### 3. Create Branch

```bash
git checkout -b feature/your-feature-name
```

### 4. Make Changes

Follow coding standards and architecture guidelines.

### 5. Write Tests

```bash
composer test
npm run test
```

### 6. Run Linter

```bash
composer lint
```

### 7. Commit Changes

Follow commit message conventions:

```
feat: Add new feature
fix: Fix bug
docs: Update documentation
style: Format code
refactor: Refactor code
test: Add tests
chore: Update dependencies
```

### 8. Push and Create PR

```bash
git push origin feature/your-feature-name
```

Create pull request on GitHub.

## Coding Standards

### PHP Standards
- PSR-12
- Type declarations
- Strict mode
- Dependency injection
- SOLID principles

### Blade Standards
- No raw PHP in templates
- Escape output
- Use View Composers
- Keep templates simple

### JavaScript Standards
- ES6+ syntax
- No jQuery (use vanilla or Alpine)
- Modern build tools

### CSS Standards
- Tailwind CSS utilities
- No inline styles
- Follow design system

## Architecture Rules

### Service Architecture
- Business logic in Services
- Controllers handle HTTP
- Views handle presentation
- Livewire handles UI state

### Livewire Rules
- Single file components
- Typed public properties
- Delegate to Services
- Use Flux UI

### Block Architecture
- JSON frontmatter
- Single .blade.php file
- No manual block.json
- Use make:block command

## Testing Requirements

### Unit Tests
- Test Services
- Test Controllers
- Test Middleware
- 80%+ coverage

### Feature Tests
- Test user flows
- Test integrations
- Test edge cases

### E2E Tests
- Test critical paths
- Test happy flows
- Test error handling

## Documentation

### Code Documentation
- PHPDoc blocks
- Inline comments for complex logic
- README for new features

### User Documentation
- Update guides
- Add examples
- Include screenshots

## Code Review Process

1. Create PR
2. Pass CI checks
3. Review by maintainer
4. Address feedback
5. Merge

## What to Contribute

### Features
- New Gutenberg blocks
- Livewire components
- Services
- Controllers
- Integrations

### Improvements
- Performance optimizations
- Developer experience
- Error handling
- Testing coverage

### Bug Fixes
- Report issues
- Fix bugs
- Add regression tests

### Documentation
- Guides
- Examples
- API docs
- Troubleshooting

## Development Tools

### Required
- PHP 8.2+
- Node 20+
- Composer
- npm/yarn
- Git

### Recommended
- IDE (PHPStorm, VS Code)
- Debugging tools (Xdebug)
- Query Monitor plugin
- Linter (Pint)

## Release Process

1. Update CHANGELOG.md
2. Update version
3. Create release branch
4. Test thoroughly
5. Create tag
6. Publish release

## Questions?

Open an issue or contact maintainers.

## Thank You!

Your contributions make Alma better for everyone.
